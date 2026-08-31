<?php
function clean($v){ return trim(str_replace(["\r","\n"], ' ', (string)$v)); }
function safe_redirect($status,$form='admissions',$extra=''){
  $url='portal.php?status=' . rawurlencode($status) . '&form=' . rawurlencode($form);
  if($extra) $url .= '&' . $extra;
  header('Location: ' . $url); exit;
}
function load_valid_students(){
  $students=[];
  $dataFile=__DIR__ . '/data/dashboard-data.json';
  if(is_file($dataFile)){
    $data=json_decode(file_get_contents($dataFile), true);
    foreach(($data['debtors'] ?? []) as $d){
      $id=clean($d['student_id'] ?? '');
      if($id){
        $students[$id]=[
          'name'=>clean($d['student_name'] ?? ''),
          'class'=>clean($d['class'] ?? ''),
          'gender'=>clean($d['gender'] ?? ''),
          'guardian'=>clean($d['guardian_name'] ?? ''),
          'phone'=>clean($d['guardian_phone'] ?? ''),
          'section'=>clean($d['section'] ?? '')
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
          $students[$id]=['name'=>clean($row[2]??''),'class'=>$class,'gender'=>clean($row[4]??''),'guardian'=>clean($row[5]??''),'phone'=>clean($row[6]??''),'section'=>str_starts_with($class,'Primary')?'Primary':'Secondary Boarding'];
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
$allowed=['Admissions','Fee_Payments','Expenses','Attendance','Students'];
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

if($formType==='Fee_Payments' || $formType==='Students'){
  $studentId=clean($_POST['student_id'] ?? '');
  if(!$studentId || !isset($validStudents[$studentId])) safe_redirect('not_admitted',$formType==='Students'?'student':'fee');
  $master=$validStudents[$studentId];
  $selectedClass=clean($_POST['class'] ?? '');
  if($selectedClass && !empty($master['class']) && $selectedClass !== $master['class']) safe_redirect('class_mismatch',$formType==='Students'?'student':'fee');
}

if($formType==='Fee_Payments'){
  $row=[$timestamp,$studentId,$master['name'],$master['gender'] ?: clean($_POST['gender']??''),$master['class'] ?: clean($_POST['class']??''),$master['guardian'], $master['phone'], clean($_POST['term']??''), clean($_POST['expected_amount']??''), clean($_POST['amount_paid']??''), clean($_POST['payment_method']??''), clean($_POST['receipt_no']??''), clean($_POST['notes']??'')];
  if(!write_csv('Fee_Payments',$row)) safe_redirect('error','fee');
  post_webhook('Fee_Payments',$row); safe_redirect('success','fee');
}
if($formType==='Students'){
  $row=[$timestamp,$studentId,$master['name'],$master['gender'] ?: clean($_POST['gender']??''),$master['class'] ?: clean($_POST['class']??''),$master['section'] ?: clean($_POST['section']??''),$master['guardian'],$master['phone'],clean($_POST['status']??''),clean($_POST['notes']??'')];
  if(!write_csv('Students',$row)) safe_redirect('error','student');
  post_webhook('Students',$row); safe_redirect('success','student');
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