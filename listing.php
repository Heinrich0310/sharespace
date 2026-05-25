<?php
session_start();
require 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT l.*, u.full_name, u.phone, u.location, u.user_id as owner_id, u.is_verified,
           c.category_name, c.icon
    FROM listings l
    LEFT JOIN users u ON l.user_id = u.user_id
    JOIN categories c ON l.category_id = c.category_id
    WHERE l.listing_id = ?
");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) { header("Location: index.php"); exit(); }

// Unsplash fallback photos for demo listings (same as index.php)
$listing_photos = [
    'Heavy-duty Power Drill'       => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80',
    'Angle Grinder'                => 'https://images.unsplash.com/photo-1531668361947-d00e652ac030?w=600&q=80',
    'Cement Mixer (Mini)'          => 'https://images.unsplash.com/photo-1531145910467-8d7338926919?w=600&q=80',
    'Pressure Washer'              => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80',
    'Scaffolding Set'              => 'https://images.unsplash.com/photo-1760597307051-67946f9cf865?w=600&q=80',
    'Plastic Chairs (30 pack)'     => 'https://images.unsplash.com/photo-1582650448861-bd3339f97601?w=600&q=80',
    'Party Tent (6x6m)'            => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600&q=80',
    'Folding Tables (10 pack)'     => 'https://images.unsplash.com/photo-1763429338698-439aa108e7fb?w=600&q=80',
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
];

// Category fallback photos for listings without a matching title
$category_photos = [
    'Tools & Equipment'   => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80',
    'Furniture & Chairs'  => 'https://images.unsplash.com/photo-1582650448861-bd3339f97601?w=600&q=80',
    'Electronics & Sound' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&q=80',
    'Gardening'           => 'https://images.unsplash.com/photo-1687512966596-1aacfeaf6e54?w=600&q=80',
];

// Resolve the image to display
$display_photo = '';
if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])) {
    $display_photo = $item['image_path'];
} elseif (isset($listing_photos[$item['title']])) {
    $display_photo = $listing_photos[$item['title']];
} elseif (isset($category_photos[$item['category_name']])) {
    $display_photo = $category_photos[$item['category_name']];
}

// Check wishlist
$in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $wcheck = $pdo->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND listing_id = ?");
    $wcheck->execute([$_SESSION['user_id'], $id]);
    $in_wishlist = (bool)$wcheck->fetch();
}

// Delivery options
$delivery_options = ['collection' => 'Collection only', 'delivery' => 'Delivery available (+R50)', 'both' => 'Collection or Delivery'];

$book_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $item['owner_id']) {
    $start_date    = $_POST['start_date'];
    $num_days      = max(1, (int)$_POST['num_days']);
    $delivery_type = $_POST['delivery_type'] ?? 'collection';
    $delivery_fee  = ($delivery_type === 'delivery') ? 50 : 0;
    $total         = ($num_days * $item['price_per_day']) + $delivery_fee;

    if (strtotime($start_date) < strtotime('today')) {
        $start_date = date('Y-m-d');
    }

    $pdo->prepare("INSERT INTO rentals (listing_id, renter_id, start_date, num_days, total_price) VALUES (?, ?, ?, ?, ?)")
        ->execute([$id, $_SESSION['user_id'], $start_date, $num_days, $total]);
    $book_success = "Booking request sent! Total: R" . number_format($total, 2);
}

// Reviews
$reviews = $pdo->prepare("
    SELECT rv.*, u.full_name
    FROM reviews rv
    JOIN users u ON rv.reviewer_id = u.user_id
    JOIN rentals r ON rv.rental_id = r.rental_id
    WHERE r.listing_id = ?
    ORDER BY rv.created_at DESC
    LIMIT 5
");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$avg_rating = count($reviews) > 0 ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($item['title']) ?> - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#FFF9F4;--bg2:#FFF2E6;--card:#fff;--text:#1A1208;--muted:#6B5C4A;--accent:#D4530A;--green:#2D7A4F;--border:#E8D5C0;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);color:var(--text)}
nav{background:var(--card);border-bottom:1px solid var(--border);padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:58px}
.logo{font-family:var(--fh);font-weight:800;font-size:22px;color:var(--accent);text-decoration:none}
.logo span{color:#E8870A}
.back{font-size:13px;color:var(--muted);text-decoration:none}
.back:hover{color:var(--accent)}
.container{max-width:920px;margin:32px auto;padding:0 24px;display:grid;grid-template-columns:1fr 340px;gap:24px}
.item-img{border-radius:16px;height:300px;display:flex;align-items:center;justify-content:center;font-size:90px;background:#FFF5ED;border:1px solid var(--border);overflow:hidden;position:relative;margin-bottom:20px}
.item-img img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.item-img .emoji{position:relative;z-index:1}
.wishlist-btn{position:absolute;top:14px;right:14px;background:rgba(255,255,255,0.9);border:none;border-radius:50%;width:40px;height:40px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;z-index:3;transition:.2s}
.wishlist-btn:hover{transform:scale(1.1)}
.item-title{font-family:var(--fh);font-size:26px;font-weight:800;margin-bottom:10px}
.item-meta{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.meta-tag{background:var(--bg2);color:var(--accent);font-size:12px;font-weight:500;padding:4px 12px;border-radius:20px}
.meta-tag.green{background:#E8F5EE;color:var(--green)}
.verified-badge{display:inline-flex;align-items:center;gap:4px;background:#E8F5EE;color:var(--green);font-size:12px;font-weight:500;padding:4px 12px;border-radius:20px}
.item-desc{font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:20px}
.owner-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:16px}
.owner-title{font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--muted);margin-bottom:10px}
.owner-row{display:flex;align-items:center;gap:12px;margin-bottom:10px}
.owner-avatar{width:40px;height:40px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:16px}
.owner-name{font-weight:500;font-size:14px}
.owner-loc{font-size:12px;color:var(--muted)}
.msg-btn{display:block;text-align:center;background:var(--bg2);color:var(--accent);border-radius:8px;padding:9px;font-size:13px;font-weight:500;text-decoration:none;border:1px solid rgba(212,83,10,0.2)}
.msg-btn:hover{background:#FFE8D6}
.reviews-section{margin-top:24px}
.reviews-section h3{font-family:var(--fh);font-size:16px;font-weight:700;margin-bottom:12px}
.review-item{background:var(--card);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:10px}
.review-header{display:flex;justify-content:space-between;margin-bottom:6px}
.review-name{font-size:13px;font-weight:500}
.stars{color:#F4A261;font-size:13px}
.review-text{font-size:13px;color:var(--muted);line-height:1.5}
.book-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;position:sticky;top:20px}
.price-big{font-family:var(--fh);font-size:32px;font-weight:800;color:var(--accent)}
.price-big span{font-size:14px;color:var(--muted);font-family:var(--ff);font-weight:400}
.rating-row{display:flex;align-items:center;gap:8px;margin:8px 0 16px}
.avg-stars{color:#F4A261;font-size:16px}
.avg-num{font-size:13px;color:var(--muted)}
label{font-size:13px;font-weight:500;display:block;margin-bottom:4px;margin-top:12px}
input,select{width:100%;border:1px solid var(--border);border-radius:8px;padding:10px 12px;font-family:var(--ff);font-size:14px;color:var(--text);background:var(--bg);outline:none}
input:focus,select:focus{border-color:var(--accent)}
.total-row{display:flex;justify-content:space-between;align-items:center;background:var(--bg2);border-radius:8px;padding:10px 14px;margin-top:14px;font-size:14px}
.total-row strong{font-family:var(--fh);font-size:18px;color:var(--accent)}
.btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:500;cursor:pointer;margin-top:14px;font-family:var(--ff)}
.btn:hover{background:#b8440a}
.btn-login{display:block;text-align:center;background:#F5EDE4;color:var(--accent);border-radius:10px;padding:13px;font-size:14px;font-weight:500;text-decoration:none;margin-top:14px}
.success{background:#E8F5EE;border:1px solid #2D7A4F;color:#1A4A2E;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
.delivery-info{background:var(--bg2);border-radius:8px;padding:10px 14px;font-size:12px;color:var(--muted);margin-top:8px;display:flex;align-items:center;gap:6px}
@media(max-width:700px){.container{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <a class="back" href="index.php">&#8592; Back to listings</a>
</nav>

<div class="container">
  <div>
    <div class="item-img">
      <?php if($display_photo): ?>
        <img src="<?= htmlspecialchars($display_photo) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
      <?php else: ?>
        <span class="emoji"><?= $item['icon'] ?></span>
      <?php endif; ?>
      <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $item['owner_id']): ?>
        <a href="wishlist_toggle.php?id=<?= $id ?>" class="wishlist-btn" title="<?= $in_wishlist ? 'Remove from wishlist' : 'Save to wishlist' ?>">
          <?= $in_wishlist ? '❤️' : '🤍' ?>
        </a>
      <?php endif; ?>
    </div>

    <div class="item-title"><?= htmlspecialchars($item['title']) ?></div>
    <div class="item-meta">
      <span class="meta-tag"><?= htmlspecialchars($item['location']) ?></span>
      <span class="meta-tag"><?= htmlspecialchars($item['category_name']) ?></span>
      <span class="meta-tag green">Available</span>
      <?php if(!empty($item['delivery_option'])): ?>
        <span class="meta-tag" style="background:#E8EEF5;color:#1A6BA0"><?= htmlspecialchars($delivery_options[$item['delivery_option']] ?? 'Collection only') ?></span>
      <?php endif; ?>
    </div>

    <p class="item-desc"><?= nl2br(htmlspecialchars($item['description'] ?: 'No description provided.')) ?></p>

    <div class="owner-card">
      <div class="owner-title">Listed by</div>
      <div class="owner-row">
        <div class="owner-avatar"><?= strtoupper(substr($item['full_name'], 0, 1)) ?></div>
        <div>
          <div class="owner-name">
            <?= htmlspecialchars($item['full_name']) ?>
            <?php if($item['is_verified']): ?>
              <span class="verified-badge">&#10003; Verified</span>
            <?php endif; ?>
          </div>
          <div class="owner-loc"><?= htmlspecialchars($item['location']) ?></div>
        </div>
      </div>
      <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $item['owner_id']): ?>
        <a href="messages.php?with=<?= $item['owner_id'] ?>&listing=<?= $id ?>" class="msg-btn">Message <?= htmlspecialchars($item['full_name']) ?></a>
      <?php endif; ?>
    </div>

    <!-- REVIEWS -->
    <?php if(count($reviews) > 0): ?>
    <div class="reviews-section">
      <h3>Reviews (<?= count($reviews) ?>)</h3>
      <?php foreach($reviews as $rv): ?>
      <div class="review-item">
        <div class="review-header">
          <span class="review-name"><?= htmlspecialchars($rv['full_name']) ?></span>
          <span class="stars"><?= str_repeat('★', $rv['rating']) ?><?= str_repeat('☆', 5-$rv['rating']) ?></span>
        </div>
        <div class="review-text"><?= htmlspecialchars($rv['comment']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- BOOKING CARD -->
  <div>
    <div class="book-card">
      <div class="price-big">R<?= number_format($item['price_per_day'], 2) ?><span>/day</span></div>
      <?php if($avg_rating > 0): ?>
      <div class="rating-row">
        <span class="avg-stars">★</span>
        <span class="avg-num"><?= number_format($avg_rating, 1) ?> (<?= count($reviews) ?> reviews)</span>
      </div>
      <?php endif; ?>

      <?php if($book_success): ?>
        <div class="success"><?= htmlspecialchars($book_success) ?></div>
      <?php endif; ?>

      <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $item['owner_id']): ?>
        <form method="POST" id="bookForm">
          <label>Start Date</label>
          <input type="date" name="start_date" required min="<?= date('Y-m-d') ?>">

          <label>Number of Days</label>
          <input type="number" name="num_days" id="numDays" min="1" max="30" value="1" required>

          <label>Delivery Option</label>
          <select name="delivery_type" id="deliveryType" onchange="updateTotal()">
            <option value="collection">Collection only (free)</option>
            <option value="delivery">Delivery (+R50)</option>
          </select>
          <div class="delivery-info">Agree on collection/delivery details with the owner via message</div>

          <div class="total-row">
            <span>Total cost</span>
            <strong id="totalPrice">R<?= number_format($item['price_per_day'], 2) ?></strong>
          </div>
          <button type="submit" class="btn">Send Booking Request</button>
        </form>
      <?php elseif(!isset($_SESSION['user_id'])): ?>
        <p style="font-size:13px;color:var(--muted);margin-top:16px">Login to book this item.</p>
        <a href="login.php" class="btn-login">Login to Book</a>
        <a href="register.php" class="btn" style="display:block;text-align:center;text-decoration:none;margin-top:8px">Sign Up Free</a>
      <?php else: ?>
        <div style="margin-top:16px;font-size:13px;color:var(--muted);background:var(--bg2);border-radius:8px;padding:12px;text-align:center">This is your own listing</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const price = <?= $item['price_per_day'] ?>;
function updateTotal() {
  const days = parseInt(document.getElementById('numDays').value) || 1;
  const delivery = document.getElementById('deliveryType').value === 'delivery' ? 50 : 0;
  const total = (days * price) + delivery;
  document.getElementById('totalPrice').textContent = 'R' + total.toFixed(2);
}
document.getElementById('numDays')?.addEventListener('input', updateTotal);
</script>
</body>
</html>
