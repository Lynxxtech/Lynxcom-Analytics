<?php
session_start();
define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT . '/data');
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('LEADS_FILE', DATA_DIR . '/leads.csv');
define('CONFIG_FILE', DATA_DIR . '/config.local.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function load_content(){
  $json = file_exists(CONTENT_FILE) ? file_get_contents(CONTENT_FILE) : '{}';
  $data = json_decode($json, true);
  return is_array($data) ? $data : [];
}
function save_content($data){
  return file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
function csrf_token(){
  if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function check_csrf(){
  return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
}
function config(){
  if(file_exists(CONFIG_FILE)) return require CONFIG_FILE;
  return [];
}
function admin_configured(){ $c=config(); return !empty($c['admin_password_hash']); }
function require_admin(){
  if(empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
}
function lead_rows(){
  if(!file_exists(LEADS_FILE)) return [];
  $f=fopen(LEADS_FILE,'r'); if(!$f) return [];
  $head=fgetcsv($f); $rows=[];
  while(($r=fgetcsv($f))!==false){ $rows[]=array_combine($head,$r); }
  fclose($f); return array_reverse($rows);
}
function append_lead($row){
  $new = !file_exists(LEADS_FILE) || filesize(LEADS_FILE)===0;
  $f=fopen(LEADS_FILE,'a');
  if(!$f) return false;
  if($new) fputcsv($f,['created_at','name','phone','email','business','service','budget','message','ip','status']);
  fputcsv($f,$row);
  fclose($f); return true;
}
function safe_text($key,$max=3000){ return trim(substr($_POST[$key] ?? '',0,$max)); }
?>