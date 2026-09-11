<?php
require __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: foundation-intake.php'); exit; }
if(!check_csrf()) die('Invalid request.');
if(!empty($_POST['website'])) die('Spam blocked.');

$fields=['lead_name','lead_phone','lead_email','business_name','role','industry','location','years_active','staff_count','branches','current_record_status','why_no_records','current_tools','daily_sales_process','customer_capture_process','payment_tracking_status','expense_tracking_status','stock_or_service_tracking','staff_task_tracking','followup_tracking_status','debtors_or_pending_payments','reports_needed','people_who_will_update','update_frequency','training_needed','preferred_tool','foundation_modules','automation_interest','dashboard_later_goal','success_definition','budget_confirmed','timeline','decision_maker','privacy_consent','extra_notes'];
$data=[]; foreach($fields as $f){ $data[$f]=safe_text($f,4000); }
if(!$data['lead_name'] || !$data['lead_phone'] || !$data['business_name'] || !$data['industry'] || !$data['current_record_status'] || !$data['success_definition'] || !$data['budget_confirmed'] || !$data['timeline'] || !$data['privacy_consent']) die('Please complete the required fields.');
$row=[date('c')]; foreach($fields as $f){ $row[]=$data[$f]; } $row[]=$_SERVER['REMOTE_ADDR']??''; $row[]='new';
append_foundation_intake($row);

$adminEmail='hello@lynxcomanalytics.com';
function fi_h($v){ return h($v); }
$table=''; foreach($data as $k=>$v){ $label=ucwords(str_replace('_',' ',$k)); $table.="<tr><td style='border:1px solid #e5edf5;padding:8px'><strong>".fi_h($label)."</strong></td><td style='border:1px solid #e5edf5;padding:8px'>".nl2br(fi_h($v))."</td></tr>"; }
$adminHtml="<div style='font-family:Arial,sans-serif;line-height:1.6;color:#061b31'><h2>Foundation package intake submitted</h2><p>A Business Data Foundation & Tracking System questionnaire has been completed. This is for a client who may need record structure before dashboard insights.</p><table cellspacing='0' cellpadding='0' style='border-collapse:collapse;border:1px solid #e5edf5;width:100%'>{$table}</table><p><a href='https://wa.me/".preg_replace('/\D/','',$data['lead_phone'])."' style='background:#08275c;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none'>Reply on WhatsApp</a></p></div>";
send_html_mail($adminEmail,'Foundation intake submitted - '.$data['business_name'],$adminHtml,$data['lead_email']);
if($data['lead_email'] && filter_var($data['lead_email'],FILTER_VALIDATE_EMAIL)){
  $clientHtml="<div style='font-family:Arial,sans-serif;line-height:1.7;color:#061b31'><h2>Foundation intake received</h2><p>Thank you, <strong>".fi_h($data['lead_name'])."</strong>. Lynxcom Analytics has received your Business Data Foundation & Tracking System questionnaire.</p><p>We will review how your business currently records sales, customers, payments, expenses, tasks and follow-ups, then recommend the right record structure before any dashboard promise is made.</p><p>Regards,<br><strong>Lynxcom Analytics</strong><br>Business Data Foundation, Dashboards & Workflow Automation</p></div>";
  send_html_mail($data['lead_email'],'Your Lynxcom Foundation intake was received',$clientHtml,$adminEmail);
}
header('Location: foundation-thank-you.php?name='.rawurlencode($data['lead_name']).'&business='.rawurlencode($data['business_name'])); exit;
?>
