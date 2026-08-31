<?php
function clean($v){ return trim(str_replace(["\r","\n"], ' ', (string)$v)); }
$formType = clean($_POST['form_type'] ?? '');
$allowed = ['Admissions','Fee_Payments','Expenses','Attendance','Students'];
if(!in_array($formType, $allowed, true)){ header('Location: portal.php?status=error'); exit; }
$base = __DIR__ . '/submissions';
if(!is_dir($base)) @mkdir($base, 0755, true);
$timestamp = date('Y-m-d H:i:s');
$rows = [
 'Admissions' => [$timestamp, clean($_POST['student_name']??''), clean($_POST['applying_class']??''), clean($_POST['gender']??''), clean($_POST['guardian']??''), clean($_POST['phone']??''), clean($_POST['source']??''), clean($_POST['stage']??''), 'Pending', clean($_POST['notes']??'')],
 'Fee_Payments' => [$timestamp, clean($_POST['student_name']??''), clean($_POST['student_id']??''), clean($_POST['gender']??''), clean($_POST['class']??''), clean($_POST['guardian']??''), clean($_POST['phone']??''), clean($_POST['term']??''), clean($_POST['amount_paid']??''), clean($_POST['payment_method']??''), clean($_POST['receipt_no']??''), clean($_POST['notes']??'')],
 'Expenses' => [$timestamp, clean($_POST['expense_date']??''), clean($_POST['category']??''), clean($_POST['description']??''), clean($_POST['amount']??''), clean($_POST['paid_by']??''), clean($_POST['payment_method']??''), clean($_POST['notes']??'')],
 'Attendance' => [$timestamp, clean($_POST['date']??''), clean($_POST['class']??''), clean($_POST['gender']??''), clean($_POST['section']??''), clean($_POST['total_students']??''), clean($_POST['present']??''), clean($_POST['absent']??''), '', clean($_POST['notes']??'')],
 'Students' => [$timestamp, clean($_POST['student_name']??''), clean($_POST['student_id']??''), clean($_POST['gender']??''), clean($_POST['class']??''), clean($_POST['section']??''), clean($_POST['guardian']??''), clean($_POST['phone']??''), clean($_POST['status']??''), clean($_POST['notes']??'')]
];
$row = $rows[$formType];
if($formType==='Attendance'){
  $total=(float)$row[5]; $present=(float)$row[6]; $row[8]=$total>0 ? round(($present/$total)*100,1) : '';
}
$file = $base . '/' . $formType . '.csv';
$fp = fopen($file, 'a');
if(!$fp){ header('Location: portal.php?status=error'); exit; }
fputcsv($fp, $row); fclose($fp);
// Webhook-ready: for a real client, set SCHOOL_GOOGLE_SHEET_WEBHOOK_URL in server config and POST this row to Google Apps Script.
$webhook = getenv('SCHOOL_GOOGLE_SHEET_WEBHOOK_URL');
if($webhook){
  $payload=json_encode(['form_type'=>$formType,'row'=>$row]);
  $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Content-Type: application/json\r\n",'content'=>$payload,'timeout'=>8]]);
  @file_get_contents($webhook, false, $ctx);
}
$map=['Admissions'=>'admissions','Fee_Payments'=>'fee','Expenses'=>'expense','Attendance'=>'attendance','Students'=>'student'];
header('Location: portal.php?status=success&form=' . $map[$formType]); exit;
?>