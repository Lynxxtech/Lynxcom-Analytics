<?php require __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: index.php'); exit; }
if(!check_csrf()) die('Invalid request.');
if(!empty($_POST['website'])) die('Spam blocked.');

$c = load_content();
$name=safe_text('name',120); $phone=safe_text('phone',80); $email=safe_text('email',160); $business=safe_text('business',160); $service=safe_text('service',160); $budget=safe_text('budget',80); $message=safe_text('message',1500);
if(!$name || !$phone || !$message) die('Name, phone and message are required.');

append_lead([date('c'),$name,$phone,$email,$business,$service,$budget,$message,$_SERVER['REMOTE_ADDR']??'','new']);

function lx_send_html_mail($to,$subject,$html,$replyTo=''){ return send_html_mail($to,$subject,$html,$replyTo); }

$adminEmail = 'hello@lynxcomanalytics.com';
$brand = h($c['brand'] ?? 'Lynxcom Analytics');
$safeName = h($name); $safePhone=h($phone); $safeEmail=h($email); $safeBusiness=h($business); $safeService=h($service); $safeBudget=h($budget); $safeMessage=nl2br(h($message));

$adminHtml = "<div style='font-family:Arial,sans-serif;line-height:1.6;color:#061b31'><h2>New consultation request</h2><p>A new lead submitted the Lynxcom Analytics consultation form.</p><table cellpadding='8' cellspacing='0' style='border-collapse:collapse;border:1px solid #e5edf5'><tr><td><strong>Name</strong></td><td>{$safeName}</td></tr><tr><td><strong>Phone/WhatsApp</strong></td><td>{$safePhone}</td></tr><tr><td><strong>Email</strong></td><td>{$safeEmail}</td></tr><tr><td><strong>Business</strong></td><td>{$safeBusiness}</td></tr><tr><td><strong>Service</strong></td><td>{$safeService}</td></tr><tr><td><strong>Budget</strong></td><td>{$safeBudget}</td></tr><tr><td><strong>Message</strong></td><td>{$safeMessage}</td></tr></table><p><a href='https://wa.me/".preg_replace('/\D/','',$phone)."' style='background:#08275c;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none'>Reply on WhatsApp</a></p></div>";
lx_send_html_mail($adminEmail,'New Lynxcom consultation request from '.$name,$adminHtml,$email);

if($email && filter_var($email,FILTER_VALIDATE_EMAIL)){
  $clientHtml = "<div style='font-family:Arial,sans-serif;line-height:1.7;color:#061b31'><h2>Thank you, {$safeName}</h2><p>Your consultation request has been received by <strong>{$brand}</strong>.</p><p>We will review your message and respond with the best next step for your dashboard, reporting or workflow automation need.</p><div style='background:#f7fbff;border-left:4px solid #0b4fd8;padding:14px 16px;margin:18px 0'><strong>Your request summary</strong><br>Service: {$safeService}<br>Business: {$safeBusiness}<br>Message: {$safeMessage}</div><p>If urgent, you can also chat with us on WhatsApp: <a href='https://wa.me/2348136377667'>+234 813 637 7667</a>.</p><p>Regards,<br><strong>Lynxcom Analytics</strong><br>Business Dashboards, Reporting & Workflow Automation Consulting</p></div>";
  lx_send_html_mail($email,'We received your Lynxcom Analytics consultation request',$clientHtml,$adminEmail);
}

$redirectUrl = 'index.php?sent=1#contact';
if(trim($budget)==='₦75k–₦150k'){
  $redirectUrl = 'starter-intake.php?name='.rawurlencode($name).'&phone='.rawurlencode($phone).'&email='.rawurlencode($email).'&business='.rawurlencode($business);
} elseif(trim($budget)==='₦200k–₦500k'){
  $redirectUrl = 'growth-intake.php?name='.rawurlencode($name).'&phone='.rawurlencode($phone).'&email='.rawurlencode($email).'&business='.rawurlencode($business);
} elseif(trim($budget)==='₦600k–₦1.5m+' || stripos($budget,'600k')!==false || stripos($budget,'1.5m')!==false){
  $redirectUrl = 'premium-intake.php?name='.rawurlencode($name).'&phone='.rawurlencode($phone).'&email='.rawurlencode($email).'&business='.rawurlencode($business);
}
header('Location: '.$redirectUrl); exit;
?>