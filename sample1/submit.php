<?php
function clean($v){ return trim(str_replace(["\r","\n"], ' ', (string)$v)); }
function safe_redirect($status,$form='admissions',$extra=''){
  $url='portal.php?status=' . rawurlencode($status) . '&form=' . rawurlencode($form);
  if($extra) $url .= '&' . $extra;
  header('Location: ' . $url); exit;
}
function load_valid_students(){
  $students=[];
  $classExpected=[];
  $dataFile=__DIR__ . '/data/dashboard-data.json';
  if(is_file($dataFile)){
    $data=json_decode(file_get_contents($dataFile), true);
    foreach(($data['charts']['fee_by_class'] ?? []) as $feeRow){
      $className=clean($feeRow['class'] ?? ''); $studentCount=0;
      foreach(($data['charts']['enrollment_by_class'] ?? []) as $enrollRow){ if(($enrollRow['label'] ?? '') === $className) $studentCount=(int)($enrollRow['value'] ?? 0); }
      if($className && $studentCount>0) $classExpected[$className]=round(((float)($feeRow['billed'] ?? 0))/$studentCount);
    }
    foreach(($data['debtors'] ?? []) as $d){
      $id=clean($d['student_id'] ?? '');
      if($id){
        $students[$id]=[
          'name'=>clean($d['student_name'] ?? ''),
          'class'=>clean($d['class'] ?? ''),
          'gender'=>clean($d['gender'] ?? ''),
          'guardian'=>clean($d['guardian_name'] ?? ''),
          'phone'=>clean($d['guardian_phone'] ?? ''),
          'section'=>clean($d['section'] ?? ''),
          'expected_amount'=>clean($d['amount_billed'] ?? ($classExpected[clean($d['class'] ?? '')] ?? ''))
        ];
      }
    }
  }
  $admissionCsv=__DIR__ . '/submissions/Admissions.csv';
  if(is_file($admissionCsv) && ($fp=fopen($admissionCsv,'r'))){
    while(($row=fgetcsv($fp))!==false){
      // New format: timestamp, student_id, student_name, class, gender, guardian, phone, source, stage, status, notes
      if(count($row)>=10){
        $id=clean($row[1] ?? ''); $stage=clean($row[8] ?? '');
        if($id && strcasecmp($stage,'Admitted')===0){
          $class=clean($row[3] ?? '');
          $students[$id]=['name'=>clean($row[2]??''),'class'=>$class,'gender'=>clean($row[4]??''),'guardian'=>clean($row[5]??''),'phone'=>clean($row[6]??''),'section'=>str_starts_with($class,'Primary')?'Primary':'Secondary Boarding','expected_amount'=>$classExpected[$class] ?? ''];
        }
      }
    }
    fclose($fp);
  }
  return $students;
}
function next_student_id(){
  $max=0;
  foreach(array_keys(load_valid_students()) as $id){ if(preg_match('/(\d+)$/',$id,$m)) $max=max($max,(int)$m[1]); }
  return 'CMA-' . date('Y') . '-' . str_pad((string)($max+1), 4, '0', STR_PAD_LEFT);
}
function write_csv($formType,$row){
  $base=__DIR__ . '/submissions';
  if(!is_dir($base)) @mkdir($base, 0755, true);
  $file=$base . '/' . $formType . '.csv';
  $fp=fopen($file,'a');
  if(!$fp) return false;
  fputcsv($fp,$row); fclose($fp); return true;
}
function post_webhook($formType,$row){
  $webhook=getenv('SCHOOL_GOOGLE_SHEET_WEBHOOK_URL');
  if($webhook){
    $payload=json_encode(['form_type'=>$formType,'row'=>$row]);
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Content-Type: application/json\r\n",'content'=>$payload,'timeout'=>8]]);
    @file_get_contents($webhook,false,$ctx);
  }
}
if($_SERVER['REQUEST_METHOD']!=='POST') safe_redirect('error');
$formType=clean($_POST['form_type'] ?? '');
$allowed=['Admissions','Fee_Billing','Fee_Payments','Parent_Followup','Expenses','Attendance','Students','Staff','Boarding','Results'];
if(!in_array($formType,$allowed,true)) safe_redirect('error');
$timestamp=date('Y-m-d H:i:s');
$validStudents=load_valid_students();

if($formType==='Admissions'){
  $stage=clean($_POST['stage'] ?? '');
  $studentId = strcasecmp($stage,'Admitted')===0 ? next_student_id() : '';
  $row=[$timestamp,$studentId,clean($_POST['student_name']??''),clean($_POST['applying_class']??''),clean($_POST['gender']??''),clean($_POST['guardian']??''),clean($_POST['phone']??''),clean($_POST['source']??''),$stage,$stage==='Admitted'?'Admitted':'Pending',clean($_POST['notes']??'')];
  if(!write_csv('Admissions',$row)) safe_redirect('error','admissions');
  post_webhook('Admissions',$row);
  if($studentId) safe_redirect('admitted','admissions','sid='.rawurlencode($studentId));
  safe_redirect('success','admissions');
}

if(in_array($formType,['Fee_Payments','Students','Fee_Billing','Parent_Followup','Boarding','Results'],true)){
  $studentId=clean($_POST['student_id'] ?? '');
  if(!$studentId || !isset($validStudents[$studentId])) safe_redirect('not_admitted',[
    'Students'=>'student','Fee_Payments'=>'fee','Fee_Billing'=>'billing','Parent_Followup'=>'followup','Boarding'=>'boarding','Results'=>'results'
  ][$formType] ?? 'student');
  $master=$validStudents[$studentId];
  $selectedClass=clean($_POST['class'] ?? '');
  if($selectedClass && !empty($master['class']) && $selectedClass !== $master['class']) safe_redirect('class_mismatch',[
    'Students'=>'student','Fee_Payments'=>'fee','Fee_Billing'=>'billing','Parent_Followup'=>'followup','Boarding'=>'boarding','Results'=>'results'
  ][$formType] ?? 'student');
}

if($formType==='Fee_Payments'){
  $expectedAmount = clean($master['expected_amount'] ?? '');
  $row=[$timestamp,$studentId,$master['name'],$master['gender'] ?: clean($_POST['gender']??''),$master['class'] ?: clean($_POST['class']??''),$master['guardian'], $master['phone'], clean($_POST['term']??''), $expectedAmount, clean($_POST['amount_paid']??''), clean($_POST['payment_method']??''), clean($_POST['receipt_no']??''), clean($_POST['notes']??'')];
  if(!write_csv('Fee_Payments',$row)) safe_redirect('error','fee');
  post_webhook('Fee_Payments',$row); safe_redirect('success','fee');
}
if($formType==='Students'){
  $row=[$timestamp,$studentId,$master['name'],$master['gender'] ?: clean($_POST['gender']??''),$master['class'] ?: clean($_POST['class']??''),$master['section'] ?: clean($_POST['section']??''),$master['guardian'],$master['phone'],clean($_POST['status']??''),clean($_POST['notes']??'')];
  if(!write_csv('Students',$row)) safe_redirect('error','student');
  post_webhook('Students',$row); safe_redirect('success','student');
}
if($formType==='Fee_Billing'){
  $expected=(float)clean($_POST['expected_amount']??'0'); $discount=(float)clean($_POST['discount']??'0'); $final=max(0,$expected-$discount);
  $row=[$timestamp,$studentId,$master['name'],$master['class'],$master['gender'],$master['guardian'],$master['phone'],clean($_POST['term']??''),clean($_POST['fee_category']??''),$expected,$discount,$final,clean($_POST['due_date']??''),clean($_POST['billing_status']??''),clean($_POST['notes']??'')];
  if(!write_csv('Fee_Billing',$row)) safe_redirect('error','billing');
  post_webhook('Fee_Billing',$row); safe_redirect('success','billing');
}
if($formType==='Parent_Followup'){
  $row=[$timestamp,$studentId,$master['name'],$master['class'],$master['guardian'],$master['phone'],clean($_POST['outstanding_amount']??''),clean($_POST['followup_date']??''),clean($_POST['contact_method']??''),clean($_POST['promise_date']??''),clean($_POST['owner']??''),clean($_POST['followup_status']??''),clean($_POST['notes']??'')];
  if(!write_csv('Parent_Followup',$row)) safe_redirect('error','followup');
  post_webhook('Parent_Followup',$row); safe_redirect('success','followup');
}
if($formType==='Staff'){
  $staffId=clean($_POST['staff_id']??'') ?: ('STF-' . date('Y') . '-' . strtoupper(substr(md5(uniqid('',true)),0,5)));
  $row=[$timestamp,$staffId,clean($_POST['staff_name']??''),clean($_POST['gender']??''),clean($_POST['role']??''),clean($_POST['department']??''),clean($_POST['assigned_class']??''),clean($_POST['phone']??''),clean($_POST['date_joined']??''),clean($_POST['employment_status']??''),clean($_POST['salary_category']??''),clean($_POST['notes']??'')];
  if(!write_csv('Staff',$row)) safe_redirect('error','staff');
  post_webhook('Staff',$row); safe_redirect('success','staff');
}
if($formType==='Boarding'){
  $row=[$timestamp,$studentId,$master['name'],$master['class'],$master['gender'],clean($_POST['hostel']??''),clean($_POST['room']??''),clean($_POST['bed_space']??''),clean($_POST['boarding_status']??''),clean($_POST['emergency_contact']??''),clean($_POST['medical_note']??'')];
  if(!write_csv('Boarding',$row)) safe_redirect('error','boarding');
  post_webhook('Boarding',$row); safe_redirect('success','boarding');
}
if($formType==='Results'){
  $ca=(float)clean($_POST['ca_score']??'0'); $exam=(float)clean($_POST['exam_score']??'0'); $total=$ca+$exam; $grade=$total>=70?'A':($total>=60?'B':($total>=50?'C':($total>=45?'D':($total>=40?'E':'F'))));
  $row=[$timestamp,clean($_POST['term']??''),$studentId,$master['name'],$master['class'],clean($_POST['subject']??''),$ca,$exam,$total,$grade,clean($_POST['teacher']??''),clean($_POST['remarks']??'')];
  if(!write_csv('Results',$row)) safe_redirect('error','results');
  post_webhook('Results',$row); safe_redirect('success','results');
}
if($formType==='Expenses'){
  $row=[$timestamp,clean($_POST['expense_date']??''),clean($_POST['category']??''),clean($_POST['description']??''),clean($_POST['amount']??''),clean($_POST['paid_by']??''),clean($_POST['payment_method']??''),clean($_POST['notes']??'')];
  if(!write_csv('Expenses',$row)) safe_redirect('error','expense');
  post_webhook('Expenses',$row); safe_redirect('success','expense');
}
if($formType==='Attendance'){
  $total=(float)clean($_POST['total_students']??'0'); $present=(float)clean($_POST['present']??'0'); $rate=$total>0 ? round(($present/$total)*100,1) : '';
  $row=[$timestamp,clean($_POST['date']??''),clean($_POST['term']??''),clean($_POST['class']??''),clean($_POST['gender']??''),clean($_POST['section']??''),clean($_POST['total_students']??''),clean($_POST['present']??''),clean($_POST['absent']??''),$rate,clean($_POST['notes']??'')];
  if(!write_csv('Attendance',$row)) safe_redirect('error','attendance');
  post_webhook('Attendance',$row); safe_redirect('success','attendance');
}
safe_redirect('error');
?>