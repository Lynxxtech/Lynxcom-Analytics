<?php require __DIR__.'/includes/functions.php';
if(admin_configured()){ die('Admin is already configured. Delete data/config.local.php only if you intentionally want to reset setup.'); }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pass=$_POST['password']??''; $pass2=$_POST['password2']??'';
  if(strlen($pass)<10) $error='Use at least 10 characters.';
  elseif($pass!==$pass2) $error='Passwords do not match.';
  else{
    $hash=password_hash($pass,PASSWORD_DEFAULT);
    $php="<?php\nreturn ['admin_password_hash' => ".var_export($hash,true)."];\n";
    if(!is_dir(dirname(CONFIG_FILE))) @mkdir(dirname(CONFIG_FILE),0755,true);
    file_put_contents(CONFIG_FILE,$php);
    header('Location: admin/login.php?setup=done'); exit;
  }
}
?><!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup Admin</title><link rel="stylesheet" href="assets/styles.css?v=fixed-sticky-header-20260825"></head><body class="admin-bg"><main class="login-card"><h1>Setup admin password</h1><p>Create your private admin password. After setup, this page locks itself.</p><?php if($error): ?><div class="alert"><?=h($error)?></div><?php endif; ?><form method="post"><input type="password" name="password" placeholder="Admin password" required><input type="password" name="password2" placeholder="Confirm password" required><button class="btn primary wide">Create admin</button></form></main></body></html>