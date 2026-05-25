<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fallback Unsplash photos (same as index.php / listing.php)
$listing_photos = [
    'Heavy-duty Power Drill'    => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80',
    'Angle Grinder'             => 'https://images.unsplash.com/photo-1531668361947-d00e652ac030?w=600&q=80',
    'Cement Mixer (Mini)'       => 'https://images.unsplash.com/photo-1531145910467-8d7338926919?w=600&q=80',
    'Pressure Washer'           => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80',
    'Scaffolding Set'           => 'https://images.unsplash.com/photo-1760597307051-67946f9cf865?w=600&q=80',
    'Plastic Chairs (30 pack)'  => 'https://images.unsplash.com/photo-1582650448861-bd3339f97601?w=600&q=80',
    'Party Tent (6x6m)'         => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600&q=80',
    'Folding Tables (10 pack)'  => 'https://images.unsplash.com/photo-1763429338698-439aa108e7fb?w=600&q=80',
    'Chafing Dishes Set (8)'    => 'https://images.unsplash.com/photo-1555244162-803834f70033?w=600&q=80',
    'Inflatable Jumping Castle' => 'https://images.unsplash.com/photo-1706743559585-ce8d51210528?w=600&q=80',
    'Sound System + 2 Speakers' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&q=80',
    'DJ Controller & Mixer'     => 'https://images.unsplash.com/photo-1618107095181-e3ba0f53ee59?w=600&q=80',
    'Projector & Screen'        => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=600&q=80',
    'Generator (3.5kVA)'        => 'https://images.unsplash.com/photo-1509390144018-eeaf65052242?w=600&q=80',
    'LED Party Lights Set'      => 'https://images.unsplash.com/photo-1560801122-b59974a71aca?w=600&q=80',
    'Petrol Lawn Mower'         => 'https://images.unsplash.com/photo-1689728222087-6984f72460c4?w=600&q=80',
    'Electric Hedge Trimmer'    => 'https://images.unsplash.com/photo-1521633603986-cb82a097ffba?w=600&q=80',
    'Garden Chipper/Shredder'   => 'https://images.unsplash.com/flagged/photo-1574359364027-b62a716266c1?w=600&q=80',
    'Wheelbarrow + Garden Tools'=> 'https://images.unsplash.com/photo-1687512966596-1aacfeaf6e54?w=600&q=80',
    'Water Pump (Submersible)'  => 'https://images.unsplash.com/photo-1622768515656-d51e5dcb68c2?w=600&q=80',
];
$category_photos = [
    'Tools & Equipment'   => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80',
    'Furniture & Chairs'  => 'https://images.unsplash.com/photo-1582650448861-bd3339f97601?w=600&q=80',
    'Electronics & Sound' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&q=80',
    'Gardening'           => 'https://images.unsplash.com/photo-1687512966596-1aacfeaf6e54?w=600&q=80',
];

// Fetch user details
$user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user->execute([$user_id]);
$user = $user->fetch();

// My listings
$my_listings = $pdo->prepare("
    SELECT l.*, c.category_name, c.icon,
           COUNT(r.rental_id) as total_rentals,
           COALESCE(SUM(r.total_price), 0) as total_earned
    FROM listings l
    JOIN categories c ON l.category_id = c.category_id
    LEFT JOIN rentals r ON l.listing_id = r.listing_id AND r.status = 'completed'
    WHERE l.user_id = ?
    GROUP BY l.listing_id
    ORDER BY l.created_at DESC
");
$my_listings->execute([$user_id]);
$my_listings = $my_listings->fetchAll();

// My rentals (as renter)
$my_rentals = $pdo->prepare("
    SELECT r.*, l.title, l.price_per_day, l.image_path, l.listing_id,
           c.icon, u.full_name as owner_name, u.phone as owner_phone
    FROM rentals r
    JOIN listings l ON r.listing_id = l.listing_id
    JOIN categories c ON l.category_id = c.category_id
    LEFT JOIN users u ON l.user_id = u.user_id
    WHERE r.renter_id = ?
    ORDER BY r.booked_at DESC
");
$my_rentals->execute([$user_id]);
$my_rentals = $my_rentals->fetchAll();

// Wishlist
$wishlist = $pdo->prepare("
    SELECT l.*, c.category_name, c.icon, u.full_name, u.location
    FROM wishlists w
    JOIN listings l ON w.listing_id = l.listing_id
    JOIN categories c ON l.category_id = c.category_id
    LEFT JOIN users u ON l.user_id = u.user_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$wishlist->execute([$user_id]);
$wishlist = $wishlist->fetchAll();

// Unread messages count
$unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unread->execute([$user_id]);
$unread_count = $unread->fetchColumn();

// Stats
$total_earned = array_sum(array_column($my_listings, 'total_earned'));
$active_rentals = count(array_filter($my_rentals, fn($r) => $r['status'] === 'active'));
$pending_rentals = count(array_filter($my_rentals, fn($r) => $r['status'] === 'pending'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Account - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#FFF9F4;--bg2:#FFF2E6;--card:#fff;--text:#1A1208;--muted:#6B5C4A;--accent:#D4530A;--green:#2D7A4F;--border:#E8D5C0;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);color:var(--text);font-size:14px}
nav{background:var(--card);border-bottom:1px solid var(--border);padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:58px;position:sticky;top:0;z-index:100}
.logo{font-family:var(--fh);font-weight:800;font-size:22px;color:var(--accent);text-decoration:none}
.logo span{color:#E8870A}
.nav-links{display:flex;gap:8px;align-items:center}
.nav-links a{font-size:13px;color:var(--muted);text-decoration:none;padding:6px 10px;border-radius:6px}
.nav-links a:hover{background:var(--bg2);color:var(--text)}
.btn-nav{background:var(--accent);color:#fff!important;padding:7px 16px!important;border-radius:8px;font-size:13px;font-weight:500}
.container{max-width:1000px;margin:0 auto;padding:32px 24px}

/* PROFILE HEADER */
.profile-header{background:linear-gradient(135deg,#2D1A08,#4A2C10);border-radius:16px;padding:28px;display:flex;align-items:center;gap:20px;margin-bottom:24px;flex-wrap:wrap}
.avatar{width:72px;height:72px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:26px;font-weight:800;color:#fff;flex-shrink:0}
.profile-info h2{font-family:var(--fh);font-size:22px;font-weight:800;color:#fff;margin-bottom:4px}
.profile-info p{font-size:13px;color:rgba(255,255,255,0.6);margin-bottom:8px}
.badges{display:flex;gap:8px;flex-wrap:wrap}
.badge{font-size:11px;font-weight:500;padding:3px 12px;border-radius:20px}
.badge.verified{background:rgba(45,122,79,0.3);color:#7FFAAA;border:1px solid rgba(45,122,79,0.4)}
.badge.unverified{background:rgba(212,83,10,0.3);color:#F4A261;border:1px solid rgba(212,83,10,0.4);cursor:pointer;text-decoration:none}
.profile-actions{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.btn-white{background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;text-decoration:none;display:inline-block;font-family:var(--ff)}
.btn-white:hover{background:rgba(255,255,255,0.2)}
.btn-orange{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;text-decoration:none;display:inline-block;font-family:var(--ff)}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:center}
.stat-val{font-family:var(--fh);font-size:26px;font-weight:800;color:var(--accent);margin-bottom:4px}
.stat-val.green{color:var(--green)}
.stat-label{font-size:12px;color:var(--muted)}

/* TABS */
.tab-bar{display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:20px;overflow-x:auto}
.tab{padding:10px 18px;font-size:13px;cursor:pointer;color:var(--muted);border-bottom:2px solid transparent;text-decoration:none;white-space:nowrap;display:flex;align-items:center;gap:6px;transition:.15s;background:none;border-top:none;border-left:none;border-right:none;font-family:var(--ff);outline:none}
.tab:hover{color:var(--text)}
.tab.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:500}
.tab-badge{background:var(--accent);color:#fff;font-size:10px;padding:1px 7px;border-radius:10px}
.tab-content{display:none}
.tab-content.active{display:block}

/* LISTING CARDS */
.my-listings{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
.my-listing-card{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.my-listing-img{height:130px;background:#FFF5ED;display:flex;align-items:center;justify-content:center;font-size:40px;position:relative;overflow:hidden}
.my-listing-img img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.my-listing-img .emoji{position:relative;z-index:1}
.avail-badge{position:absolute;top:8px;right:8px;font-size:10px;font-weight:500;padding:2px 8px;border-radius:10px;z-index:2}
.avail-badge.available{background:#E8F5EE;color:var(--green)}
.avail-badge.unavailable{background:#F5E8E8;color:#C0392B}
.avail-badge.pending{background:#FFF3CD;color:#856404}
.my-listing-body{padding:12px}
.my-listing-title{font-weight:500;font-size:13px;margin-bottom:6px}
.my-listing-stats{display:flex;justify-content:space-between;font-size:12px;color:var(--muted);margin-bottom:10px}
.my-listing-price{font-family:var(--fh);font-size:15px;font-weight:700;color:var(--accent)}
.my-listing-actions{display:flex;gap:6px;margin-top:10px}
.btn-xs{font-size:11px;padding:4px 10px;border-radius:6px;cursor:pointer;border:1px solid var(--border);background:transparent;color:var(--muted);text-decoration:none;display:inline-block;font-family:var(--ff)}
.btn-xs:hover{border-color:var(--accent);color:var(--accent)}
.btn-xs.danger:hover{border-color:#C0392B;color:#C0392B}

/* RENTAL ROWS */
.rental-list{display:flex;flex-direction:column;gap:12px}
.rental-row{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;display:flex;gap:14px;align-items:center}
.rental-row-img{width:60px;height:54px;border-radius:8px;background:#FFF5ED;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;overflow:hidden}
.rental-row-img img{width:100%;height:100%;object-fit:cover}
.rental-row-info{flex:1}
.rental-row-title{font-weight:500;font-size:14px;margin-bottom:4px}
.rental-row-meta{font-size:12px;color:var(--muted);display:flex;gap:12px;flex-wrap:wrap}
.status{font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px;display:inline-block}
.status.pending{background:#FFF5E0;color:#B8860B}
.status.active{background:#E8F5EE;color:var(--green)}
.status.completed{background:#E8EEF5;color:#1A6BA0}
.status.cancelled{background:#F5F5F5;color:#888}
.rental-row-price{font-family:var(--fh);font-size:16px;font-weight:700;color:var(--accent);flex-shrink:0}

/* WISHLIST */
.wishlist-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.wishlist-card{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;text-decoration:none;color:var(--text);display:block;position:relative}
.wishlist-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.wishlist-img{height:120px;background:#FFF5ED;display:flex;align-items:center;justify-content:center;font-size:36px;overflow:hidden;position:relative}
.wishlist-img img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.wishlist-img .emoji{position:relative;z-index:1}
.wishlist-remove{position:absolute;top:8px;right:8px;background:rgba(255,255,255,0.9);border:none;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;color:#C0392B;z-index:3;text-decoration:none}
.wishlist-body{padding:12px}
.wishlist-title{font-size:13px;font-weight:500;margin-bottom:4px}
.wishlist-loc{font-size:11px;color:var(--muted);margin-bottom:6px}
.wishlist-price{font-family:var(--fh);font-size:15px;font-weight:700;color:var(--accent)}

/* VERIFY */
.verify-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;max-width:500px}
.verify-card h3{font-family:var(--fh);font-size:18px;font-weight:700;margin-bottom:8px}
.verify-card p{font-size:13px;color:var(--muted);margin-bottom:20px;line-height:1.6}
.verify-steps{display:flex;flex-direction:column;gap:12px;margin-bottom:20px}
.verify-step{display:flex;gap:12px;align-items:center;padding:12px;background:var(--bg);border-radius:8px}
.verify-step-num{width:28px;height:28px;background:var(--accent);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0}
.verify-step p{font-size:13px;color:var(--text);margin:0}
label{font-size:13px;font-weight:500;display:block;margin-bottom:4px;margin-top:14px}
input,select{width:100%;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:var(--ff);font-size:14px;color:var(--text);background:var(--bg);outline:none}
input:focus{border-color:var(--accent)}
.upload-area{border:2px dashed var(--border);border-radius:10px;padding:20px;text-align:center;cursor:pointer;position:relative;margin-top:6px}
.upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.upload-area:hover{border-color:var(--accent)}
.btn-submit{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-size:14px;font-weight:500;cursor:pointer;margin-top:16px;font-family:var(--ff);width:100%}
.btn-submit:hover{background:#b8440a}
.success-box{background:#E8F5EE;border:1px solid #2D7A4F;color:#1A4A2E;padding:16px;border-radius:10px;font-size:13px;display:flex;gap:12px;align-items:center}

/* EMPTY STATE */
.empty{text-align:center;padding:40px;color:var(--muted)}
.empty-icon{font-size:40px;margin-bottom:12px}
.empty h3{font-size:15px;font-weight:500;margin-bottom:6px;color:var(--text)}
.empty p{font-size:13px}
.empty a{color:var(--accent);text-decoration:none;font-weight:500}

footer{background:#1A1208;color:rgba(255,255,255,0.5);text-align:center;padding:20px;font-size:13px;margin-top:40px}
footer span{color:#F4A261}


@media(max-width:600px){
  .stats{grid-template-columns:1fr 1fr}
  .profile-header{flex-direction:column;text-align:center}
  .profile-actions{margin-left:0;justify-content:center}
}
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <div class="nav-links">
    <a href="index.php">Browse</a>
    <a href="list_item.php">+ List Item</a>
    <a href="messages.php" style="position:relative">
      Messages
      <?php if($unread_count > 0): ?><span style="background:var(--accent);color:#fff;font-size:10px;padding:1px 6px;border-radius:10px;position:absolute;top:-4px;right:-4px"><?= $unread_count ?></span><?php endif; ?>
    </a>
    <a href="logout.php" class="btn-nav">Logout</a>
  </div>
</nav>

<div class="container">

  <!-- PROFILE HEADER -->
  <div class="profile-header">
    <div class="avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
    <div class="profile-info">
      <h2><?= htmlspecialchars($user['full_name']) ?></h2>
      <p><?= htmlspecialchars($user['location']) ?> &nbsp;&bull;&nbsp; <?= htmlspecialchars($user['phone'] ?: 'No phone added') ?></p>
      <div class="badges">
        <?php if($user['is_verified']): ?>
          <span class="badge verified">&#10003; ID Verified</span>
        <?php else: ?>
          <button class="badge unverified" data-tab="verify" style="font-family:var(--ff);cursor:pointer">&#9888; Not Verified — Click to verify</button>
        <?php endif; ?>
        <span class="badge" style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);border:1px solid rgba(255,255,255,0.15)"><?= ucfirst($user['role']) ?></span>
      </div>
    </div>
    <div class="profile-actions">
      <a href="list_item.php" class="btn-orange">+ List New Item</a>
      <a href="messages.php" class="btn-white">Messages <?php if($unread_count > 0): ?>(<?= $unread_count ?>)<?php endif; ?></a>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-val"><?= count($my_listings) ?></div>
      <div class="stat-label">My Listings</div>
    </div>
    <div class="stat-card">
      <div class="stat-val green">R<?= number_format($total_earned, 0) ?></div>
      <div class="stat-label">Total Earned</div>
    </div>
    <div class="stat-card">
      <div class="stat-val"><?= count($my_rentals) ?></div>
      <div class="stat-label">Rentals Made</div>
    </div>
    <div class="stat-card">
      <div class="stat-val" style="color:#1A6BA0"><?= count($wishlist) ?></div>
      <div class="stat-label">Saved Items</div>
    </div>
  </div>

  <!-- TABS -->
  <div class="tab-bar">
    <button class="tab active" data-tab="listings">My Listings</button>
    <button class="tab" data-tab="rentals">
      My Rentals
      <?php if($pending_rentals > 0): ?><span class="tab-badge"><?= $pending_rentals ?></span><?php endif; ?>
    </button>
    <button class="tab" data-tab="wishlist">Saved Items</button>
    <button class="tab" data-tab="verify">
      ID Verification
      <?php if(!$user['is_verified']): ?><span class="tab-badge" style="background:#E8870A">!</span><?php endif; ?>
    </button>
  </div>

  <!-- MY LISTINGS -->
  <div id="tab-listings" class="tab-content active">
    <?php if(count($my_listings) > 0): ?>
      <div class="my-listings">
        <?php foreach($my_listings as $l): ?>
        <div class="my-listing-card">
          <div class="my-listing-img">
            <?php
              $l_img = (!empty($l['image_path']) && file_exists(__DIR__ . '/' . $l['image_path']))
                       ? $l['image_path']
                       : ($listing_photos[$l['title']] ?? $category_photos[$l['category_name']] ?? null);
            ?>
            <?php if($l_img): ?>
              <img src="<?= htmlspecialchars($l_img) ?>" alt="">
            <?php else: ?>
              <span class="emoji"><?= $l['icon'] ?></span>
            <?php endif; ?>
            <span class="avail-badge <?= $l['availability_status'] ?>"><?= ucfirst($l['availability_status']) ?></span>
          </div>
          <div class="my-listing-body">
            <div class="my-listing-title"><?= htmlspecialchars($l['title']) ?></div>
            <div class="my-listing-stats">
              <span><?= $l['total_rentals'] ?> rentals</span>
              <span style="color:var(--green)">R<?= number_format($l['total_earned'], 0) ?> earned</span>
            </div>
            <div class="my-listing-price">R<?= number_format($l['price_per_day'], 2) ?>/day</div>
            <div class="my-listing-actions">
              <a href="listing.php?id=<?= $l['listing_id'] ?>" class="btn-xs">View</a>
              <a href="edit_listing.php?id=<?= $l['listing_id'] ?>" class="btn-xs">Edit</a>
              <a href="delete_listing.php?id=<?= $l['listing_id'] ?>" class="btn-xs danger" onclick="return confirm('Delete this listing?')">Delete</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">
        <div class="empty-icon">&#127981;</div>
        <h3>No listings yet</h3>
        <p>Start earning by <a href="list_item.php">listing your first item</a></p>
      </div>
    <?php endif; ?>
  </div>

  <!-- MY RENTALS -->
  <div id="tab-rentals" class="tab-content">
    <?php if(count($my_rentals) > 0): ?>
      <div class="rental-list">
        <?php foreach($my_rentals as $r): ?>
        <div class="rental-row">
          <div class="rental-row-img">
            <?php
              $r_img = (!empty($r['image_path']) && file_exists(__DIR__ . '/' . $r['image_path']))
                       ? $r['image_path']
                       : ($listing_photos[$r['title']] ?? null);
            ?>
            <?php if($r_img): ?>
              <img src="<?= htmlspecialchars($r_img) ?>" alt="">
            <?php else: ?>
              <?= $r['icon'] ?>
            <?php endif; ?>
          </div>
          <div class="rental-row-info">
            <div class="rental-row-title"><?= htmlspecialchars($r['title']) ?></div>
            <div class="rental-row-meta">
              <span><?= $r['start_date'] ?></span>
              <span><?= $r['num_days'] ?> day<?= $r['num_days']>1?'s':'' ?></span>
              <span><?= htmlspecialchars($r['owner_name']) ?></span>
              <?php if($r['owner_phone']): ?><span><?= htmlspecialchars($r['owner_phone']) ?></span><?php endif; ?>
            </div>
            <span class="status <?= $r['status'] ?>" style="margin-top:6px"><?= ucfirst($r['status']) ?></span>
          </div>
          <div class="rental-row-price">R<?= number_format($r['total_price'], 2) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">
        <div class="empty-icon">&#128203;</div>
        <h3>No rentals yet</h3>
        <p><a href="index.php">Browse items</a> to make your first booking</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- WISHLIST -->
  <div id="tab-wishlist" class="tab-content">
    <?php if(count($wishlist) > 0): ?>
      <div class="wishlist-grid">
        <?php foreach($wishlist as $w): ?>
        <div style="position:relative">
          <a href="listing.php?id=<?= $w['listing_id'] ?>" class="wishlist-card">
            <div class="wishlist-img">
              <?php
                $w_img = (!empty($w['image_path']) && file_exists(__DIR__ . '/' . $w['image_path']))
                         ? $w['image_path']
                         : ($listing_photos[$w['title']] ?? $category_photos[$w['category_name']] ?? null);
              ?>
              <?php if($w_img): ?>
                <img src="<?= htmlspecialchars($w_img) ?>" alt="">
              <?php else: ?>
                <span class="emoji"><?= $w['icon'] ?></span>
              <?php endif; ?>
            </div>
            <div class="wishlist-body">
              <div class="wishlist-title"><?= htmlspecialchars($w['title']) ?></div>
              <div class="wishlist-loc"><?= htmlspecialchars($w['location']) ?></div>
              <div class="wishlist-price">R<?= number_format($w['price_per_day'], 2) ?>/day</div>
            </div>
          </a>
          <a href="wishlist_toggle.php?id=<?= $w['listing_id'] ?>&redirect=dashboard" class="wishlist-remove" title="Remove from wishlist">&#10005;</a>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">
        <div class="empty-icon">&#10084;</div>
        <h3>No saved items</h3>
        <p>Click the heart icon on any listing to save it here</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- ID VERIFICATION -->
  <div id="tab-verify" class="tab-content">
    <?php if($user['is_verified']): ?>
      <div class="success-box">
        <span style="font-size:28px">&#9989;</span>
        <div>
          <div style="font-weight:500;margin-bottom:4px">Your ID has been verified!</div>
          <div style="font-size:12px">Your listings now show a verified badge, building trust with renters.</div>
        </div>
      </div>
    <?php else: ?>
      <div class="verify-card">
        <h3>Get ID Verified</h3>
        <p>Verified users get a badge on their profile and listings, making renters more likely to trust and book their items. It only takes a minute.</p>
        <div class="verify-steps">
          <div class="verify-step"><div class="verify-step-num">1</div><p>Enter your South African ID number</p></div>
          <div class="verify-step"><div class="verify-step-num">2</div><p>Upload a photo of your ID document</p></div>
          <div class="verify-step"><div class="verify-step-num">3</div><p>Admin reviews and approves — usually within 24 hours</p></div>
        </div>
        <form method="POST" action="verify_id.php" enctype="multipart/form-data">
          <label>SA ID Number</label>
          <input type="text" name="id_number" placeholder="e.g. 9001015009087" maxlength="13" required>
          <label>Upload ID Document Photo</label>
          <div class="upload-area">
            <input type="file" name="id_photo" accept="image/*" required>
            <div style="font-size:28px;margin-bottom:6px">&#128247;</div>
            <div style="font-size:13px;font-weight:500">Click to upload your ID photo</div>
            <div style="font-size:12px;color:var(--muted);margin-top:4px">JPG or PNG — max 5MB</div>
          </div>
          <button type="submit" class="btn-submit">Submit for Verification</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

</div>

<footer>&copy; 2026 <span>ShareSpace</span> &mdash; Empowering South Africa's People's Economy</footer>

<script>
document.querySelectorAll('[data-tab]').forEach(function(el) {
  el.onclick = function() {
    var name = this.getAttribute('data-tab');
    document.querySelectorAll('.tab-content').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
    var target = document.getElementById('tab-' + name);
    if (target) target.classList.add('active');
    if (this.classList.contains('tab')) this.classList.add('active');
  };
});
</script>
</body>
</html>
