<?php
require __DIR__.'/../includes/functions.php';
require_admin();
$leads = lead_rows();
$support = support_rows();
$traffic = traffic_rows(1500);
$starterIntakes = starter_intake_rows();
$growthIntakes = growth_intake_rows();
$posts = load_posts(true);
$mailLogPath = STORAGE_DIR.'/mail.log';
$mailLog = file_exists($mailLogPath) ? array_reverse(array_slice(file($mailLogPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -80)) : [];
$content = json_encode(load_content(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
$published_posts = array_filter($posts, fn($p) => ($p['status'] ?? 'published') === 'published');
$draft_posts = array_filter($posts, fn($p) => ($p['status'] ?? 'published') === 'draft');
$blogStats = blog_view_stats($posts);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Lynxcom Analytics Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/styles.css?v=lynxcom-blog-geo-views-20260825">
  <link rel="stylesheet" href="../assets/admin.css?v=lynxcom-blog-geo-views-20260825">
</head>
<body class="wp-admin-body">
<div class="wp-admin-shell">
  <aside class="wp-sidebar" aria-label="Admin navigation">
    <div class="wp-brand">
      <div class="wp-brand-mark">L</div>
      <div><strong>Lynxcom Analytics</strong><span>Admin backend</span></div>
    </div>
    <nav class="wp-menu">
      <a class="active" href="#dashboard"><span class="dashicon">▦</span> Dashboard</a>
      <a href="#leads"><span class="dashicon">☏</span> Leads</a>
      <a href="#support"><span class="dashicon">☑</span> Support Tickets</a>
      <a href="#starter-intakes"><span class="dashicon">▣</span> Starter Intakes</a>
      <a href="#growth-intakes"><span class="dashicon">▤</span> Growth Intakes</a>
      <a href="#mail-log"><span class="dashicon">✉</span> Mail Log</a>
      <a href="#traffic"><span class="dashicon">↗</span> Traffic</a>
      <a href="#blog-analytics"><span class="dashicon">◉</span> Blog Analytics</a>
      <a href="#blog"><span class="dashicon">✎</span> Blog Posts</a>
      <a href="#content"><span class="dashicon">⚙</span> Site Content</a>
      <div class="wp-menu-divider"></div>
      <a href="../index.php" target="_blank" rel="noopener"><span class="dashicon">⌂</span> View Website</a>
      <a href="../blog.php" target="_blank" rel="noopener"><span class="dashicon">◱</span> View Blog</a>
      <a href="logout.php"><span class="dashicon">⇥</span> Logout</a>
    </nav>
    <div class="wp-sidebar-note">WordPress-style command centre for enquiries, support, blog publishing, and website updates.</div>
  </aside>

  <main class="wp-main" id="dashboard">
    <header class="wp-topbar">
      <div class="wp-topbar-title">Lynxcom Admin</div>
      <div class="wp-topbar-actions">
        <a class="wp-button secondary" href="../index.php" target="_blank" rel="noopener">View site</a>
        <a class="wp-button secondary" href="../blog.php" target="_blank" rel="noopener">View blog</a>
        <a class="wp-button secondary" href="logout.php">Logout</a>
      </div>
    </header>

    <div class="wp-content">
      <div class="wp-page-head">
        <div>
          <h1>Dashboard</h1>
          <p>Manage enquiries, support tickets, blog content, traffic insights, and website JSON from one backend.</p>
        </div>
        <a class="wp-button" href="#blog">+ New blog post</a>
      </div>

      <?php if(!empty($_SESSION['flash'])): ?>
        <div class="wp-updated"><?=h($_SESSION['flash']); unset($_SESSION['flash']);?></div>
      <?php endif; ?>

      <section class="wp-stats-grid" aria-label="Admin overview stats">
        <div class="wp-stat-card"><div><span>Total leads</span><strong><?=count($leads)?></strong><em>Consultation enquiries</em></div><div class="wp-stat-icon">☏</div></div>
        <div class="wp-stat-card"><div><span>Support</span><strong><?=count($support)?></strong><em>Customer requests</em></div><div class="wp-stat-icon">☑</div></div>
        <div class="wp-stat-card"><div><span>Starter intakes</span><strong><?=count($starterIntakes)?></strong><em>Detailed audit forms</em></div><div class="wp-stat-icon">▣</div></div><div class="wp-stat-card"><div><span>Growth intakes</span><strong><?=count($growthIntakes)?></strong><em>Dashboard/workflow systems</em></div><div class="wp-stat-icon">▤</div></div><div class="wp-stat-card"><div><span>Visits</span><strong><?=count($traffic)?></strong><em>Tracked sessions</em></div><div class="wp-stat-icon">↗</div></div>
        <div class="wp-stat-card"><div><span>Blog posts</span><strong><?=count($posts)?></strong><em><?=count($published_posts)?> published · <?=count($draft_posts)?> draft</em></div><div class="wp-stat-icon">✎</div></div>
      </section>

      <div class="wp-grid">
        <section class="wp-card wp-section-anchor" id="leads">
          <div class="wp-card-head">
            <div><h2>Leads</h2><p>Recent consultation enquiries and fast reply links.</p></div>
            <div class="wp-card-actions"><span class="wp-count-pill"><?=count($leads)?> total</span><a class="wp-button secondary" href="export.php">Export CSV</a></div>
          </div>
          <div class="wp-card-body">
            <div class="table-wrap"><table><thead><tr><th>Date</th><th>Name</th><th>Phone</th><th>Email</th><th>Reply</th><th>Business</th><th>Service</th><th>Budget</th><th>Message</th></tr></thead><tbody><?php foreach(array_slice($leads,0,50) as $l): ?><tr><td><?=h($l['created_at']??'')?></td><td><?=h($l['name']??'')?></td><td><?=h($l['phone']??'')?></td><td><?=h($l['email']??'')?></td><td><a href="https://wa.me/<?=preg_replace('/\D/','',$l['phone']??'')?>" target="_blank" rel="noopener">WhatsApp</a><?php if(!empty($l['email'])): ?> · <a href="mailto:<?=h($l['email'])?>?subject=Lynxcom Analytics consultation request">Email</a><?php endif; ?></td><td><?=h($l['business']??'')?></td><td><?=h($l['service']??'')?></td><td><?=h($l['budget']??'')?></td><td><?=h($l['message']??'')?></td></tr><?php endforeach; ?></tbody></table></div>
          </div>
        </section>

        <section class="wp-card wp-section-anchor" id="starter-intakes">
          <div class="wp-card-head"><div><h2>Starter package intakes</h2><p>Detailed questionnaires submitted by Starter audit clients.</p></div><span class="wp-count-pill"><?=count($starterIntakes)?> total</span></div>
          <div class="wp-card-body">
            <div class="table-wrap"><table><thead><tr><th>Date</th><th>Name</th><th>Phone</th><th>Email</th><th>Business</th><th>Industry</th><th>Location</th><th>Biggest pain</th><th>Success definition</th><th>Budget</th><th>Timeline</th><th>Reply</th></tr></thead><tbody><?php foreach(array_slice($starterIntakes,0,80) as $i): ?><tr><td><?=h($i['created_at']??'')?></td><td><?=h($i['lead_name']??'')?></td><td><?=h($i['lead_phone']??'')?></td><td><?=h($i['lead_email']??'')?></td><td><?=h($i['business_name']??'')?></td><td><?=h($i['industry']??'')?></td><td><?=h($i['location']??'')?></td><td><?=h($i['biggest_reporting_pain']??'')?></td><td><?=h($i['success_definition']??'')?></td><td><?=h($i['budget_confirmed']??'')?></td><td><?=h($i['timeline']??'')?></td><td><a href="https://wa.me/<?=preg_replace('/\D/','',$i['lead_phone']??'')?>" target="_blank" rel="noopener">WhatsApp</a><?php if(!empty($i['lead_email'])): ?> · <a href="mailto:<?=h($i['lead_email'])?>?subject=Your Lynxcom Starter Audit">Email</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
          </div>
        </section>

        <section class="wp-card wp-section-anchor" id="growth-intakes">
          <div class="wp-card-head"><div><h2>Growth package intakes</h2><p>Detailed questionnaires submitted by Growth Dashboard & Workflow System prospects.</p></div><span class="wp-count-pill"><?=count($growthIntakes)?> total</span></div>
          <div class="wp-card-body">
            <div class="table-wrap"><table><thead><tr><th>Date</th><th>Name</th><th>Phone</th><th>Email</th><th>Business</th><th>Industry</th><th>Location</th><th>KPIs</th><th>Dashboard pages</th><th>Workflow bottlenecks</th><th>Budget</th><th>Timeline</th><th>Reply</th></tr></thead><tbody><?php foreach(array_slice($growthIntakes,0,80) as $i): ?><tr><td><?=h($i['created_at']??'')?></td><td><?=h($i['lead_name']??'')?></td><td><?=h($i['lead_phone']??'')?></td><td><?=h($i['lead_email']??'')?></td><td><?=h($i['business_name']??'')?></td><td><?=h($i['industry']??'')?></td><td><?=h($i['location']??'')?></td><td><?=h($i['kpis_tracked']??'')?></td><td><?=h($i['required_dashboard_pages']??'')?></td><td><?=h($i['workflow_bottlenecks']??'')?></td><td><?=h($i['budget_confirmed']??'')?></td><td><?=h($i['timeline']??'')?></td><td><a href="https://wa.me/<?=preg_replace('/\D/','',$i['lead_phone']??'')?>" target="_blank" rel="noopener">WhatsApp</a><?php if(!empty($i['lead_email'])): ?> · <a href="mailto:<?=h($i['lead_email'])?>?subject=Your Lynxcom Growth Dashboard System">Email</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
          </div>
        </section>

        <section class="wp-card wp-section-anchor" id="mail-log">
          <div class="wp-card-head"><div><h2>Mail delivery log</h2><p>Recent website email attempts. Leads and intakes are saved even if mail delivery fails.</p></div><span class="wp-count-pill"><?=count($mailLog)?> entries</span></div>
          <div class="wp-card-body"><div class="table-wrap"><table><thead><tr><th>Recent mail attempts</th></tr></thead><tbody><?php foreach($mailLog as $line): ?><tr><td><code><?=h($line)?></code></td></tr><?php endforeach; if(!$mailLog): ?><tr><td>No mail attempts logged yet.</td></tr><?php endif; ?></tbody></table></div></div>
        </section>

        <section class="wp-card wp-section-anchor" id="traffic">
          <div class="wp-card-head"><div><h2>Traffic tracker</h2><p>Quick website traffic and campaign visibility.</p></div><span class="wp-count-pill"><?=count($traffic)?> visits</span></div>
          <div class="wp-card-body">
            <div class="wp-traffic-grid">
              <div class="wp-mini-card"><h3>Top pages</h3><ul><?php foreach(traffic_summary($traffic,'page',8) as $k=>$v): ?><li><span><?=h($k)?></span><strong><?=h($v)?></strong></li><?php endforeach; ?></ul></div>
              <div class="wp-mini-card"><h3>Referral sites</h3><ul><?php foreach(traffic_summary($traffic,'referrer_host',8) as $k=>$v): ?><li><span><?=h($k)?></span><strong><?=h($v)?></strong></li><?php endforeach; ?></ul></div>
              <div class="wp-mini-card"><h3>Visitor locations</h3><ul><?php foreach(traffic_summary($traffic,'location_hint',8) as $k=>$v): ?><li><span><?=h($k)?></span><strong><?=h($v)?></strong></li><?php endforeach; ?></ul></div>
              <div class="wp-mini-card"><h3>UTM sources</h3><ul><?php foreach(traffic_summary($traffic,'utm_source',8) as $k=>$v): ?><li><span><?=h($k)?></span><strong><?=h($v)?></strong></li><?php endforeach; ?></ul></div>
            </div>
            <div class="table-wrap" style="margin-top:16px"><table><thead><tr><th>Date</th><th>Page</th><th>Post</th><th>Location</th><th>Country</th><th>Region</th><th>City</th><th>Referrer</th><th>UTM Source</th><th>Campaign</th><th>User agent</th></tr></thead><tbody><?php foreach(array_slice($traffic,0,120) as $v): ?><tr><td><?=h($v['created_at']??'')?></td><td><?=h($v['page']??'')?></td><td><?=h($v['post_slug']??'')?></td><td><?=h($v['location_hint']??'')?></td><td><?=h($v['country']??'')?></td><td><?=h($v['region']??'')?></td><td><?=h($v['city']??'')?></td><td><?=h($v['referrer_host']??($v['referrer']??''))?></td><td><?=h($v['utm_source']??'')?></td><td><?=h($v['utm_campaign']??'')?></td><td><?=h(substr($v['user_agent']??'',0,120))?></td></tr><?php endforeach; ?></tbody></table></div>
            <p><small>Location is estimated from the visitor IP using a cached geo lookup. Accuracy depends on the visitor network/VPN/mobile carrier.</small></p>
          </div>
        </section>

        <section class="wp-card wp-section-anchor" id="support">
          <div class="wp-card-head"><div><h2>Support tickets</h2><p>Incoming support requests with quick WhatsApp/email reply links.</p></div><span class="wp-count-pill"><?=count($support)?> total</span></div>
          <div class="wp-card-body">
            <div class="table-wrap"><table><thead><tr><th>Date</th><th>Ticket</th><th>Name</th><th>Phone</th><th>Email</th><th>Category</th><th>Urgency</th><th>Subject</th><th>Message</th><th>Reply</th></tr></thead><tbody><?php foreach(array_slice($support,0,80) as $t): ?><tr><td><?=h($t['created_at']??'')?></td><td><?=h($t['ticket_id']??'')?></td><td><?=h($t['name']??'')?></td><td><?=h($t['phone']??'')?></td><td><?=h($t['email']??'')?></td><td><?=h($t['category']??'')?></td><td><?=h($t['urgency']??'')?></td><td><?=h($t['subject']??'')?></td><td><?=h($t['message']??'')?></td><td><a href="https://wa.me/<?=preg_replace('/\D/','',$t['phone']??'')?>" target="_blank" rel="noopener">WhatsApp</a><?php if(!empty($t['email'])): ?> · <a href="mailto:<?=h($t['email'])?>?subject=Re: <?=h($t['subject']??'Lynxcom support')?>">Email</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
          </div>
        </section>

        <section class="wp-card wp-section-anchor" id="blog-analytics">
          <div class="wp-card-head"><div><h2>Blog post analytics</h2><p>Unique post views and where readers are coming from.</p></div><span class="wp-count-pill"><?=count($blogStats)?> tracked posts</span></div>
          <div class="wp-card-body">
            <div class="table-wrap"><table><thead><tr><th>Post</th><th>Total views</th><th>Unique views</th><th>Top locations</th><th>Countries</th><th>Last unique view</th></tr></thead><tbody><?php foreach($posts as $p): $slug=$p['slug']??''; $st=$blogStats[$slug]??['total'=>0,'unique'=>0,'locations'=>[],'countries'=>[],'last_view'=>'']; ?><tr><td><strong><?=h($p['title']??'Untitled')?></strong><br><small><?=h($slug)?></small></td><td><?=h($st['total']??0)?></td><td><?=h($st['unique']??0)?></td><td><?php $locs=array_slice($st['locations']??[],0,5,true); if(!$locs) echo '<span class="wp-muted">No views yet</span>'; foreach($locs as $k=>$v): ?><span class="wp-location-chip"><?=h($k)?> · <?=h($v)?></span><?php endforeach; ?></td><td><?php $countries=array_slice($st['countries']??[],0,5,true); if(!$countries) echo '<span class="wp-muted">—</span>'; foreach($countries as $k=>$v): ?><span class="wp-location-chip"><?=h($k)?> · <?=h($v)?></span><?php endforeach; ?></td><td><?=h($st['last_view']??'')?></td></tr><?php endforeach; ?></tbody></table></div>
            <p><small>Unique views are counted once per blog post per visitor fingerprint. Location is based on IP lookup and may be approximate.</small></p>
          </div>
        </section>

        <section class="wp-card wp-section-anchor" id="blog">
          <div class="wp-card-head"><div><h2>Blog posts</h2><p>Create helpful articles that answer real business questions and attract free Google search traffic.</p></div><span class="wp-count-pill"><?=count($published_posts)?> published</span></div>
          <div class="wp-card-body">
            <form class="blog-admin-form rich-post-form" method="post" action="save-blog.php">
              <input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="id" value="">
              <div class="form-row"><input name="title" placeholder="Post title" required><input name="slug" placeholder="URL slug — leave blank to auto-generate"></div>
              <div class="form-row"><input name="cover_image" placeholder="Cover image path or URL e.g. assets/section-services.jpg"><input name="cover_alt" placeholder="Cover image description"></div>
              <textarea name="summary" placeholder="Short summary for Google and blog cards"></textarea>
              <div class="wysiwyg-editor" data-placeholder="Write the full blog post here. Format text visually and insert links/images."></div>
              <textarea class="hidden-html" name="content_html"></textarea><textarea class="plain-content" name="content" placeholder="Plain text fallback — optional"></textarea>
              <div class="form-row"><input name="keywords" placeholder="Internal keywords/topics — optional"><select name="status"><option value="published">Published</option><option value="draft">Draft</option></select></div>
              <button class="wp-button">Publish blog post</button>
            </form>
            <div class="admin-post-list"><h3>Existing posts</h3><?php foreach($posts as $p): ?><article><div><strong><?=h($p['title']??'Untitled')?></strong><span><?=h($p['status']??'published')?> · <?=h($p['created_at']??'')?></span><a class="wp-button secondary" href="../post.php?slug=<?=urlencode($p['slug']??'')?>" target="_blank" rel="noopener">View</a></div><details><summary>Edit this post</summary><form method="post" action="save-blog.php" class="rich-post-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="id" value="<?=h($p['id']??'')?>"><input name="title" value="<?=h($p['title']??'')?>" required><input name="slug" value="<?=h($p['slug']??'')?>"><div class="form-row"><input name="cover_image" value="<?=h($p['cover_image']??'')?>" placeholder="Cover image path or URL"><input name="cover_alt" value="<?=h($p['cover_alt']??'')?>" placeholder="Cover image description"></div><textarea name="summary"><?=h($p['summary']??'')?></textarea><div class="wysiwyg-editor"><?=clean_html($p['content_html']??'')?></div><textarea class="hidden-html" name="content_html"></textarea><textarea class="plain-content" name="content"><?=h($p['content']??'')?></textarea><div class="form-row"><input name="keywords" value="<?=h($p['keywords']??'')?>"><select name="status"><option value="published" <?=($p['status']??'published')==='published'?'selected':''?>>Published</option><option value="draft" <?=($p['status']??'')==='draft'?'selected':''?>>Draft</option></select></div><button class="wp-button">Save changes</button><button class="wp-button danger" name="action" value="delete" onclick="return confirm('Delete this blog post?')">Delete</button></form></details></article><?php endforeach; ?></div>
          </div>
        </section>

        <section class="wp-card wp-section-anchor" id="content">
          <div class="wp-card-head"><div><h2>Edit website content</h2><p>Edit carefully. This is JSON. Invalid JSON will not save.</p></div><span class="wp-count-pill wp-danger-pill">Advanced</span></div>
          <div class="wp-card-body">
            <form method="post" action="save-content.php"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><textarea class="json-editor" name="content_json"><?=h($content)?></textarea><button class="wp-button" style="margin-top:12px">Save content</button></form>
          </div>
        </section>
      </div>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script src="../assets/admin-blog-editor.js?v=lynxcom-blog-geo-views-20260825"></script>
</body>
</html>
