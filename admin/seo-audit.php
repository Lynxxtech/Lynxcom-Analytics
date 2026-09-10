<?php
require __DIR__.'/../includes/functions.php';
require_admin();

function lx_normalize_url($url){
  $url=trim((string)$url);
  if($url==='') $url='https://lynxcomanalytics.com/';
  if(!preg_match('~^https?://~i',$url)) $url='https://'.$url;
  return $url;
}
function lx_same_host($base,$url){
  $bh=parse_url($base, PHP_URL_HOST); $uh=parse_url($url, PHP_URL_HOST);
  if(!$bh || !$uh) return false;
  return strtolower(preg_replace('/^www\./','',$bh))===strtolower(preg_replace('/^www\./','',$uh));
}
function lx_abs_url($base,$href){
  $href=trim(html_entity_decode((string)$href,ENT_QUOTES|ENT_HTML5));
  if($href==='' || preg_match('~^(mailto:|tel:|sms:|whatsapp:|javascript:|#)~i',$href)) return '';
  if(preg_match('~^https?://~i',$href)) return $href;
  $p=parse_url($base); if(!$p || empty($p['scheme']) || empty($p['host'])) return '';
  if(str_starts_with($href,'//')) return $p['scheme'].':'.$href;
  if(str_starts_with($href,'/')) return $p['scheme'].'://'.$p['host'].$href;
  $path=$p['path']??'/'; $dir=preg_replace('~/[^/]*$~','/',$path);
  return $p['scheme'].'://'.$p['host'].$dir.$href;
}
function lx_fetch_page($url){
  $ctx=stream_context_create(['http'=>['timeout'=>8,'ignore_errors'=>true,'user_agent'=>'LynxCom-SEO-Audit/1.0 (+https://lynxcomanalytics.com)']]);
  $start=microtime(true); $html=@file_get_contents($url,false,$ctx); $ms=(microtime(true)-$start)*1000;
  $status=0;
  $headers=$http_response_header??[];
  if(!empty($headers[0]) && preg_match('~\s(\d{3})\s~',$headers[0],$m)) $status=(int)$m[1];
  return ['url'=>$url,'html'=>$html===false?'':$html,'status'=>$status,'ms'=>round($ms),'headers'=>$headers,'bytes'=>$html===false?0:strlen($html)];
}
function lx_text_len($html){ $t=trim(preg_replace('/\s+/',' ',strip_tags(preg_replace('~<(script|style|noscript)[^>]*>.*?</\1>~is',' ',$html)))); return strlen($t); }
function lx_meta_content($html,$name){
  if(preg_match('~<meta[^>]+(?:name|property)=["\']'.preg_quote($name,'~').'["\'][^>]*content=["\']([^"\']*)["\'][^>]*>~i',$html,$m)) return html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5);
  if(preg_match('~<meta[^>]+content=["\']([^"\']*)["\'][^>]*(?:name|property)=["\']'.preg_quote($name,'~').'["\'][^>]*>~i',$html,$m)) return html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5);
  return '';
}
function lx_page_score($checks){
  $score=100; foreach($checks as $c){ if(($c['status']??'')==='fail') $score-=($c['weight']??8); elseif(($c['status']??'')==='warn') $score-=max(2,(int)(($c['weight']??8)/2)); }
  return max(0,$score);
}
function lx_audit_page($url,$base){
  $f=lx_fetch_page($url); $html=$f['html']; $checks=[]; $links=[]; $images=[];
  if($f['status']<200 || $f['status']>=400 || $html===''){
    return $f+['title'=>'','description'=>'','h1s'=>[],'checks'=>[['status'=>'fail','label'=>'Page fetch failed','detail'=>'HTTP status '.$f['status'],'weight'=>25]],'score'=>0,'links'=>[],'images'=>[],'text_len'=>0];
  }
  preg_match('~<title[^>]*>(.*?)</title>~is',$html,$tm); $title=trim(html_entity_decode(strip_tags($tm[1]??''),ENT_QUOTES|ENT_HTML5));
  $desc=lx_meta_content($html,'description');
  preg_match_all('~<h1[^>]*>(.*?)</h1>~is',$html,$hm); $h1s=array_map(fn($x)=>trim(preg_replace('/\s+/',' ',html_entity_decode(strip_tags($x),ENT_QUOTES|ENT_HTML5))),$hm[1]??[]);
  preg_match_all('~<a[^>]+href=["\']([^"\']+)["\'][^>]*>~i',$html,$am); foreach($am[1]??[] as $href){ $u=lx_abs_url($url,$href); if($u && lx_same_host($base,$u)){ $u=preg_replace('/#.*$/','',$u); $links[$u]=true; }}
  preg_match_all('~<img\b[^>]*>~i',$html,$im); foreach($im[0]??[] as $tag){ preg_match('~src=["\']([^"\']+)~i',$tag,$sm); preg_match('~alt=["\']([^"\']*)~i',$tag,$altm); $src=lx_abs_url($url,$sm[1]??''); $alt=trim($altm[1]??''); $images[]=['src'=>$src,'alt'=>$alt]; }
  $canonical=''; if(preg_match('~<link[^>]+rel=["\']canonical["\'][^>]*href=["\']([^"\']+)~i',$html,$cm)) $canonical=html_entity_decode($cm[1],ENT_QUOTES|ENT_HTML5);
  $textLen=lx_text_len($html);
  $checks[]=['label'=>'HTTP reachable','status'=>($f['status']>=200&&$f['status']<300)?'pass':'warn','detail'=>'HTTP '.$f['status'].' · '.$f['ms'].'ms','weight'=>20];
  $checks[]=['label'=>'Page title','status'=>($title===''?'fail':(strlen($title)>65?'warn':'pass')),'detail'=>$title===''?'Missing title':strlen($title).' characters','weight'=>14];
  $checks[]=['label'=>'Meta description','status'=>($desc===''?'fail':((strlen($desc)<80||strlen($desc)>170)?'warn':'pass')),'detail'=>$desc===''?'Missing description':strlen($desc).' characters','weight'=>14];
  $checks[]=['label'=>'Single H1','status'=>(count(array_filter($h1s))===1?'pass':(count(array_filter($h1s))===0?'fail':'warn')),'detail'=>count(array_filter($h1s)).' H1 found','weight'=>10];
  $checks[]=['label'=>'Canonical URL','status'=>$canonical?'pass':'warn','detail'=>$canonical?:'Missing canonical tag','weight'=>8];
  $missingAlt=count(array_filter($images,fn($i)=>$i['alt']===''));
  $checks[]=['label'=>'Image alt text','status'=>$missingAlt===0?'pass':($missingAlt>3?'fail':'warn'),'detail'=>$missingAlt.' image(s) missing alt text','weight'=>10];
  $checks[]=['label'=>'Indexable robots','status'=>preg_match('~<meta[^>]+name=["\']robots["\'][^>]+noindex~i',$html)?'fail':'pass','detail'=>preg_match('~noindex~i',lx_meta_content($html,'robots'))?'Noindex found':'No noindex found','weight'=>16];
  $checks[]=['label'=>'Content depth','status'=>$textLen>=600?'pass':($textLen>=300?'warn':'fail'),'detail'=>$textLen.' text characters','weight'=>8];
  $checks[]=['label'=>'Page weight','status'=>$f['bytes']<350000?'pass':($f['bytes']<900000?'warn':'fail'),'detail'=>round($f['bytes']/1024,1).' KB HTML','weight'=>8];
  return $f+['title'=>$title,'description'=>$desc,'h1s'=>$h1s,'canonical'=>$canonical,'checks'=>$checks,'score'=>lx_page_score($checks),'links'=>array_keys($links),'images'=>$images,'text_len'=>$textLen];
}
function lx_run_site_audit($start,$limit=12){
  $start=lx_normalize_url($start); $queue=[$start]; $seen=[]; $pages=[]; $base=$start;
  while($queue && count($pages)<$limit){
    $url=array_shift($queue); $url=preg_replace('/#.*$/','',$url); if(isset($seen[$url])) continue; $seen[$url]=true;
    $page=lx_audit_page($url,$base); $pages[]=$page;
    foreach($page['links'] as $l){ if(count($queue)+count($seen)>$limit*4) break; if(!isset($seen[$l]) && lx_same_host($base,$l) && !preg_match('~\.(pdf|jpg|jpeg|png|gif|webp|svg|css|js|zip)$~i',$l)) $queue[]=$l; }
  }
  $allChecks=[]; foreach($pages as $p){ foreach($p['checks'] as $c){ $allChecks[]=$c; }}
  $fails=count(array_filter($allChecks,fn($c)=>$c['status']==='fail')); $warns=count(array_filter($allChecks,fn($c)=>$c['status']==='warn')); $passes=count(array_filter($allChecks,fn($c)=>$c['status']==='pass'));
  $avg=$pages?round(array_sum(array_column($pages,'score'))/count($pages)):0;
  return ['created_at'=>date('c'),'start_url'=>$start,'pages_crawled'=>count($pages),'average_score'=>$avg,'passes'=>$passes,'warnings'=>$warns,'fails'=>$fails,'pages'=>$pages];
}

$url=$_POST['url']??'https://lynxcomanalytics.com/'; $limit=max(3,min(30,(int)($_POST['limit']??12))); $report=null; $jsonPath='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!check_csrf()) die('Invalid request.');
  $report=lx_run_site_audit($url,$limit);
  $jsonPath=STORAGE_DIR.'/seo-audit-latest.json';
  @file_put_contents($jsonPath,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>LynxCom SEO Audit</title><link rel="stylesheet" href="../assets/styles.css?v=seo-audit-20260910"><link rel="stylesheet" href="../assets/admin.css?v=seo-audit-20260910"><link rel="stylesheet" href="../assets/seo-audit.css?v=seo-audit-20260910"></head>
<body class="wp-admin-body"><div class="wp-admin-shell"><aside class="wp-sidebar"><div class="wp-brand"><div class="wp-brand-mark">L</div><div><strong>LynxCom Analytics</strong><span>SEO command centre</span></div></div><nav class="wp-menu"><a href="index.php"><span class="dashicon">▦</span> Admin Dashboard</a><a class="active" href="seo-audit.php"><span class="dashicon">◎</span> SEO Audit</a><div class="wp-menu-divider"></div><a href="../index.php" target="_blank" rel="noopener"><span class="dashicon">⌂</span> View Website</a><a href="logout.php"><span class="dashicon">⇥</span> Logout</a></nav><div class="wp-sidebar-note">Internal LynxCom tool: crawl pages, score SEO basics, and identify fixes before publishing content.</div></aside>
<main class="wp-main"><header class="wp-topbar"><div class="wp-topbar-title">Website SEO Audit</div><div class="wp-topbar-actions"><a class="wp-button secondary" href="index.php">Back to admin</a><a class="wp-button secondary" href="../index.php" target="_blank" rel="noopener">View site</a></div></header><div class="wp-content"><div class="wp-page-head"><div><h1>LynxCom Website SEO Audit</h1><p>Audit website pages for title, meta description, H1, canonical, image alt text, indexability, content depth and page weight.</p></div></div>
<section class="wp-card"><div class="wp-card-head"><div><h2>Run audit</h2><p>Default target is the live LynxCom website. Keep the page limit modest on shared hosting.</p></div></div><div class="wp-card-body"><form method="post" class="seo-audit-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><div class="form-row"><input name="url" value="<?=h($url)?>" placeholder="https://lynxcomanalytics.com/"><input type="number" name="limit" min="3" max="30" value="<?=h($limit)?>"></div><button class="wp-button">Run SEO audit</button></form></div></section>
<?php if($report): ?><section class="wp-stats-grid"><div class="wp-stat-card"><div><span>Average score</span><strong><?=h($report['average_score'])?>%</strong><em><?=h($report['pages_crawled'])?> pages crawled</em></div><div class="wp-stat-icon">◎</div></div><div class="wp-stat-card"><div><span>Passed checks</span><strong><?=h($report['passes'])?></strong><em>Healthy items</em></div><div class="wp-stat-icon">✓</div></div><div class="wp-stat-card"><div><span>Warnings</span><strong><?=h($report['warnings'])?></strong><em>Needs review</em></div><div class="wp-stat-icon">!</div></div><div class="wp-stat-card"><div><span>Failed checks</span><strong><?=h($report['fails'])?></strong><em>Fix first</em></div><div class="wp-stat-icon">×</div></div></section>
<section class="wp-card"><div class="wp-card-head"><div><h2>Audit results</h2><p>Latest report saved privately on server as <code>seo-audit-latest.json</code>.</p></div><span class="wp-count-pill"><?=h($report['pages_crawled'])?> pages</span></div><div class="wp-card-body"><div class="table-wrap"><table><thead><tr><th>Score</th><th>Page</th><th>Title / Description</th><th>Checks</th></tr></thead><tbody><?php foreach($report['pages'] as $p): ?><tr><td><strong><?=h($p['score'])?>%</strong><br><small>HTTP <?=h($p['status'])?> · <?=h($p['ms'])?>ms</small></td><td><a href="<?=h($p['url'])?>" target="_blank" rel="noopener"><?=h($p['url'])?></a><br><small><?=h(round(($p['bytes']??0)/1024,1))?> KB HTML · <?=h($p['text_len']??0)?> chars</small></td><td><strong><?=h($p['title']?:'Missing title')?></strong><br><small><?=h($p['description']?:'Missing meta description')?></small></td><td><ul class="seo-check-list"><?php foreach($p['checks'] as $c): ?><li class="seo-<?=h($c['status'])?>"><b><?=h(strtoupper($c['status']))?></b> <?=h($c['label'])?> — <span><?=h($c['detail'])?></span></li><?php endforeach; ?></ul></td></tr><?php endforeach; ?></tbody></table></div></div></section><?php endif; ?>
</div></main></div></body></html>
