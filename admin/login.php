<?php
require __DIR__.'/../includes/functions.php';
if(!admin_configured()){
  header('Location: ../setup.php');
  exit;
}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $c=config();
  if(password_verify($_POST['password']??'', $c['admin_password_hash'])){
    $_SESSION['admin_logged_in']=true;
    header('Location: index.php');
    exit;
  } else {
    $error='Wrong password.';
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Lynxcom Analytics Admin Login</title>
  <link rel="stylesheet" href="../assets/styles.css?v=lynxcom-admin-wp-20260825">
  <link rel="stylesheet" href="../assets/admin.css?v=lynxcom-admin-wp-20260825">
</head>
<body class="wp-admin-body">
  <main class="wp-login-page">
    <section class="wp-login-card">
      <div class="wp-login-brand">
        <div class="wp-brand-mark">L</div>
        <strong>Lynxcom Analytics</strong>
        <p style="margin:6px 0 0;color:#c3c4c7">Admin backend</p>
      </div>
      <div class="wp-login-body">
        <h1>Log in</h1>
        <p>Enter the admin password to manage leads, support tickets, posts, and site content.</p>
        <?php if(isset($_GET['setup'])): ?><div class="success-msg">Setup complete. Log in now.</div><?php endif; ?>
        <?php if($error): ?><div class="alert"><?=h($error)?></div><?php endif; ?>
        <form method="post">
          <input type="password" name="password" placeholder="Admin password" required autocomplete="current-password">
          <button class="wp-button wide" style="margin-top:10px">Log in</button>
        </form>
        <p><a href="../index.php">← Back to website</a></p>
      </div>
    </section>
  </main>
</body>
</html>
