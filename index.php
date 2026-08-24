<?php require __DIR__.'/includes/functions.php'; $c=load_content(); $wa=preg_replace('/\D/','',$c['whatsapp']??'2348136377667'); ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($c['brand']??'Lynxcom Analytics')?> | <?=h($c['tagline']??'AI dashboards and automation')?></title>
<meta name="description" content="<?=h($c['subheadline']??'AI-powered business dashboards and automation consulting')?>">
<meta name="theme-color" content="#08275c">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="site-header">
  <nav class="nav wrap">
    <a class="brand" href="#top"><span class="brand-mark">LA</span><span><?=h($c['brand']??'Lynxcom Analytics')?></span></a>
    <button class="nav-toggle" aria-label="Open menu">☰</button>
    <div class="nav-links" id="navLinks"><a href="#services">Services</a><a href="#process">Process</a><a href="#packages">Packages</a><a href="#security">Security</a><a href="#contact" class="nav-cta">Request consultation</a></div>
  </nav>
</header>
<main id="top">
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <div class="eyebrow">Business intelligence • AI automation • Reporting systems</div>
      <h1><?=h($c['headline']??'See your business clearly.')?></h1>
      <p class="lead"><?=h($c['subheadline']??'We build dashboards and workflow systems.')?></p>
      <div class="hero-actions"><a class="btn primary" href="https://wa.me/<?=$wa?>?text=Hi%2C%20I%20want%20to%20set%20up%20a%20dashboard%20and%20automation%20system" target="_blank" rel="noopener">Start on WhatsApp</a><a class="btn secondary" href="#contact">Send enquiry</a></div>
    </div>
    <aside class="command-panel" aria-label="Dashboard preview">
      <div class="panel-title"><span>LYNXCOM COMMAND VIEW</span><b>Live metrics</b></div>
      <div class="stat-row"><div><small>Monthly revenue tracked</small><strong>₦24.8M</strong><em>+18.4%</em></div><div><small>Follow-up queue</small><strong>42</strong><em>12 urgent</em></div></div>
      <div class="analytics-grid"><div class="line-chart"><span style="height:35%"></span><span style="height:61%"></span><span style="height:48%"></span><span style="height:78%"></span><span style="height:55%"></span><span style="height:88%"></span><span style="height:72%"></span></div><div class="insight-box"><b>AI recommendation</b><p>Prioritize inactive customers, automate weekly reporting and flag revenue drops early.</p></div></div>
      <div class="queue"><p><span></span> Dashboard updated</p><p><span></span> Data quality passed</p><p><i></i> 7 workflow gaps found</p></div>
    </aside>
  </div>
</section>
<section class="signal-strip"><div class="wrap signal-grid"><span>Dashboards</span><span>Automation</span><span>Data cleanup</span><span>KPI reporting</span><span>Management insights</span></div></section>
<section class="section wrap" id="services"><div class="section-head"><p class="kicker">What we deliver</p><h2>One system for dashboards, reporting and automation strategy.</h2><p>We help you move from scattered records to a clear operating system that tells you what is happening, what needs attention and what can be automated.</p></div><div class="service-grid"><?php foreach($c['services']??[] as $s): ?><article class="service-card"><span><?=h($s['icon']??'')?></span><h3><?=h($s['title']??'Service')?></h3><p><?=h($s['body']??'')?></p></article><?php endforeach; ?></div></section>
<section class="dark-band"><div class="wrap split"><div><p class="kicker inverse">Why it matters</p><h2>Better numbers. Faster follow-up. Less manual admin.</h2><p>Your business does not need more scattered files. It needs a command system that shows performance, detects gaps and turns reports into action.</p></div><div class="outcomes"><div><b>01</b><span>Know your real numbers without waiting for manual reports.</span></div><div><b>02</b><span>Track customer, staff, sales and operational performance in one place.</span></div><div><b>03</b><span>Identify repetitive work AI can simplify or automate.</span></div><div><b>04</b><span>Make weekly decisions from data, not guesswork.</span></div></div></div></section>
<section class="section wrap"><div class="section-head center"><p class="kicker">Who we serve</p><h2>For service businesses, field teams and organizations that depend on records.</h2></div><div class="pill-grid"><?php foreach($c['industries']??[] as $i): ?><span><?=h($i)?></span><?php endforeach; ?></div></section>
<section class="section process" id="process"><div class="wrap"><div class="section-head"><p class="kicker">Our process</p><h2>From diagnosis to deployment.</h2></div><div class="process-grid"><div><b>Assess</b><p>We understand your business records, reporting pain, goals and current tools.</p></div><div><b>Structure</b><p>We clean and organize your data so the dashboard is reliable.</p></div><div><b>Build</b><p>We create dashboards, trackers and reporting views for management decisions.</p></div><div><b>Automate</b><p>We design practical workflows for reminders, reports, follow-ups and alerts.</p></div></div></div></section>
<section class="section wrap" id="packages"><div class="section-head"><p class="kicker">Packages</p><h2>Start small or build a complete operating system.</h2></div><div class="pricing-grid"><?php foreach($c['packages']??[] as $p): ?><article class="price-card <?=!empty($p['featured'])?'featured':''?>"><span class="badge"><?=h($p['name']??'Package')?></span><h3><?=h($p['title']??'Package')?></h3><p class="price"><?=h($p['price']??'Custom')?></p><ul><?php foreach($p['items']??[] as $item): ?><li><?=h($item)?></li><?php endforeach; ?></ul><a href="#contact">Request package →</a></article><?php endforeach; ?></div></section>
<section class="section wrap" id="security"><div class="security-panel"><div><p class="kicker">Security & privacy</p><h2>Professional handling of business data.</h2><p>We do not request passwords or OTPs. Clients provide exports, spreadsheets, PDFs or limited-access data. Outreach campaigns require approval before sending.</p></div><div class="security-list"><p>✓ Data used only for agreed project scope</p><p>✓ Password-protected dashboards available</p><p>✓ Approval before SMS, email or WhatsApp outreach</p><p>✓ Lead data stored privately inside your hosting account</p></div></div></section>
<section class="contact-section" id="contact"><div class="wrap contact-grid"><div><p class="kicker inverse">Book consultation</p><h2>Ready to build a clearer business system?</h2><p>Tell us what you track today and what you want to improve. We will recommend the right dashboard and automation plan.</p><div class="contact-lines"><span>WhatsApp / Calls: <?=h($c['phone_primary']??'')?> · <?=h($c['phone_secondary']??'')?></span><span>Email: <a href="mailto:<?=h($c['email']??'')?>"><?=h($c['email']??'')?></a></span></div></div><div class="contact-card"><?php if(isset($_GET['sent'])): ?><div class="success-msg">Thank you. Your request has been received.</div><?php endif; ?><form method="post" action="submit-lead.php"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input class="hidden" name="website" tabindex="-1" autocomplete="off"><input name="name" placeholder="Your name" required><input name="phone" placeholder="Phone / WhatsApp" required><input type="email" name="email" placeholder="Email address"><input name="business" placeholder="Business / organization name"><select name="service"><option>Dashboard & automation consulting</option><option>Executive dashboard setup</option><option>Data cleanup and reporting</option><option>Customer follow-up system</option><option>AI workflow audit</option></select><select name="budget"><option>Budget range</option><option>₦75k–₦150k</option><option>₦200k–₦500k</option><option>₦600k+</option></select><textarea name="message" placeholder="What do you want to improve or automate?" required></textarea><button class="btn primary wide">Send enquiry</button></form></div></div></section>
</main><footer class="footer wrap"><div><strong><?=h($c['brand']??'Lynxcom Analytics')?></strong><p><?=h($c['tagline']??'AI dashboards and automation')?></p></div><div class="footer-links"><a href="#services">Services</a><a href="#packages">Packages</a><a href="admin/login.php">Admin</a></div></footer><script src="assets/script.js"></script></body></html>