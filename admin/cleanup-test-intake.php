<?php
require __DIR__.'/../includes/functions.php';
$hash='a546b458ee7bf505446ba3710f479e3313ae6f2260adba9019b340c3eeffed39';
$key=$_GET['key']??'';
if($key==='' || !hash_equals($hash, hash('sha256',$key))) { http_response_code(404); die('Not found'); }
$file=STARTER_INTAKE_FILE;
if(!file_exists($file)) die('No starter intake file found');
$in=fopen($file,'r');
$rows=[]; $removed=0; $header=fgetcsv($in);
while(($r=fgetcsv($in))!==false){
  $name=$r[1]??''; $business=$r[4]??'';
  if($name==='Test Delete' && $business==='LynxCom Internal Test'){ $removed++; continue; }
  $rows[]=$r;
}
fclose($in);
$out=fopen($file,'w');
if($header) fputcsv($out,$header);
foreach($rows as $r) fputcsv($out,$r);
fclose($out);
header('Content-Type: text/plain');
echo 'Removed test rows: '.$removed;
?>
