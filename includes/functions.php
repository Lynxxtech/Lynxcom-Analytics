<?php
session_start();
define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT . '/data');
// Live/private data should survive Hostinger Git deploys. Prefer a folder
// outside public_html; fall back to /data when the parent is not writable.
define('PRIVATE_DATA_DIR', dirname(APP_ROOT) . '/lynxcom_private_data');
function lx_storage_dir(){
  if(!is_dir(PRIVATE_DATA_DIR)) @mkdir(PRIVATE_DATA_DIR, 0755, true);
  return is_dir(PRIVATE_DATA_DIR) && is_writable(PRIVATE_DATA_DIR) ? PRIVATE_DATA_DIR : DATA_DIR;
}
define('STORAGE_DIR', lx_storage_dir());
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('LEADS_FILE', STORAGE_DIR . '/leads.csv');
define('SUPPORT_FILE', STORAGE_DIR . '/support.csv');
define('TRAFFIC_FILE', STORAGE_DIR . '/traffic.csv');
define('BLOG_VIEWS_FILE', STORAGE_DIR . '/blog_views.csv');
define('GEO_CACHE_FILE', STORAGE_DIR . '/geo_cache.json');
define('BLOG_FILE', STORAGE_DIR . '/blog.json');
define('BLOG_SEED_FILE', DATA_DIR . '/blog.json');
define('CONFIG_FILE', STORAGE_DIR . '/config.local.php');
define('LEGACY_CONFIG_FILE', DATA_DIR . '/config.local.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function load_content(){
  $json = file_exists(CONTENT_FILE) ? file_get_contents(CONTENT_FILE) : '{}';
  $data = json_decode($json, true);
  return is_array($data) ? $data : [];
}
function save_content($data){
  return file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
function csrf_token(){
  if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function check_csrf(){
  return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
}
function config(){
  if(file_exists(CONFIG_FILE)) return require CONFIG_FILE;
  if(file_exists(LEGACY_CONFIG_FILE)){
    @copy(LEGACY_CONFIG_FILE, CONFIG_FILE);
    return require LEGACY_CONFIG_FILE;
  }
  return [];
}
function admin_configured(){ $c=config(); return !empty($c['admin_password_hash']); }
function require_admin(){
  if(empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
}
function lead_rows(){
  if(!file_exists(LEADS_FILE)) return [];
  $f=fopen(LEADS_FILE,'r'); if(!$f) return [];
  $head=fgetcsv($f); $rows=[];
  while(($r=fgetcsv($f))!==false){ $rows[]=array_combine($head,$r); }
  fclose($f); return array_reverse($rows);
}


function client_ip(){
  $keys=['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'];
  foreach($keys as $k){
    if(!empty($_SERVER[$k])){
      $v=explode(',',$_SERVER[$k])[0];
      return trim($v);
    }
  }
  return '';
}
function rough_location_from_ip($ip){
  if(!$ip) return 'Unknown';
  if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)===false) return 'Local/Private';
  $prefix=substr($ip,0,strpos($ip,'.')?:0);
  return $prefix ? 'IP prefix '.$prefix : 'Unknown';
}
function is_public_ip($ip){
  return $ip && filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)!==false;
}
function geo_cache(){
  if(!file_exists(GEO_CACHE_FILE)) return [];
  $j=json_decode(@file_get_contents(GEO_CACHE_FILE),true);
  return is_array($j)?$j:[];
}
function save_geo_cache($cache){
  if(!is_dir(dirname(GEO_CACHE_FILE))) @mkdir(dirname(GEO_CACHE_FILE),0755,true);
  @file_put_contents(GEO_CACHE_FILE,json_encode($cache,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
function geo_location_from_ip($ip){
  $fallback=['country'=>'Unknown','region'=>'','city'=>'','location'=>'Unknown','source'=>'fallback'];
  if(!is_public_ip($ip)) return ['country'=>'Local/Private','region'=>'','city'=>'','location'=>'Local/Private','source'=>'local','lookup_at'=>date('c')];
  $cache=geo_cache();
  if(isset($cache[$ip]) && !empty($cache[$ip]['lookup_at'])){
    $age=time()-strtotime($cache[$ip]['lookup_at']);
    $src=$cache[$ip]['source']??'';
    $maxAge=in_array($src,['ipwho.is','ip-api'],true) ? 86400*30 : 3600;
    if($age>=0 && $age<$maxAge) return $cache[$ip];
  }
  $ctx=stream_context_create(['http'=>['timeout'=>3.0,'user_agent'=>'LynxcomAnalyticsTrafficTracker/1.0']]);
  $providers=[
    ['source'=>'ipwho.is','url'=>'https://ipwho.is/'.rawurlencode($ip),'parser'=>function($d){
      if(!is_array($d) || empty($d['success'])) return null;
      $parts=array_values(array_filter([$d['city']??'', $d['region']??'', $d['country']??'']));
      return ['country'=>$d['country']??'Unknown','region'=>$d['region']??'','city'=>$d['city']??'','location'=>$parts?implode(', ',$parts):'Unknown','source'=>'ipwho.is','lookup_at'=>date('c')];
    }],
    ['source'=>'ip-api','url'=>'http://ip-api.com/json/'.rawurlencode($ip).'?fields=status,country,regionName,city,query','parser'=>function($d){
      if(!is_array($d) || ($d['status']??'')!=='success') return null;
      $parts=array_values(array_filter([$d['city']??'', $d['regionName']??'', $d['country']??'']));
      return ['country'=>$d['country']??'Unknown','region'=>$d['regionName']??'','city'=>$d['city']??'','location'=>$parts?implode(', ',$parts):'Unknown','source'=>'ip-api','lookup_at'=>date('c')];
    }]
  ];
  foreach($providers as $provider){
    $raw=@file_get_contents($provider['url'],false,$ctx);
    if(!$raw) continue;
    $d=json_decode($raw,true);
    $geo=$provider['parser']($d);
    if($geo){ $cache[$ip]=$geo; save_geo_cache($cache); return $geo; }
  }
  $fallback['location']=rough_location_from_ip($ip); $fallback['lookup_at']=date('c'); $cache[$ip]=$fallback; save_geo_cache($cache); return $fallback;
}
function csv_read_assoc_compat($file){
  if(!file_exists($file)) return [];
  $f=fopen($file,'r'); if(!$f) return [];
  $head=fgetcsv($f); if(!$head){ fclose($f); return []; }
  $rows=[];
  while(($r=fgetcsv($f))!==false){
    $row=[];
    foreach($head as $i=>$h){ $row[$h]=$r[$i]??''; }
    $rows[]=$row;
  }
  fclose($f); return $rows;
}
function ensure_csv_header($file,$header){
  if(!is_dir(dirname($file))) @mkdir(dirname($file),0755,true);
  if(!file_exists($file) || filesize($file)===0){ $f=fopen($file,'w'); fputcsv($f,$header); fclose($f); return; }
  $f=fopen($file,'r'); $old=fgetcsv($f); fclose($f);
  if($old===$header) return;
  $rows=csv_read_assoc_compat($file);
  $tmp=$file.'.tmp'; $out=fopen($tmp,'w'); fputcsv($out,$header);
  foreach($rows as $r){ $line=[]; foreach($header as $h){ $line[]=$r[$h]??''; } fputcsv($out,$line); }
  fclose($out); @rename($tmp,$file);
}
function append_traffic($row){
  $header=['created_at','page','page_title','ip','location_hint','country','region','city','geo_source','post_slug','referrer','referrer_host','utm_source','utm_medium','utm_campaign','user_agent'];
  ensure_csv_header(TRAFFIC_FILE,$header);
  $f=fopen(TRAFFIC_FILE,'a'); if(!$f) return false;
  fputcsv($f,$row); fclose($f); return true;
}
function visitor_hash($ip,$ua,$slug=''){
  $salt='lynxcom-analytics-v1';
  return hash('sha256',$salt.'|'.$slug.'|'.$ip.'|'.$ua);
}
function append_blog_view_unique($postSlug,$pageTitle,$ip,$ua,$geo,$ref,$host,$utmSource,$utmMedium,$utmCampaign){
  $postSlug=trim((string)$postSlug); if($postSlug==='') return false;
  $header=['created_at','post_slug','page_title','visitor_hash','country','region','city','location','geo_source','referrer_host','utm_source','utm_medium','utm_campaign'];
  ensure_csv_header(BLOG_VIEWS_FILE,$header);
  $hash=visitor_hash($ip,$ua,$postSlug);
  foreach(csv_read_assoc_compat(BLOG_VIEWS_FILE) as $r){ if(($r['post_slug']??'')===$postSlug && ($r['visitor_hash']??'')===$hash) return false; }
  $f=fopen(BLOG_VIEWS_FILE,'a'); if(!$f) return false;
  fputcsv($f,[date('c'),$postSlug,$pageTitle,$hash,$geo['country']??'Unknown',$geo['region']??'',$geo['city']??'',$geo['location']??'Unknown',$geo['source']??'',$host,$utmSource,$utmMedium,$utmCampaign]); fclose($f); return true;
}
function track_visit($pageTitle='',$postSlug=''){
  if(php_sapi_name()==='cli') return;
  $ua=$_SERVER['HTTP_USER_AGENT']??'';
  if(preg_match('/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|preview/i',$ua)) return;
  $uri=$_SERVER['REQUEST_URI']??'';
  if(preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp|ico|xml|txt)$/i',$uri)) return;
  $ip=client_ip(); $ref=$_SERVER['HTTP_REFERER']??''; $host='';
  if($ref){ $parts=parse_url($ref); $host=$parts['host']??''; }
  $geo=geo_location_from_ip($ip);
  $utmSource=$_GET['utm_source']??''; $utmMedium=$_GET['utm_medium']??''; $utmCampaign=$_GET['utm_campaign']??'';
  $row=[date('c'),$uri,$pageTitle,$ip,$geo['location']??rough_location_from_ip($ip),$geo['country']??'Unknown',$geo['region']??'',$geo['city']??'',$geo['source']??'',(string)$postSlug,$ref,$host,$utmSource,$utmMedium,$utmCampaign,$ua];
  append_traffic($row);
  if($postSlug) append_blog_view_unique($postSlug,$pageTitle,$ip,$ua,$geo,$ref,$host,$utmSource,$utmMedium,$utmCampaign);
}
function traffic_rows($limit=1000){
  $rows=csv_read_assoc_compat(TRAFFIC_FILE);
  $rows=array_reverse($rows); return array_slice($rows,0,$limit);
}
function traffic_summary($rows,$key,$limit=10){
  $out=[]; foreach($rows as $r){ $v=trim($r[$key]??''); if($v==='') $v='Direct / none'; $out[$v]=($out[$v]??0)+1; }
  arsort($out); return array_slice($out,0,$limit,true);
}
function blog_view_rows(){ return csv_read_assoc_compat(BLOG_VIEWS_FILE); }
function blog_view_stats($posts){
  $views=blog_view_rows(); $traffic=traffic_rows(5000); $out=[];
  foreach($posts as $p){ $slug=$p['slug']??''; if($slug) $out[$slug]=['unique'=>0,'total'=>0,'locations'=>[],'countries'=>[],'last_view'=>'']; }
  foreach($views as $v){
    $slug=$v['post_slug']??''; if(!isset($out[$slug])) $out[$slug]=['unique'=>0,'total'=>0,'locations'=>[],'countries'=>[],'last_view'=>''];
    $out[$slug]['unique']++;
    $loc=trim($v['location']??''); if($loc==='') $loc=trim(implode(', ',array_filter([$v['city']??'',$v['region']??'',$v['country']??'']))); if($loc==='') $loc='Unknown';
    $out[$slug]['locations'][$loc]=($out[$slug]['locations'][$loc]??0)+1;
    $country=trim($v['country']??''); if($country==='') $country='Unknown'; $out[$slug]['countries'][$country]=($out[$slug]['countries'][$country]??0)+1;
    if(($v['created_at']??'')>$out[$slug]['last_view']) $out[$slug]['last_view']=$v['created_at']??'';
  }
  foreach($traffic as $t){ $slug=$t['post_slug']??''; if($slug && isset($out[$slug])) $out[$slug]['total']++; }
  foreach($out as &$st){ arsort($st['locations']); arsort($st['countries']); }
  return $out;
}

function support_rows(){
  if(!file_exists(SUPPORT_FILE)) return [];
  $f=fopen(SUPPORT_FILE,'r'); if(!$f) return [];
  $head=fgetcsv($f); $rows=[];
  while(($r=fgetcsv($f))!==false){ if(count($r)==count($head)) $rows[]=array_combine($head,$r); }
  fclose($f); return array_reverse($rows);
}
function append_support($row){
  $new = !file_exists(SUPPORT_FILE) || filesize(SUPPORT_FILE)===0;
  if(!is_dir(dirname(SUPPORT_FILE))) @mkdir(dirname(SUPPORT_FILE),0755,true);
  $f=fopen(SUPPORT_FILE,'a');
  if(!$f) return false;
  if($new) fputcsv($f,['created_at','ticket_id','name','phone','email','business','category','urgency','subject','message','ip','status']);
  fputcsv($f,$row);
  fclose($f); return true;
}
function send_html_mail($to,$subject,$html,$replyTo=''){
  $to = trim(str_replace(["\r","\n"],' ',(string)$to));
  $subject = trim(str_replace(["\r","\n"],' ',(string)$subject));
  $replyTo = trim(str_replace(["\r","\n"],' ',(string)$replyTo));
  if(!$to || !filter_var($to,FILTER_VALIDATE_EMAIL)) return false;
  $from = 'hello@lynxcomanalytics.com';
  $headers = "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $headers .= "From: Lynxcom Analytics <{$from}>\r\n";
  if($replyTo && filter_var($replyTo,FILTER_VALIDATE_EMAIL)) $headers .= "Reply-To: {$replyTo}\r\n";
  $ok=@mail($to,$subject,$html,$headers);
  @file_put_contents(DATA_DIR.'/mail.log', date('c').' | '.($ok?'sent':'failed').' | '.$to.' | '.$subject."\n", FILE_APPEND);
  return $ok;
}

function append_lead($row){
  $new = !file_exists(LEADS_FILE) || filesize(LEADS_FILE)===0;
  if(!is_dir(dirname(LEADS_FILE))) @mkdir(dirname(LEADS_FILE),0755,true);
  $f=fopen(LEADS_FILE,'a');
  if(!$f) return false;
  if($new) fputcsv($f,['created_at','name','phone','email','business','service','budget','message','ip','status']);
  fputcsv($f,$row);
  fclose($f); return true;
}

function slugify($text){
  $text = strtolower(trim((string)$text));
  $text = preg_replace('/[^a-z0-9]+/i','-',$text);
  $text = trim($text,'-');
  return $text ?: 'post-'.date('YmdHis');
}

function sync_seed_blog_posts(){
  if(!file_exists(BLOG_SEED_FILE)) return;
  $seed = json_decode(file_get_contents(BLOG_SEED_FILE), true);
  if(!is_array($seed)) return;
  $live = file_exists(BLOG_FILE) ? json_decode(file_get_contents(BLOG_FILE), true) : [];
  if(!is_array($live)) $live=[];
  $changed=false;
  $liveById=[];
  foreach($live as $i=>$p){ if(!empty($p['id'])) $liveById[$p['id']]=$i; }
  foreach($seed as $sp){
    if(empty($sp['id'])) continue;
    $sid=$sp['id'];
    if(!array_key_exists($sid,$liveById)){
      $live[]=$sp; $changed=true; continue;
    }
    $i=$liveById[$sid];
    $liveDate=$live[$i]['updated_at'] ?? '';
    $seedDate=$sp['updated_at'] ?? '';
    $liveVersion=$live[$i]['seed_version'] ?? '';
    $seedVersion=$sp['seed_version'] ?? '';
    if(($seedDate && strcmp($seedDate,$liveDate)>0) || ($seedVersion && $seedVersion!==$liveVersion)){
      // Update only matching seeded posts. Admin-created posts with other IDs are preserved.
      $live[$i]=$sp; $changed=true;
    }
  }
  if($changed){
    if(!is_dir(dirname(BLOG_FILE))) @mkdir(dirname(BLOG_FILE),0755,true);
    @file_put_contents(BLOG_FILE, json_encode(array_values($live), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
  }
}

function load_posts($includeDrafts=false){
  if(!file_exists(BLOG_FILE) && file_exists(BLOG_SEED_FILE)) @copy(BLOG_SEED_FILE, BLOG_FILE);
  sync_seed_blog_posts();
  $json = file_exists(BLOG_FILE) ? file_get_contents(BLOG_FILE) : '[]';
  $posts = json_decode($json,true);
  if(!is_array($posts)) $posts=[];
  usort($posts,function($a,$b){ return strcmp($b['created_at']??'', $a['created_at']??''); });
  if(!$includeDrafts) $posts = array_values(array_filter($posts,function($p){ return ($p['status']??'published')==='published'; }));
  return $posts;
}
function save_posts($posts){
  if(!is_array($posts)) $posts=[];
  $ok = file_put_contents(BLOG_FILE, json_encode(array_values($posts), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
  rebuild_sitemap();
  return $ok;
}
function find_post($slug,$includeDrafts=false){
  foreach(load_posts($includeDrafts) as $p){ if(($p['slug']??'')===$slug) return $p; }
  return null;
}

function clean_html($html){
  $html = (string)$html;
  $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><blockquote><a><img><figure><figcaption>';
  $html = strip_tags($html, $allowed);
  $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i','',$html);
  $html = preg_replace('/javascript\s*:/i','',$html);
  $html = preg_replace('/<a\s+/i','<a target="_blank" rel="noopener" ',$html);
  $html = preg_replace('/<img([^>]*?)>/i','<img$1 loading="lazy">',$html);
  return trim($html);
}
function post_body_html($post){
  if(!empty($post['content_html'])) return clean_html($post['content_html']);
  $paras = preg_split('/\n\s*\n/', trim($post['content']??''));
  $out=''; foreach($paras as $para){ if(trim($para)!=='') $out.='<p>'.h($para).'</p>'; }
  return $out;
}
function cover_image($post){
  $img = trim($post['cover_image'] ?? '');
  return $img ?: 'assets/section-services.jpg';
}
function public_media_url($path){
  $path = trim((string)$path);
  if(preg_match('/^https?:\/\//i',$path)) return $path;
  return 'https://lynxcomanalytics.com/' . ltrim($path,'/');
}

function excerpt($text,$max=170){
  $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$text)));
  return strlen($text)>$max ? substr($text,0,$max-1).'…' : $text;
}
function rebuild_sitemap(){
  $base='https://lynxcomanalytics.com';
  $urls=[
    ['loc'=>$base.'/', 'priority'=>'1.0', 'freq'=>'weekly', 'lastmod'=>date('Y-m-d')],
    ['loc'=>$base.'/blog.php', 'priority'=>'0.9', 'freq'=>'weekly', 'lastmod'=>date('Y-m-d')],
    ['loc'=>$base.'/support.php', 'priority'=>'0.8', 'freq'=>'weekly', 'lastmod'=>date('Y-m-d')],
    ['loc'=>$base.'/ai-automation-agency-nigeria.php', 'priority'=>'0.8', 'freq'=>'weekly', 'lastmod'=>date('Y-m-d')],
    ['loc'=>$base.'/business-dashboard-consulting-nigeria.php', 'priority'=>'0.8', 'freq'=>'weekly', 'lastmod'=>date('Y-m-d')],
    ['loc'=>$base.'/data-analytics-consulting-nigeria.php', 'priority'=>'0.8', 'freq'=>'weekly', 'lastmod'=>date('Y-m-d')],
    ['loc'=>$base.'/customer-follow-up-automation-nigeria.php', 'priority'=>'0.8', 'freq'=>'weekly', 'lastmod'=>date('Y-m-d')]
  ];
  foreach(load_posts(false) as $post){
    if(!empty($post['slug'])) $urls[]=['loc'=>$base.'/post.php?slug='.rawurlencode($post['slug']),'priority'=>'0.7','freq'=>'monthly','lastmod'=>substr($post['updated_at'] ?? ($post['created_at'] ?? date('Y-m-d')),0,10)];
  }
  $xml='<?xml version="1.0" encoding="UTF-8"?>' . "
" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "
";
  $seen=[];
  foreach($urls as $u){
    if(isset($seen[$u['loc']])) continue; $seen[$u['loc']]=true;
    $xml.='  <url>' . "
";
    $xml.='    <loc>'.htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8').'</loc>' . "
";
    $xml.='    <lastmod>'.h($u['lastmod']).'</lastmod>' . "
";
    $xml.='    <changefreq>'.h($u['freq']).'</changefreq>' . "
";
    $xml.='    <priority>'.h($u['priority']).'</priority>' . "
";
    $xml.='  </url>' . "
";
  }
  $xml.='</urlset>' . "
";
  @file_put_contents(APP_ROOT.'/sitemap.xml',$xml);
}

function safe_text($key,$max=3000){ return trim(substr($_POST[$key] ?? '',0,$max)); }
?>