<?php
$hash='c127915bdc991a3f14ea802ed9ad7dfeca842aefc2da454b209a143f0a9034dd';
$key=$_GET['key']??'';
if($key==='' || !hash_equals($hash, hash('sha256',$key))) { http_response_code(404); die('Not found'); }
$file=__DIR__.'/data/member-registrations.csv';
if(!file_exists($file)) { header('Content-Type: text/plain'); echo 'No file found'; exit; }
$in=fopen($file,'r');
$header=fgetcsv($in);
$rows=[]; $removed=0;
while(($r=fgetcsv($in))!==false){
  if(($r[1]??'')==='Demo Test Delete' && ($r[3]??'')==='demo@example.com'){ $removed++; continue; }
  $rows[]=$r;
}
fclose($in);
$out=fopen($file,'w');
if($header) fputcsv($out,$header);
foreach($rows as $r) fputcsv($out,$r);
fclose($out);
header('Content-Type: text/plain');
echo 'Removed demo test rows: '.$removed;
?>
