<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ header('Location: index.php#join'); exit; }
function clean($key,$max=500){ return trim(substr(str_replace(["\r","\n"], ' ', $_POST[$key] ?? ''),0,$max)); }
$name=clean('name',120); $phone=clean('phone',80); $email=clean('email',160); $plan=clean('plan',120); $goal=clean('goal',600);
if(!$name || !$phone){ http_response_code(422); die('Name and phone are required.'); }
$file=__DIR__.'/data/member-registrations.csv';
$new=!file_exists($file);
$fp=fopen($file,'a');
if($new) fputcsv($fp,['date','name','phone','email','plan','goal','ip']);
fputcsv($fp,[date('c'),$name,$phone,$email,$plan,$goal,$_SERVER['REMOTE_ADDR'] ?? '']);
fclose($fp);
header('Location: thank-you.php?name='.rawurlencode($name).'&plan='.rawurlencode($plan)); exit;
?>
