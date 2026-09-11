<?php
$hash='8ddc40170762231c2033425f94c82b069f4ffbbed41a1df73514d357c4ce861d';
$k=$_GET['k'] ?? '';
if (!hash_equals($hash, hash('sha256', $k))) { http_response_code(404); exit('Not found'); }
$file=__DIR__.'/data/member-registrations.csv';
if(!file_exists($file)){ echo 'No file found'; exit; }
$rows=array_map('str_getcsv', file($file));
$out=[]; $removed=0;
foreach($rows as $i=>$row){
  $line=implode(' ', $row);
  if($i>0 && (stripos($line,'Demo Preview')!==false || stripos($line,'Client preview test')!==false || stripos($line,'08000000000')!==false || stripos($line,'demo@example.com')!==false)){ $removed++; continue; }
  $out[]=$row;
}
$fp=fopen($file,'w'); foreach($out as $row) fputcsv($fp,$row); fclose($fp);
echo 'Removed demo test rows: '.$removed;
?>