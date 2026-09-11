<?php
header('X-Robots-Tag: noindex, nofollow', true);
$name=trim($_GET['name'] ?? ''); $plan=trim($_GET['plan'] ?? '');
function h($v){ return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registration Received | Smart Gym</title><meta name="robots" content="noindex,nofollow"><link rel="stylesheet" href="assets/styles.css?v=smartgym-v1"></head><body class="thanks"><main class="thanks-card"><span>Smart Gym</span><h1>Thank you<?= $name ? ', '.h($name) : '' ?>.</h1><p>Your <?= $plan ? '<strong>'.h($plan).'</strong>' : 'membership' ?> registration has been received. Our team will contact you with the next step.</p><a class="btn primary" href="index.php">Back to website</a></main></body></html>
