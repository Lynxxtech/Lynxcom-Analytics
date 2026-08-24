<?php
require __DIR__.'/../includes/functions.php';
require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST' || !check_csrf()) die('Invalid request');
$posts=load_posts(true);
$action=$_POST['action']??'save';
$id=trim($_POST['id']??'');
if($action==='delete'){
  $posts=array_values(array_filter($posts,function($p) use ($id){ return ($p['id']??'')!==$id; }));
  save_posts($posts);
  $_SESSION['flash']='Blog post deleted.';
  header('Location: index.php#blog'); exit;
}
$title=safe_text('title',180);
$summary=safe_text('summary',360);
$content=safe_text('content',20000);
$keywords=safe_text('keywords',500);
$status=($_POST['status']??'published')==='draft'?'draft':'published';
if($title==='' || $content===''){
  $_SESSION['flash']='Blog title and content are required.';
  header('Location: index.php#blog'); exit;
}
$slug=slugify($_POST['slug']??$title);
$now=date('Y-m-d');
$found=false;
foreach($posts as &$p){
  if(($p['id']??'')===$id && $id!==''){
    $p['title']=$title; $p['slug']=$slug; $p['summary']=$summary ?: excerpt($content,170); $p['content']=$content; $p['keywords']=$keywords; $p['status']=$status; $p['updated_at']=$now; if(empty($p['created_at'])) $p['created_at']=$now; $found=true; break;
  }
}
unset($p);
if(!$found){
  $posts[]=['id'=>'post_'.date('Ymd_His'),'title'=>$title,'slug'=>$slug,'summary'=>$summary ?: excerpt($content,170),'content'=>$content,'keywords'=>$keywords,'status'=>$status,'created_at'=>$now,'updated_at'=>$now];
}
save_posts($posts);
$_SESSION['flash']='Blog post saved.';
header('Location: index.php#blog');
?>