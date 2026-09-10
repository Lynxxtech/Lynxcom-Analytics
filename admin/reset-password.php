<?php
require __DIR__.'/../includes/functions.php';
$resetHash = 'c35ebc9f1a92538f1e38e3e692c71875b0fd9bc4a85d2d08fca897eb9682a092';
$usedFile = STORAGE_DIR . '/admin-reset-used-20260910.flag';
$key = $_GET['key'] ?? $_POST['key'] ?? '';
$valid = $key !== '' && hash_equals($resetHash, hash('sha256', $key));
$error=''; $done=false;
if(file_exists($usedFile)) { http_response_code(410); die('This reset link has already been used.'); }
if(!$valid) { http_response_code(404); die('Reset link not found.'); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pass=$_POST['password']??''; $pass2=$_POST['password2']??'';
  if(strlen($pass)<10) $error='Use at least 10 characters.';
  elseif($pass!==$pass2) $error='Passwords do not match.';
  else{
    $hash=password_hash($pass,PASSWORD_DEFAULT);
    $php="<?php\nreturn ['admin_password_hash' => ".var_export($hash,true)."];\n";
    if(!is_dir(dirname(CONFIG_FILE))) @mkdir(dirname(CONFIG_FILE),0755,true);
    file_put_contents(CONFIG_FILE,$php);
    file_put_contents($usedFile,date('c'));
    $_SESSION=[];
    $done=true;
  }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset LynxCom Admin Password</title><link rel="stylesheet" href="../assets/styles.css?v=reset-20260910"><link rel="stylesheet" href="../assets/admin.css?v=reset-20260910"></head><body class="wp-admin-body"><main class="wp-login-page"><section class="wp-login-card"><div class="wp-login-brand"><div class="wp-brand-mark">L</div><strong>LynxCom Analytics</strong><p style="margin:6px 0 0;color:#c3c4c7">Secure password reset</p></div><div class="wp-login-body"><h1>Reset admin password</h1><?php if($done): ?><div class="success-msg">Password reset complete. This reset link is now locked.</div><p><a class="wp-button wide" href="login.php">Go to admin login</a></p><?php else: ?><p>Create a new private admin password. Do not share it with anyone.</p><?php if($error): ?><div class="alert"><?=h($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="key" value="<?=h($key)?>"><input type="password" name="password" placeholder="New admin password" required autocomplete="new-password"><input type="password" name="password2" placeholder="Confirm new password" required autocomplete="new-password"><button class="wp-button wide" style="margin-top:10px">Reset password</button></form><?php endif; ?></div></section></main></body></html>
