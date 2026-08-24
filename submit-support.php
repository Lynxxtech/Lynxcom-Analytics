<?php require __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: support.php'); exit; }
if(!check_csrf()) die('Invalid request.');
if(!empty($_POST['website'])) die('Spam blocked.');
$c=load_content();
$name=safe_text('name',120); $phone=safe_text('phone',80); $email=safe_text('email',160); $business=safe_text('business',160); $category=safe_text('category',120); $urgency=safe_text('urgency',80); $subject=safe_text('subject',180); $message=safe_text('message',2000);
if(!$name || !$phone || !$subject || !$message) die('Name, phone, subject and message are required.');
$ticket='LX-'.date('Ymd-His');
append_support([date('c'),$ticket,$name,$phone,$email,$business,$category,$urgency,$subject,$message,$_SERVER['REMOTE_ADDR']??'','new']);
$adminEmail='hello@lynxcomanalytics.com';
$safeName=h($name); $safePhone=h($phone); $safeEmail=h($email); $safeBusiness=h($business); $safeCategory=h($category); $safeUrgency=h($urgency); $safeSubject=h($subject); $safeMessage=nl2br(h($message)); $safeTicket=h($ticket);
$adminHtml="<div style='font-family:Arial,sans-serif;line-height:1.6;color:#061b31'><h2>New Lynxcom support ticket: {$safeTicket}</h2><p>A customer/prospect submitted a support request.</p><table cellpadding='8' cellspacing='0' style='border-collapse:collapse;border:1px solid #e5edf5'><tr><td><strong>Ticket</strong></td><td>{$safeTicket}</td></tr><tr><td><strong>Name</strong></td><td>{$safeName}</td></tr><tr><td><strong>Phone/WhatsApp</strong></td><td>{$safePhone}</td></tr><tr><td><strong>Email</strong></td><td>{$safeEmail}</td></tr><tr><td><strong>Business</strong></td><td>{$safeBusiness}</td></tr><tr><td><strong>Category</strong></td><td>{$safeCategory}</td></tr><tr><td><strong>Urgency</strong></td><td>{$safeUrgency}</td></tr><tr><td><strong>Subject</strong></td><td>{$safeSubject}</td></tr><tr><td><strong>Message</strong></td><td>{$safeMessage}</td></tr></table><p><strong>Benny support note:</strong> Review the customer context, classify the need, and draft a clear helpful response before any final message is sent.</p><p><a href='https://wa.me/".preg_replace('/\D/','',$phone)."' style='background:#08275c;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none'>Reply on WhatsApp</a></p></div>";
send_html_mail($adminEmail,"[Lynxcom Support] {$ticket} - {$subject}",$adminHtml,$email);
if($email && filter_var($email,FILTER_VALIDATE_EMAIL)){
  $clientHtml="<div style='font-family:Arial,sans-serif;line-height:1.7;color:#061b31'><h2>Support request received</h2><p>Hello {$safeName}, your support request has been received by <strong>Lynxcom Analytics</strong>.</p><p><strong>Ticket ID:</strong> {$safeTicket}</p><div style='background:#f7fbff;border-left:4px solid #0b4fd8;padding:14px 16px;margin:18px 0'><strong>{$safeSubject}</strong><br>{$safeMessage}</div><p>We will review your request and respond with the next best step. If urgent, you can also chat with us on WhatsApp: <a href='https://wa.me/2348136377667'>+234 813 637 7667</a>.</p><p>Regards,<br><strong>Lynxcom Analytics Support</strong></p></div>";
  send_html_mail($email,"Support request received - {$ticket}",$clientHtml,$adminEmail);
}
header('Location: support.php?sent=1#support'); exit;
?>