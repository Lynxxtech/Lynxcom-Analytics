<?php
session_start();
define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT . '/data');
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('LEADS_FILE', DATA_DIR . '/leads.csv');
define('BLOG_FILE', DATA_DIR . '/blog.json');
define('CONFIG_FILE', DATA_DIR . '/config.local.php');

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
function append_lead($row){
  $new = !file_exists(LEADS_FILE) || filesize(LEADS_FILE)===0;
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
function load_posts($includeDrafts=false){
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
function excerpt($text,$max=170){
  $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$text)));
  return strlen($text)>$max ? substr($text,0,$max-1).'…' : $text;
}
function rebuild_sitemap(){
  $base='https://lynxcomanalytics.com/';
  $urls=[['loc'=>$base,'priority'=>'1.0'],['loc'=>$base.'blog.php','priority'=>'0.9'],['loc'=>$base.'ai-automation-agency-nigeria.php','priority'=>'0.8'],['loc'=>$base.'business-dashboard-consulting-nigeria.php','priority'=>'0.8'],['loc'=>$base.'data-analytics-consulting-nigeria.php','priority'=>'0.8'],['loc'=>$base.'customer-follow-up-automation-nigeria.php','priority'=>'0.8']];
  foreach(load_posts(false) as $p){ if(!empty($p['slug'])) $urls[]=['loc'=>$base.'post.php?slug='.rawurlencode($p['slug']),'priority'=>'0.7']; }
  $xml='<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
  foreach($urls as $u){ $xml.='  <url><loc>'.h($u['loc']).'</loc><lastmod>'.date('Y-m-d').'</lastmod><changefreq>weekly</changefreq><priority>'.$u['priority'].'</priority></url>' . "\n"; }
  $xml.='</urlset>' . "\n";
  @file_put_contents(APP_ROOT.'/sitemap.xml',$xml);
}

function safe_text($key,$max=3000){ return trim(substr($_POST[$key] ?? '',0,$max)); }
?>