<?php
require __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: growth-intake.php'); exit; }
if(!check_csrf()) die('Invalid request.');
if(!empty($_POST['website'])) die('Spam blocked.');

$fields=['lead_name','lead_phone','lead_email','business_name','role','industry','location','years_active','staff_count','branches','decision_maker','current_tools','records_kept','data_sources','record_owners','record_frequency','data_quality','data_cleanup_needed','sample_data_ready','sales_channels','customer_segments','products_services','monthly_transactions','average_order_value','payment_methods','customer_data_available','customer_followup_method','current_reports','report_frequency','kpis_tracked','management_questions','dashboard_users','dashboard_devices','dashboard_frequency','preferred_dashboard_format','required_dashboard_pages','filtering_needs','access_roles','export_needs','tracker_needs','customer_tracker_needs','staff_tracker_needs','payment_tracker_needs','inventory_tracker_needs','workflow_current','workflow_bottlenecks','workflow_automation_needs','notifications_needed','approval_processes','integrations_needed','training_users','support_expectation','success_definition','budget_confirmed','timeline','urgency','privacy_consent','extra_notes'];
$data=[]; foreach($fields as $f){ $data[$f]=safe_text($f,5000); }
$required=['lead_name','lead_phone','business_name','kpis_tracked','management_questions','workflow_current','workflow_bottlenecks','success_definition','budget_confirmed','privacy_consent'];
foreach($required as $r){ if(!$data[$r]) die('Please complete the required fields.'); }
$row=[date('c')]; foreach($fields as $f){ $row[]=$data[$f]; } $row[]=$_SERVER['REMOTE_ADDR']??''; $row[]='new';
append_growth_intake($row);

$adminEmail='hello@lynxcomanalytics.com';
function gi_h($v){ return h($v); }
$table=''; foreach($data as $k=>$v){ $label=ucwords(str_replace('_',' ',$k)); $table.="<tr><td style='border:1px solid #e5edf5;padding:8px;vertical-align:top'><strong>".gi_h($label)."</strong></td><td style='border:1px solid #e5edf5;padding:8px'>".nl2br(gi_h($v))."</td></tr>"; }
$adminHtml="<div style='font-family:Arial,sans-serif;line-height:1.6;color:#061b31'><h2>Growth package intake submitted</h2><p>A Growth Dashboard & Workflow System questionnaire has been completed.</p><table cellspacing='0' cellpadding='0' style='border-collapse:collapse;border:1px solid #e5edf5;width:100%'>{$table}</table><p><a href='https://wa.me/".preg_replace('/\D/','',$data['lead_phone'])."' style='background:#08275c;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none'>Reply on WhatsApp</a></p></div>";
send_html_mail($adminEmail,'Growth intake submitted - '.$data['business_name'],$adminHtml,$data['lead_email']);
if($data['lead_email'] && filter_var($data['lead_email'],FILTER_VALIDATE_EMAIL)){
  $clientHtml="<div style='font-family:Arial,sans-serif;line-height:1.7;color:#061b31'><h2>Growth intake received</h2><p>Thank you, <strong>".gi_h($data['lead_name'])."</strong>. Lynxcom Analytics has received your Growth Dashboard & Workflow System questionnaire.</p><p>We will review your dashboard needs, trackers, workflow bottlenecks, data sources, reporting expectations, and implementation scope, then contact you with the next step.</p><p>Please do not send passwords, OTPs, card details, or private login credentials. If sample records are needed, we will request safe exports/files only.</p><p>Regards,<br><strong>Lynxcom Analytics</strong></p></div>";
  send_html_mail($data['lead_email'],'Your Lynxcom Growth intake was received',$clientHtml,$adminEmail);
}
header('Location: growth-thank-you.php?name='.rawurlencode($data['lead_name']).'&business='.rawurlencode($data['business_name'])); exit;
?>
