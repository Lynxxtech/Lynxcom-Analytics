<?php
require __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: starter-intake.php'); exit; }
if(!check_csrf()) die('Invalid request.');
if(!empty($_POST['website'])) die('Spam blocked.');

$fields=['lead_name','lead_phone','lead_email','business_name','role','industry','location','years_active','staff_count','branches','current_tools','records_kept','record_location','record_frequency','data_quality','sales_channels','products_services','monthly_transactions','current_reports','kpis_tracked','top_business_goals','biggest_reporting_pain','repetitive_tasks','customer_followup_method','missed_followups','payment_tracking','debtor_tracking','inventory_tracking','staff_tracking','customer_data_available','data_formats_available','access_method','sample_data_ready','dashboard_users','dashboard_frequency','preferred_dashboard_format','automation_priorities','success_definition','budget_confirmed','timeline','decision_maker','training_needed','privacy_consent','extra_notes'];
$data=[]; foreach($fields as $f){ $data[$f]=safe_text($f,4000); }
if(!$data['lead_name'] || !$data['lead_phone'] || !$data['business_name'] || !$data['biggest_reporting_pain'] || !$data['success_definition'] || !$data['budget_confirmed'] || !$data['privacy_consent']) die('Please complete the required fields.');
$row=[date('c')]; foreach($fields as $f){ $row[]=$data[$f]; } $row[]=$_SERVER['REMOTE_ADDR']??''; $row[]='new';
append_starter_intake($row);

$adminEmail='hello@lynxcomanalytics.com';
function si_h($v){ return h($v); }
$table=''; foreach($data as $k=>$v){ $label=ucwords(str_replace('_',' ',$k)); $table.="<tr><td style='border:1px solid #e5edf5;padding:8px'><strong>".si_h($label)."</strong></td><td style='border:1px solid #e5edf5;padding:8px'>".nl2br(si_h($v))."</td></tr>"; }
$adminHtml="<div style='font-family:Arial,sans-serif;line-height:1.6;color:#061b31'><h2>Starter package intake submitted</h2><p>A Starter Dashboard & Automation Audit questionnaire has been completed.</p><table cellspacing='0' cellpadding='0' style='border-collapse:collapse;border:1px solid #e5edf5;width:100%'>{$table}</table><p><a href='https://wa.me/".preg_replace('/\D/','',$data['lead_phone'])."' style='background:#08275c;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none'>Reply on WhatsApp</a></p></div>";
send_html_mail($adminEmail,'Starter intake submitted - '.$data['business_name'],$adminHtml,$data['lead_email']);
if($data['lead_email'] && filter_var($data['lead_email'],FILTER_VALIDATE_EMAIL)){
  $clientHtml="<div style='font-family:Arial,sans-serif;line-height:1.7;color:#061b31'><h2>Starter intake received</h2><p>Thank you, <strong>".si_h($data['lead_name'])."</strong>. Lynxcom Analytics has received your Starter package questionnaire.</p><p>We will review your business records, reporting needs, KPI gaps and automation opportunities, then contact you with the next step for your Dashboard & Automation Audit.</p><p>Regards,<br><strong>Lynxcom Analytics</strong></p></div>";
  send_html_mail($data['lead_email'],'Your Lynxcom Starter intake was received',$clientHtml,$adminEmail);
}
header('Location: starter-intake.php?sent=1'); exit;
?>
