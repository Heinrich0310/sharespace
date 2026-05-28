<?php
session_start();
require 'includes/db.php';

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

$cat_filter = isset($_GET['cat']) ? $_GET['cat'] : '';
$search     = isset($_GET['search']) ? $_GET['search'] : '';
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';

$sql = "SELECT l.*, u.full_name, u.location, u.is_verified, c.category_name, c.icon
        FROM listings l
        LEFT JOIN users u ON l.user_id = u.user_id
        JOIN categories c ON l.category_id = c.category_id
        WHERE l.availability_status = 'available'";

$params = [];
if ($cat_filter) { $sql .= " AND c.category_name = ?"; $params[] = $cat_filter; }
if ($search)     { $sql .= " AND (l.title LIKE ? OR u.location LIKE ? OR l.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($location_filter) { $sql .= " AND u.location = ?"; $params[] = $location_filter; }
$sql .= " ORDER BY l.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Category fallback photos (keyed by category name — must match listing.php)
$category_photos = [
    'Tools & Equipment'   => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80',
    'Furniture & Chairs'  => 'https://images.unsplash.com/photo-1769874827774-421332072cb0?w=600&q=80',
    'Electronics & Sound' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&q=80',
    'Gardening'           => 'https://images.unsplash.com/photo-1687512966596-1aacfeaf6e54?w=600&q=80',
];

// Per-listing Unsplash photos for demo data
$listing_photos = [
    'Heavy-duty Power Drill'       => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80',
    'Angle Grinder'                => 'https://images.unsplash.com/photo-1531668361947-d00e652ac030?w=600&q=80',
    'Cement Mixer (Mini)'          => 'https://images.unsplash.com/photo-1531145910467-8d7338926919?w=600&q=80',
    'Pressure Washer'              => 'https://images.unsplash.com/photo-1630868837435-5f7abc85e012?w=600&q=80',
    'Scaffolding Set'              => 'https://images.unsplash.com/photo-1760597307051-67946f9cf865?w=600&q=80',
    'Plastic Chairs (30 pack)'     => 'https://images.unsplash.com/photo-1769874827774-421332072cb0?w=600&q=80',
    'Party Tent (6x6m)'            => 'https://images.unsplash.com/photo-1777097489810-a376ba4478e7?w=600&q=80',
    'Folding Tables (10 pack)'     => 'https://images.unsplash.com/photo-1762765685348-4bced247d12c?w=600&q=80',
    'Chafing Dishes Set (8)'       => 'https://images.unsplash.com/photo-1555244162-803834f70033?w=600&q=80',
    'Inflatable Jumping Castle'    => 'https://images.unsplash.com/photo-1706743559585-ce8d51210528?w=600&q=80',
    'Sound System + 2 Speakers'   => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&q=80',
    'DJ Controller & Mixer'        => 'https://images.unsplash.com/photo-1618107095181-e3ba0f53ee59?w=600&q=80',
    'Projector & Screen'           => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=600&q=80',
    'Generator (3.5kVA)'           => 'https://images.unsplash.com/photo-1509390144018-eeaf65052242?w=600&q=80',
    'LED Party Lights Set'         => 'https://images.unsplash.com/photo-1560801122-b59974a71aca?w=600&q=80',
    'Petrol Lawn Mower'            => 'https://images.unsplash.com/photo-1689728222087-6984f72460c4?w=600&q=80',
    'Electric Hedge Trimmer'       => 'https://images.unsplash.com/photo-1521633603986-cb82a097ffba?w=600&q=80',
    'Garden Chipper/Shredder'      => 'https://images.unsplash.com/flagged/photo-1574359364027-b62a716266c1?w=600&q=80',
    'Wheelbarrow + Garden Tools'   => 'https://images.unsplash.com/photo-1687512966596-1aacfeaf6e54?w=600&q=80',
    'Water Pump (Submersible)'     => 'https://images.unsplash.com/photo-1622768515656-d51e5dcb68c2?w=600&q=80',
    'Test Bicycle'                 => 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=600&q=80',
];

$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    $u = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $u->execute([$_SESSION['user_id']]);
    $unread_count = $u->fetchColumn();
}

$total_listings = count($listings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ShareSpace — Rent Anything from Your Community</title>
<meta name="description" content="South Africa's community rental platform. Rent tools, furniture, electronics and more from people near you.">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#FAFAF8;--bg2:#F4F0EB;--card:#FFFFFF;
  --text:#18120A;--muted:#6B5C4A;--accent:#C94A0A;--accent2:#E8870A;
  --green:#1E6B3C;--border:#E2D5C3;--shadow:0 2px 12px rgba(0,0,0,0.07);
  --ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif;
  --radius:14px;
}
body{font-family:var(--ff);background:var(--bg);color:var(--text);font-size:15px;line-height:1.6}

/* NAV */
nav{background:rgba(255,255,255,0.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:62px;position:sticky;top:0;z-index:200}
.logo{font-family:var(--fh);font-weight:800;font-size:23px;color:var(--accent);text-decoration:none;letter-spacing:-0.5px}
.logo span{color:var(--accent2)}
.nav-links{display:flex;gap:4px;align-items:center}
.nav-links a{font-size:13px;color:var(--muted);text-decoration:none;padding:7px 12px;border-radius:8px;font-weight:500;transition:.15s}
.nav-links a:hover{background:var(--bg2);color:var(--text)}
.btn-nav{background:var(--accent)!important;color:#fff!important;padding:8px 18px!important;border-radius:9px!important;font-weight:600!important}
.btn-nav:hover{background:#a83d09!important;transform:translateY(-1px)}
.nav-msg-wrap{position:relative}
.nav-dot{position:absolute;top:4px;right:6px;width:7px;height:7px;background:#E8870A;border-radius:50%;border:2px solid #fff}
.hamburger{display:none;flex-direction:column;justify-content:center;gap:5px;cursor:pointer;padding:8px;background:none;border:none;border-radius:8px}
.hamburger span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px;transition:.3s}
.hamburger.open span:nth-child(1){transform:rotate(45deg) translate(5px,5px)}
.hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0)}
.hamburger.open span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px)}

/* HERO */
.hero{position:relative;background:linear-gradient(145deg,#1A0D04 0%,#3A1E08 40%,#5C3010 100%);padding:80px 32px 100px;text-align:center;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M0 0h40v40H0zm40 40h40v40H40z'/%3E%3C/g%3E%3C/svg%3E")}
.hero-inner{position:relative;z-index:1;max-width:680px;margin:0 auto}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(232,135,10,0.15);border:1px solid rgba(232,135,10,0.3);color:#F4A261;font-size:12px;font-weight:600;padding:6px 16px;border-radius:30px;margin-bottom:24px;letter-spacing:0.3px}
.hero h1{font-family:var(--fh);font-size:clamp(32px,5.5vw,58px);font-weight:800;color:#fff;line-height:1.05;margin-bottom:18px;letter-spacing:-1px}
.hero h1 em{font-style:normal;color:#F4A261}
.hero p{color:rgba(255,255,255,0.65);font-size:16px;max-width:440px;margin:0 auto 36px;line-height:1.7}
.hero-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.btn-hero-primary{background:var(--accent);color:#fff;padding:14px 32px;border-radius:11px;font-size:15px;font-weight:600;text-decoration:none;transition:.2s;display:inline-block}
.btn-hero-primary:hover{background:#a83d09;transform:translateY(-2px);box-shadow:0 8px 24px rgba(201,74,10,0.4)}
.btn-hero-outline{background:rgba(255,255,255,0.08);color:#fff;padding:14px 32px;border-radius:11px;font-size:15px;font-weight:500;text-decoration:none;border:1.5px solid rgba(255,255,255,0.2);transition:.2s;display:inline-block}
.btn-hero-outline:hover{background:rgba(255,255,255,0.14);border-color:rgba(255,255,255,0.4)}
.hero-stats{display:flex;gap:40px;justify-content:center;margin-top:52px;flex-wrap:wrap}
.hero-stat{text-align:center}
.hero-stat-num{font-family:var(--fh);font-size:28px;font-weight:800;color:#F4A261;line-height:1}
.hero-stat-label{font-size:12px;color:rgba(255,255,255,0.45);margin-top:4px;letter-spacing:0.3px}

/* SEARCH */
.search-outer{padding:0 32px}
.search-box{background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);padding:20px;margin:0 auto;max-width:740px;transform:translateY(-32px);display:flex;gap:10px;flex-wrap:wrap;border:1px solid var(--border)}
.search-box input,.search-box select{border:1.5px solid var(--border);border-radius:9px;padding:11px 16px;font-family:var(--ff);font-size:14px;color:var(--text);background:var(--bg);flex:1;min-width:140px;outline:none;transition:.15s}
.search-box input:focus,.search-box select:focus{border-color:var(--accent);background:#fff}
.btn-search{background:var(--accent);color:#fff;border:none;border-radius:9px;padding:11px 22px;font-size:14px;font-weight:600;cursor:pointer;white-space:nowrap;transition:.2s;font-family:var(--ff)}
.btn-search:hover{background:#a83d09;transform:translateY(-1px)}

/* TRUST BAR */
.trust-bar{display:flex;gap:0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);background:#fff;padding:14px 32px;justify-content:center;flex-wrap:wrap;gap:32px;margin-bottom:8px}
.trust-item{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);font-weight:500}
.trust-icon{font-size:16px}

/* SECTIONS */
.section{padding:0 32px 40px}
.section-head{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:8px}
.section-title{font-family:var(--fh);font-size:21px;font-weight:700;letter-spacing:-0.3px}
.section-sub{font-size:13px;color:var(--muted);margin-top:2px}
.section-link{font-size:13px;color:var(--accent);text-decoration:none;font-weight:500}
.section-link:hover{text-decoration:underline}

/* CATEGORIES */
.cats{display:flex;gap:10px;flex-wrap:wrap}
.cat{display:flex;align-items:center;gap:8px;background:#fff;border:1.5px solid var(--border);border-radius:11px;padding:10px 18px;font-size:13px;font-weight:600;text-decoration:none;color:var(--text);transition:.2s}
.cat:hover{border-color:var(--accent);color:var(--accent);background:#FFF5ED}
.cat.active{background:var(--accent);color:#fff;border-color:var(--accent)}
.cat-count{font-size:11px;opacity:.7;font-weight:400}

/* LISTING GRID */
.listings{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:18px}
.listing-card{background:#fff;border:1.5px solid var(--border);border-radius:var(--radius);overflow:hidden;text-decoration:none;color:var(--text);display:block;transition:.25s;box-shadow:var(--shadow)}
.listing-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,0.12);border-color:var(--accent)}
.listing-img{height:185px;position:relative;overflow:hidden;background:var(--bg2)}
.listing-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s}
.listing-card:hover .listing-img img{transform:scale(1.06)}
.listing-badge{position:absolute;top:10px;left:10px;background:var(--green);color:#fff;font-size:11px;font-weight:600;padding:4px 11px;border-radius:20px;letter-spacing:0.2px}
.verified-icon{position:absolute;top:10px;right:10px;background:rgba(30,107,60,0.9);color:#fff;font-size:10px;padding:3px 8px;border-radius:20px;font-weight:600}
.listing-body{padding:15px}
.listing-title{font-weight:600;font-size:14px;margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:-0.1px}
.listing-loc{font-size:12px;color:var(--muted);margin-bottom:12px;display:flex;align-items:center;gap:4px}
.listing-footer{display:flex;align-items:center;justify-content:space-between}
.listing-price{font-family:var(--fh);font-size:18px;font-weight:800;color:var(--accent);letter-spacing:-0.3px}
.listing-price span{font-size:11px;color:var(--muted);font-family:var(--ff);font-weight:400}
.listing-cat-tag{font-size:11px;color:var(--muted);background:var(--bg2);padding:3px 9px;border-radius:20px}
.no-listings{text-align:center;padding:60px 20px;color:var(--muted);grid-column:1/-1}

/* HOW IT WORKS */
.how-section{background:linear-gradient(135deg,#1A0D04,#3A1E08);border-radius:20px;padding:48px 40px;margin:0 32px 40px}
.how-title{font-family:var(--fh);font-size:28px;font-weight:800;color:#fff;margin-bottom:6px;letter-spacing:-0.5px}
.how-sub{color:rgba(255,255,255,0.5);font-size:14px;margin-bottom:36px}
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px}
.step{position:relative}
.step-num{width:48px;height:48px;background:rgba(201,74,10,0.2);border:2px solid rgba(201,74,10,0.4);color:#F4A261;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-weight:800;font-size:18px;margin-bottom:14px}
.step h3{font-family:var(--fh);font-size:16px;font-weight:700;color:#fff;margin-bottom:8px}
.step p{font-size:13px;color:rgba(255,255,255,0.55);line-height:1.6}

/* MARKET STATS */
.market-section{background:#fff;border:1.5px solid var(--border);border-radius:20px;padding:36px 40px;margin:0 32px 40px;display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center}
.market-text h2{font-family:var(--fh);font-size:24px;font-weight:800;margin-bottom:12px;letter-spacing:-0.5px}
.market-text p{font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:16px}
.market-text a{color:var(--accent);font-weight:600;text-decoration:none;font-size:14px}
.market-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.market-stat-card{background:var(--bg2);border-radius:12px;padding:18px}
.market-stat-card .num{font-family:var(--fh);font-size:24px;font-weight:800;color:var(--accent);margin-bottom:4px}
.market-stat-card .label{font-size:12px;color:var(--muted);line-height:1.4}

/* NOTICE */
.notice{background:linear-gradient(135deg,#E8F5EE,#F0FAF4);border:1.5px solid #A8D5BE;padding:12px 24px;font-size:13px;margin:0 32px 0;border-radius:11px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px}
.notice strong{color:var(--green)}
.notice-links{display:flex;gap:16px}
.notice-links a{color:var(--accent);font-weight:600;text-decoration:none;font-size:13px}

/* FOOTER */
footer{background:#18120A;color:rgba(255,255,255,0.4);padding:48px 32px 32px;margin-top:20px}
.footer-inner{max-width:960px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr;gap:40px;margin-bottom:40px}
.footer-brand .logo{font-size:20px;margin-bottom:12px;display:block}
.footer-brand p{font-size:13px;line-height:1.7;max-width:280px}
.footer-col h4{font-family:var(--fh);font-size:14px;font-weight:700;color:rgba(255,255,255,0.8);margin-bottom:14px}
.footer-col a{display:block;font-size:13px;color:rgba(255,255,255,0.4);text-decoration:none;margin-bottom:8px;transition:.15s}
.footer-col a:hover{color:rgba(255,255,255,0.8)}
.footer-bottom{border-top:1px solid rgba(255,255,255,0.08);padding-top:24px;text-align:center;font-size:12px}
.footer-bottom span{color:#F4A261}

@media(max-width:700px){
  nav{padding:0 16px}
  .hamburger{display:flex}
  .nav-links{
    display:none;flex-direction:column;align-items:stretch;
    position:absolute;top:62px;left:0;right:0;
    background:#fff;border-bottom:1px solid var(--border);
    padding:12px 16px;gap:2px;z-index:199;
    box-shadow:0 8px 24px rgba(0,0,0,0.1)
  }
  .nav-links.open{display:flex}
  .nav-links a{padding:12px 16px;border-radius:8px;font-size:14px}
  .btn-nav{text-align:center;margin-top:4px}
  .hero{padding:56px 16px 72px}
  .search-outer{padding:0 16px}
  .search-box{margin:0;transform:translateY(-24px)}
  .section{padding:0 16px 32px}
  .trust-bar{padding:12px 16px;gap:16px}
  .how-section,.market-section{margin:0 16px 32px;padding:28px 20px}
  .market-section{grid-template-columns:1fr}
  .footer-inner{grid-template-columns:1fr}
  .notice{margin:0 16px}
  .hero-stats{gap:24px}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <button class="hamburger" id="hamburger" onclick="toggleNav()" aria-label="Open menu">
    <span></span><span></span><span></span>
  </button>
  <div class="nav-links" id="navLinks">
    <a href="index.php#listings">Browse</a>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="list_item.php">+ List Item</a>
      <a href="dashboard.php">My Account</a>
      <a href="messages.php" class="nav-msg-wrap">
        Messages
        <?php if($unread_count > 0): ?><span class="nav-dot"></span><?php endif; ?>
      </a>
      <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="admin/index.php">Admin</a>
      <?php endif; ?>
      <a href="logout.php" class="btn-nav">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
      <a href="register.php" class="btn-nav">Sign Up Free</a>
    <?php endif; ?>
  </div>
</nav>

<?php if(isset($_SESSION['user_id'])): ?>
<div class="notice">
  <span>Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>! Ready to rent or earn today?</span>
  <div class="notice-links">
    <a href="dashboard.php">My Account</a>
    <a href="list_item.php">+ List Item</a>
    <?php if($unread_count > 0): ?><a href="messages.php"><?= $unread_count ?> new message<?= $unread_count>1?'s':'' ?></a><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- HERO -->
<div class="hero">
  <div class="hero-inner">
    <div class="hero-badge">Built for South Africa&rsquo;s People's Economy</div>
    <h1>Rent anything from<br><em>your community</em></h1>
    <p>Turn your unused tools, furniture and equipment into income. Borrow what you need at affordable rates. Safe, verified &amp; local.</p>
    <div class="hero-btns">
      <a href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'register.php' ?>" class="btn-hero-primary">
        <?= isset($_SESSION['user_id']) ? 'My Dashboard' : 'Get Started Free' ?>
      </a>
      <a href="#listings" class="btn-hero-outline">Browse <?= $total_listings ?> Listings</a>
    </div>
    <div class="hero-stats">
      <div class="hero-stat"><div class="hero-stat-num">R900B</div><div class="hero-stat-label">People's Economy</div></div>
      <div class="hero-stat"><div class="hero-stat-num"><?= $total_listings ?>+</div><div class="hero-stat-label">Active Listings</div></div>
      <div class="hero-stat"><div class="hero-stat-num">48h</div><div class="hero-stat-label">Avg. Response</div></div>
      <div class="hero-stat"><div class="hero-stat-num">100%</div><div class="hero-stat-label">Free to Join</div></div>
    </div>
  </div>
</div>

<!-- SEARCH -->
<div class="search-outer">
  <form method="GET" action="index.php" class="search-box">
    <input type="text" name="search" placeholder="Search items, e.g. drill, tent, sound system..." value="<?= htmlspecialchars($search) ?>">
    <select name="cat">
      <option value="">All Categories</option>
      <?php foreach($cats as $c): ?>
        <option value="<?= htmlspecialchars($c['category_name']) ?>" <?= $cat_filter===$c['category_name']?'selected':'' ?>>
          <?= htmlspecialchars($c['category_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="location">
      <option value="">Any Location</option>
      <?php foreach(['Soweto','Alexandra','Diepsloot','Khayelitsha','Mitchells Plain','Tembisa'] as $loc): ?>
        <option value="<?= $loc ?>" <?= $location_filter===$loc?'selected':'' ?>><?= $loc ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-search">Search</button>
  </form>
</div>

<!-- TRUST BAR -->
<div class="trust-bar">
  <div class="trust-item">ID-Verified Users</div>
  <div class="trust-item">Rated & Reviewed</div>
  <div class="trust-item">Mobile Friendly</div>
  <div class="trust-item">Local Payments</div>
  <div class="trust-item">Community First</div>
</div>

<!-- CATEGORIES -->
<div class="section" style="padding-top:8px">
  <div class="section-head">
    <div>
      <div class="section-title">Categories</div>
      <div class="section-sub">Browse by type of item</div>
    </div>
  </div>
  <div class="cats">
    <a href="index.php#listings" class="cat <?= !$cat_filter?'active':'' ?>">All Items</a>
    <?php foreach($cats as $c): ?>
      <a href="index.php?cat=<?= urlencode($c['category_name']) ?>#listings" class="cat <?= $cat_filter===$c['category_name']?'active':'' ?>">
        <?= htmlspecialchars($c['category_name']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- LISTINGS -->
<div class="section" id="listings">
  <div class="section-head">
    <div>
      <div class="section-title">Available Near You</div>
      <div class="section-sub"><?= $total_listings ?> verified item<?= $total_listings!=1?'s':'' ?> from your community</div>
    </div>
    <?php if($cat_filter || $search || $location_filter): ?>
      <a href="index.php#listings" class="section-link">Clear filters &#10005;</a>
    <?php endif; ?>
  </div>
  <div class="listings">
    <?php if(count($listings) > 0): ?>
      <?php foreach($listings as $item):
        $photo = '';
        if(!empty($item['image_path'])) {
            $photo = $item['image_path'];
        } elseif(isset($listing_photos[$item['title']])) {
            $photo = $listing_photos[$item['title']];
        } else {
            $photo = $category_photos[$item['category_name']] ?? 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80';
        }
      ?>
        <a href="listing.php?id=<?= $item['listing_id'] ?>" class="listing-card">
          <div class="listing-img">
            <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
            <div class="listing-badge">Available</div>
            <?php if($item['is_verified']): ?><div class="verified-icon">&#10003; Verified</div><?php endif; ?>
          </div>
          <div class="listing-body">
            <div class="listing-title"><?= htmlspecialchars($item['title']) ?></div>
            <div class="listing-loc"><?= htmlspecialchars($item['location']) ?></div>
            <div class="listing-footer">
              <div class="listing-price">R<?= number_format($item['price_per_day'], 2) ?><span>/day</span></div>
              <span class="listing-cat-tag"><?= htmlspecialchars($item['category_name']) ?></span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-listings">
        <div style="font-size:48px;margin-bottom:16px"></div>
        <div style="font-size:16px;font-weight:600;margin-bottom:8px;color:var(--text)">No listings found</div>
        <div style="font-size:14px">Try a different search or <a href="index.php" style="color:var(--accent);font-weight:500">view all items</a></div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- HOW IT WORKS -->
<div class="how-section">
  <div class="how-title">How ShareSpace Works</div>
  <div class="how-sub">Three simple steps to start renting or earning</div>
  <div class="steps">
    <div class="step">
      <div class="step-num">1</div>
      <h3>Create Account</h3>
      <p>Register free in seconds. Verify your ID to build trust with your community and unlock more features.</p>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <h3>Browse or List</h3>
      <p>Find items near you at affordable daily rates, or list your unused tools, furniture and equipment to earn extra income.</p>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <h3>Rent Safely</h3>
      <p>Book securely, arrange collection or delivery, pay locally, and build your rental history with verified reviews.</p>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <h3>Earn & Grow</h3>
      <p>Turn idle assets into consistent income. Track your earnings, reviews and rental history from your dashboard.</p>
    </div>
  </div>
</div>

<!-- MARKET STATS -->
<div class="market-section">
  <div class="market-text">
    <h2>The Opportunity is Real</h2>
    <p>South Africa's People's economy is valued at over <strong>R900 billion annually</strong> — yet most of the trade happens without digital infrastructure, trust systems or fair pricing.</p>
    <p>ShareSpace bridges that gap. We give community members a secure, mobile-friendly platform to monetise their assets and access affordable rentals — keeping money inside the community.</p>
    <a href="register.php">Join ShareSpace today &rarr;</a>
  </div>
  <div class="market-stats-grid">
    <div class="market-stat-card">
      <div class="num">R900B</div>
      <div class="label">Annual value of SA People's economy</div>
    </div>
    <div class="market-stat-card">
      <div class="num">1.8M+</div>
      <div class="label">Informal traders in SA People's</div>
    </div>
    <div class="market-stat-card">
      <div class="num">20%</div>
      <div class="label">Of SA employment from informal sector</div>
    </div>
    <div class="market-stat-card">
      <div class="num">R130B</div>
      <div class="label">SA e-commerce turnover in 2025</div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <a class="logo" href="index.php" style="text-decoration:none">Share<span>Space</span></a>
      <p>South Africa&rsquo;s community rental platform. Connecting asset owners and renters in People's and informal communities.</p>
    </div>
    <div class="footer-col">
      <h4>Platform</h4>
      <a href="index.php#listings">Browse Listings</a>
      <a href="list_item.php">List an Item</a>
      <a href="register.php">Create Account</a>
      <a href="login.php">Login</a>
    </div>
    <div class="footer-col">
      <h4>Categories</h4>
      <?php foreach($cats as $c): ?>
        <a href="index.php?cat=<?= urlencode($c['category_name']) ?>#listings"><?= htmlspecialchars($c['category_name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <a href="terms.php">Terms &amp; Conditions</a>
      <a href="popia.php">Privacy Policy (POPIA)</a>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; 2026 <span>ShareSpace</span> &mdash; Empowering South Africa&rsquo;s People's Economy &mdash;
    <a href="terms.php" style="color:rgba(255,255,255,0.5);text-decoration:none">Terms</a> &middot;
    <a href="popia.php" style="color:rgba(255,255,255,0.5);text-decoration:none">Privacy</a>
  </div>
</footer>

<script>
function toggleNav(){
  document.getElementById('hamburger').classList.toggle('open');
  document.getElementById('navLinks').classList.toggle('open');
}
document.addEventListener('click',function(e){
  var nav=document.getElementById('navLinks');
  var btn=document.getElementById('hamburger');
  if(nav&&btn&&!nav.contains(e.target)&&!btn.contains(e.target)&&nav.classList.contains('open')){
    nav.classList.remove('open');btn.classList.remove('open');
  }
});
// After a search/filter, scroll down to the listings section
if(window.location.search){
  window.addEventListener('load',function(){
    var el=document.getElementById('listings');
    if(el) el.scrollIntoView({behavior:'smooth',block:'start'});
  });
}
</script>
</body>
</html>
