<?php
$hash='5412485f9d0f7e06822d28148554d863f0da2f9056adbf1b165b63e73b998096';
$k=$_GET['k'] ?? '';
if (!hash_equals($hash, hash('sha256', $k))) { http_response_code(404); exit('Not found'); }
$file=__DIR__.'/data/member-registrations.csv';
if(!file_exists($file)){ echo 'No file found'; exit; }
$rows=array_map('str_getcsv', file($file));
$out=[]; $removed=0;
foreach($rows as $i=>$row){
  $line=implode(' ', $row);
  if($i>0 && (stripos($line,'Metric Test Delete')!==false || stripos($line,'metric-test@example.com')!==false || stripos($line,'Metric verification only')!==false)){ $removed++; continue; }
  $out[]=$row;
}
$fp=fopen($file,'w'); foreach($out as $row) fputcsv($fp,$row); fclose($fp);
echo 'Removed metric test rows: '.$removed;
?>