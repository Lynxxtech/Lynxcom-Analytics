<?php
require __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: premium-intake.php'); exit; }
if(!check_csrf()) die('Invalid request.');
if(!empty($_POST['website'])) die('Spam blocked.');

$fields=['lead_name','lead_phone','lead_email','business_name','role','industry','location','years_active','staff_count','branches','decision_maker','decision_process','project_sponsor','business_overview','primary_goals','success_definition','current_tools','systems_in_use','data_sources','data_volume','data_owners','data_quality','data_cleanup_needed','historical_data_needed','departments_in_scope','dashboard_kpis','management_questions','dashboard_users','dashboard_devices','dashboard_permissions','reporting_cadence','report_exports','tracker_modules','customer_data','customer_journey','customer_followup_channels','customer_automation_needs','sales_process','payment_process','inventory_process','staff_operations','approval_processes','workflow_bottlenecks','automation_priorities','ai_use_cases','ai_boundaries','notification_channels','integrations_needed','api_access_status','existing_sops','documentation_needed','training_users','support_expectation','security_requirements','data_privacy_consent','access_method','sample_data_ready','implementation_timeline','urgency','budget_confirmed','payment_preference','stakeholder_availability','risks_concerns','extra_notes'];
$data=[]; foreach($fields as $f){ $data[$f]=safe_text($f,7000); }
$required=['lead_name','lead_phone','business_name','business_overview','primary_goals','success_definition','data_sources','departments_in_scope','dashboard_kpis','management_questions','workflow_bottlenecks','automation_priorities','data_privacy_consent','budget_confirmed'];
foreach($required as $r){ if(!$data[$r]) die('Please complete the required Premium intake fields.'); }
$row=[date('c')]; foreach($fields as $f){ $row[]=$data[$f]; } $row[]=$_SERVER['REMOTE_ADDR']??''; $row[]='new';
append_premium_intake($row);

$adminEmail='hello@lynxcomanalytics.com';
function pi_h($v){ return h($v); }
$table=''; foreach($data as $k=>$v){ $label=ucwords(str_replace('_',' ',$k)); $table.="<tr><td style='border:1px solid #e5edf5;padding:8px;vertical-align:top;width:32%'><strong>".pi_h($label)."</strong></td><td style='border:1px solid #e5edf5;padding:8px'>".nl2br(pi_h($v))."</td></tr>"; }
$adminHtml="<div style='font-family:Arial,sans-serif;line-height:1.6;color:#061b31'><h2>Premium AI Operations System intake submitted</h2><p>A comprehensive Premium questionnaire has been completed.</p><table cellspacing='0' cellpadding='0' style='border-collapse:collapse;border:1px solid #e5edf5;width:100%'>{$table}</table><p><a href='https://wa.me/".preg_replace('/\D/','',$data['lead_phone'])."' style='background:#08275c;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none'>Reply on WhatsApp</a></p></div>";
send_html_mail($adminEmail,'Premium intake submitted - '.$data['business_name'],$adminHtml,$data['lead_email']);
if($data['lead_email'] && filter_var($data['lead_email'],FILTER_VALIDATE_EMAIL)){
  $clientHtml="<div style='font-family:Arial,sans-serif;line-height:1.7;color:#061b31'><h2>Premium intake received</h2><p>Thank you, <strong>".pi_h($data['lead_name'])."</strong>. Lynxcom Analytics has received your Premium AI Operations System questionnaire.</p><p>We will review your departments, dashboards, data sources, workflow bottlenecks, AI use cases, integrations, access requirements, privacy concerns, timeline and budget fit, then contact you with the next scoping step.</p><p>Please do not send passwords, OTPs, API keys, card details, or private login credentials. If sample records are needed, we will request safe exports/files only.</p><p>Regards,<br><strong>Lynxcom Analytics</strong></p></div>";
  send_html_mail($data['lead_email'],'Your Lynxcom Premium intake was received',$clientHtml,$adminEmail);
}
header('Location: premium-thank-you.php?name='.rawurlencode($data['lead_name']).'&business='.rawurlencode($data['business_name'])); exit;
?>
