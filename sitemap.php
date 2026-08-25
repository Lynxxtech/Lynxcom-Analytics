<?php
require __DIR__.'/includes/functions.php';
header('Content-Type: application/xml; charset=UTF-8');
$base='https://lynxcomanalytics.com';
$urls=[
  ['/', 'weekly', '1.0', '2026-08-25'],
  ['/blog.php', 'weekly', '0.9', '2026-08-25'],
  ['/support.php', 'weekly', '0.8', '2026-08-25'],
  ['/ai-automation-agency-nigeria.php', 'weekly', '0.8', '2026-08-25'],
  ['/business-dashboard-consulting-nigeria.php', 'weekly', '0.8', '2026-08-25'],
  ['/data-analytics-consulting-nigeria.php', 'weekly', '0.8', '2026-08-25'],
  ['/customer-follow-up-automation-nigeria.php', 'weekly', '0.8', '2026-08-25'],
];
foreach(load_posts(false) as $post){
  if(!empty($post['slug'])){
    $urls[]=['/post.php?slug='.rawurlencode($post['slug']), 'monthly', '0.7', $post['updated_at'] ?? ($post['created_at'] ?? date('Y-m-d'))];
  }
}
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
$seen=[];
foreach($urls as $u){
  [$path,$freq,$priority,$lastmod]=$u;
  $loc=$base.$path;
  if(isset($seen[$loc])) continue;
  $seen[$loc]=true;
  echo "  <url>\n";
  echo "    <loc>".htmlspecialchars($loc, ENT_XML1, 'UTF-8')."</loc>\n";
  echo "    <lastmod>".htmlspecialchars(substr($lastmod,0,10), ENT_XML1, 'UTF-8')."</lastmod>\n";
  echo "    <changefreq>".htmlspecialchars($freq, ENT_XML1, 'UTF-8')."</changefreq>\n";
  echo "    <priority>".htmlspecialchars($priority, ENT_XML1, 'UTF-8')."</priority>\n";
  echo "  </url>\n";
}
echo "</urlset>\n";
?>
