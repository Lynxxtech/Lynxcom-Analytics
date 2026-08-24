<?php require __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: index.php'); exit; }
if(!check_csrf()) die('Invalid request.');
if(!empty($_POST['website'])) die('Spam blocked.');
$name=safe_text('name',120); $phone=safe_text('phone',80); $email=safe_text('email',160); $business=safe_text('business',160); $service=safe_text('service',160); $budget=safe_text('budget',80); $message=safe_text('message',1500);
if(!$name || !$phone || !$message) die('Name, phone and message are required.');
append_lead([date('c'),$name,$phone,$email,$business,$service,$budget,$message,$_SERVER['REMOTE_ADDR']??'','new']);
header('Location: index.php?sent=1#contact'); exit;
?>