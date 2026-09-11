<?php
$products=[
 ['category'=>'Supplements','items'=>[
  ['name'=>'Whey Protein','price'=>'From ₦38,000','desc'=>'Protein support for recovery, lean muscle and daily nutrition.'],
  ['name'=>'Pre-Workout','price'=>'From ₦22,000','desc'=>'Energy and focus support before intense training sessions.'],
  ['name'=>'Creatine Monohydrate','price'=>'From ₦18,000','desc'=>'Strength and performance support for consistent gym members.'],
  ['name'=>'Shaker Bottle','price'=>'From ₦4,500','desc'=>'Easy mixing bottle for protein shakes and workout drinks.'],
 ]],
 ['category'=>'Training Accessories','items'=>[
  ['name'=>'Gym Gloves','price'=>'From ₦8,500','desc'=>'Grip protection for weights, pull exercises and general training.'],
  ['name'=>'Resistance Bands','price'=>'From ₦6,000','desc'=>'Portable support for warmups, mobility and strength routines.'],
  ['name'=>'Skipping Rope','price'=>'From ₦5,500','desc'=>'Cardio tool for endurance, speed and conditioning.'],
  ['name'=>'Lifting Belt','price'=>'From ₦20,000','desc'=>'Waist support for heavy lifting and strength training.'],
 ]],
 ['category'=>'Gym Wear','items'=>[
  ['name'=>'Training T-Shirt','price'=>'From ₦12,000','desc'=>'Comfortable breathable gym top for daily workouts.'],
  ['name'=>'Joggers / Training Pants','price'=>'From ₦18,000','desc'=>'Flexible training wear for mobility, warmups and gym sessions.'],
  ['name'=>'Sports Towel','price'=>'From ₦4,000','desc'=>'Workout towel for gym floor and class sessions.'],
  ['name'=>'Gym Bag','price'=>'From ₦25,000','desc'=>'Carry training gear, shoes, towel and supplements.'],
 ]],
 ['category'=>'Services','items'=>[
  ['name'=>'Membership Registration','price'=>'Ask at front desk','desc'=>'Get started with the plan that matches your training goal.'],
  ['name'=>'Personal Training Assessment','price'=>'Book enquiry','desc'=>'Meet a coach for body goal review and training direction.'],
  ['name'=>'Online Coaching Enquiry','price'=>'Book enquiry','desc'=>'Remote fitness guidance for members who train anywhere.'],
  ['name'=>'Boxing Fitness Session','price'=>'Book enquiry','desc'=>'Pad work, conditioning and boxing-inspired fitness coaching.'],
 ]],
];
function h($v){return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Smart Gym Shop | Products & Fitness Items</title><meta name="description" content="Browse Smart Gym shop items including supplements, gym accessories, training wear and service enquiries."><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/styles.css?v=smartgym-menu-type-20260911"></head><body><header class="sg-header"><a class="sg-logo" href="index.php#top"><span>SG</span><b>SMART GYM</b></a><nav><a href="index.php#top">Home</a><a href="index.php#memberships">Membership Plans</a><a href="index.php#programs">Programs</a><a href="index.php#schedule">Schedule</a><a href="shop.php">Shop</a><a href="index.php#coaches">Trainers</a><a href="index.php#join">Join</a></nav><a class="header-btn" href="index.php#join">Start Now</a></header><main><section class="shop-hero"><div><span class="label">Smart Gym Shop</span><h1>Training essentials, products and service enquiries.</h1><p>Browse available product categories and send an enquiry for membership, supplements, accessories, gym wear or training services.</p><div class="hero-actions"><a class="btn fire" href="#products">View Items</a><a class="btn ghost" href="index.php#join">Make Enquiry</a></div></div><figure><img src="assets/img/shop-naija-v2-20260911.webp" alt="Smart Gym shop and fitness products" loading="eager" decoding="async"></figure></section><section class="shop-page-products" id="products"><?php foreach($products as $group): ?><div class="shop-category"><div class="section-title"><span><?=h($group['category'])?></span><h2><?=h($group['category'])?></h2></div><div class="shop-item-grid"><?php foreach($group['items'] as $item): ?><article><h3><?=h($item['name'])?></h3><b><?=h($item['price'])?></b><p><?=h($item['desc'])?></p><a href="index.php#join">Enquire</a></article><?php endforeach; ?></div></div><?php endforeach; ?></section></main><footer class="sg-footer"><b>SMART GYM</b><span>Fitness website + data collection demo by LynxCom Analytics</span></footer><script src="assets/script.js?v=smartgym-menu-type-20260911"></script></body></html>
