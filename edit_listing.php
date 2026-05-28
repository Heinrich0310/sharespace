<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$listing_id = (int)($_GET['id'] ?? 0);
$user_id    = $_SESSION['user_id'];
$is_admin   = (($_SESSION['role'] ?? '') === 'admin');

// Admins may edit any listing; regular users only their own.
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT * FROM listings WHERE listing_id = ?");
    $stmt->execute([$listing_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM listings WHERE listing_id = ? AND user_id = ?");
    $stmt->execute([$listing_id, $user_id]);
}
$listing = $stmt->fetch();

if (!$listing) {
    header("Location: " . ($is_admin ? "admin/listings.php" : "dashboard.php"));
    exit();
}

// Keep the original owner so an admin edit never reassigns ownership.
$owner_id = $listing['user_id'];

$cats    = $pdo->query("SELECT * FROM categories")->fetchAll();
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title           = trim($_POST['title']);
    $description     = trim($_POST['description']);
    $price           = (float)$_POST['price_per_day'];
    $category_id     = (int)$_POST['category_id'];
    $delivery_option = $_POST['delivery_option'] ?? 'collection';
    $availability    = $_POST['availability_status'] ?? 'available';
    $image_path      = $listing['image_path'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mime     = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        $allowed  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $max_size = 2 * 1024 * 1024;
        if (!in_array(strtolower($mime), $allowed)) {
            $error = "Only JPG, PNG, WEBP or GIF images are allowed.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error = "Image must be under 2MB.";
        } else {
            $upload_dir = __DIR__ . '/uploads/listings/';
            $url_dir    = 'uploads/listings/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext_map  = ['image/jpeg'=>'jpg','image/jpg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
            $ext      = $ext_map[strtolower($mime)] ?? 'jpg';
            $filename = uniqid('listing_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                // Try to delete old file — ignore errors if it doesn't exist
                if (!empty($listing['image_path'])) @unlink(__DIR__ . '/' . $listing['image_path']);
                $image_path = $url_dir . $filename;
            } else {
                $error = "Upload failed — the uploads folder may not be writable.";
            }
        }
    }

    if (!$error) {
        if (!$title || !$price || !$category_id) {
            $error = "Please fill in all required fields.";
        } else {
            // Scope the UPDATE to the listing's real owner (admins use $owner_id,
            // regular users can only ever match their own id).
            $scope_id = $is_admin ? $owner_id : $user_id;
            $pdo->prepare("UPDATE listings SET title=?, description=?, price_per_day=?, category_id=?, delivery_option=?, availability_status=?, image_path=? WHERE listing_id=? AND user_id=?")
                ->execute([$title, $description, $price, $category_id, $delivery_option, $availability, $image_path, $listing_id, $scope_id]);
            $listing = array_merge($listing, compact('title','description','price','category_id','delivery_option','availability_status','image_path'));
            $success = "Listing updated successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Listing - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--accent:#D4530A;--border:#E8D5C0;--bg:#FFF9F4;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);color:#1A1208}
nav{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:58px}
.logo{font-family:var(--fh);font-weight:800;font-size:22px;color:var(--accent);text-decoration:none}
.logo span{color:#E8870A}
.back{font-size:13px;color:#6B5C4A;text-decoration:none}
.back:hover{color:var(--accent)}
.container{max-width:560px;margin:40px auto;padding:0 24px}
.page-title{font-family:var(--fh);font-size:26px;font-weight:800;margin-bottom:4px}
.page-sub{font-size:14px;color:#6B5C4A;margin-bottom:28px}
.card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:28px}
label{font-size:13px;font-weight:500;display:block;margin-bottom:4px;margin-top:14px}
input,select,textarea{width:100%;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:var(--ff);font-size:14px;color:#1A1208;background:var(--bg);outline:none}
input:focus,select:focus,textarea:focus{border-color:var(--accent)}
textarea{resize:vertical;min-height:100px}
.current-img{margin-top:8px;border-radius:10px;overflow:hidden;border:1px solid var(--border);max-height:180px;display:flex;align-items:center;justify-content:center;background:#FFF5ED}
.current-img img{width:100%;max-height:180px;object-fit:cover}
.upload-area{border:2px dashed var(--border);border-radius:12px;padding:24px;text-align:center;cursor:pointer;transition:all .2s;position:relative;margin-top:8px}
.upload-area:hover{border-color:var(--accent);background:#FFF5ED}
.upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.delivery-options{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:6px}
.delivery-opt{border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center;cursor:pointer;transition:.2s;position:relative}
.delivery-opt input[type=radio]{position:absolute;opacity:0;width:0;height:0}
.delivery-opt:hover{border-color:var(--accent)}
.delivery-opt.selected{border-color:var(--accent);background:#FFF5ED}
.delivery-opt-icon{font-size:22px;margin-bottom:4px}
.delivery-opt-label{font-size:12px;font-weight:500}
.delivery-opt-sub{font-size:11px;color:#6B5C4A;margin-top:2px}
.avail-toggle{display:flex;gap:10px;margin-top:6px}
.avail-opt{flex:1;border:1px solid var(--border);border-radius:8px;padding:10px;text-align:center;cursor:pointer;font-size:13px;transition:.2s}
.avail-opt input{position:absolute;opacity:0;width:0;height:0}
.avail-opt.sel-available{border-color:#2D7A4F;background:#E8F5EE;color:#2D7A4F;font-weight:500}
.avail-opt.sel-unavailable{border-color:#C0392B;background:#F5E8E8;color:#C0392B;font-weight:500}
.btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:500;cursor:pointer;margin-top:20px;font-family:var(--ff)}
.btn:hover{background:#b8440a}
.error{background:#FDE8E8;border:1px solid #E88;color:#8B1A1A;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
.success{background:#E8F5EE;border:1px solid #2D7A4F;color:#1A4A2E;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
</style>
</head>
<body>
<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <a class="back" href="<?= $is_admin ? 'admin/listings.php' : 'dashboard.php' ?>">&#8592; <?= $is_admin ? 'Back to Admin' : 'My Account' ?></a>
</nav>

<div class="container">
  <div class="page-title">Edit Listing</div>
  <div class="page-sub"><?= $is_admin ? 'Editing as admin &mdash; changes apply to this user&rsquo;s listing' : 'Update your item details' ?></div>

  <div class="card">
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?> <a href="listing.php?id=<?= $listing_id ?>" style="color:var(--accent)">View listing</a></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

      <label>Item Photo</label>
      <?php if(!empty($listing['image_path'])): ?>
        <div class="current-img"><img src="<?= htmlspecialchars($listing['image_path']) ?>" alt="Current photo"></div>
        <div style="font-size:12px;color:#6B5C4A;margin-top:6px">Current photo shown above. Upload a new one to replace it.</div>
      <?php endif; ?>
      <div class="upload-area">
        <input type="file" name="image" accept="image/*">
        <div style="font-size:13px;font-weight:500">Click to upload a new photo</div>
        <div style="font-size:12px;color:#6B5C4A;margin-top:4px">JPG, PNG or WEBP &mdash; max 5MB (optional)</div>
      </div>

      <label>Item Title *</label>
      <input type="text" name="title" value="<?= htmlspecialchars($listing['title']) ?>" required>

      <label>Category *</label>
      <select name="category_id" required>
        <option value="">Select a category</option>
        <?php foreach($cats as $c): ?>
          <option value="<?= $c['category_id'] ?>" <?= $c['category_id'] == $listing['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Price per Day (R) *</label>
      <input type="number" name="price_per_day" value="<?= $listing['price_per_day'] ?>" min="1" step="0.01" required>

      <label>Description</label>
      <textarea name="description"><?= htmlspecialchars($listing['description'] ?? '') ?></textarea>

      <label>Availability</label>
      <div class="avail-toggle" id="availToggle">
        <label class="avail-opt <?= ($listing['availability_status'] ?? 'available') === 'available' ? 'sel-available' : '' ?>" onclick="selectAvail('available', this)">
          <input type="radio" name="availability_status" value="available" <?= ($listing['availability_status'] ?? 'available') === 'available' ? 'checked' : '' ?>>
          &#10003; Available
        </label>
        <label class="avail-opt <?= ($listing['availability_status'] ?? '') === 'unavailable' ? 'sel-unavailable' : '' ?>" onclick="selectAvail('unavailable', this)">
          <input type="radio" name="availability_status" value="unavailable" <?= ($listing['availability_status'] ?? '') === 'unavailable' ? 'checked' : '' ?>>
          &#10007; Unavailable
        </label>
      </div>

      <label>Delivery Option</label>
      <div class="delivery-options">
        <label class="delivery-opt <?= ($listing['delivery_option'] ?? 'collection') === 'collection' ? 'selected' : '' ?>" onclick="selectDelivery('collection', this)">
          <input type="radio" name="delivery_option" value="collection" <?= ($listing['delivery_option'] ?? 'collection') === 'collection' ? 'checked' : '' ?>>
          <div class="delivery-opt-icon">&#128075;</div>
          <div class="delivery-opt-label">Collection</div>
          <div class="delivery-opt-sub">Renter collects</div>
        </label>
        <label class="delivery-opt <?= ($listing['delivery_option'] ?? '') === 'delivery' ? 'selected' : '' ?>" onclick="selectDelivery('delivery', this)">
          <input type="radio" name="delivery_option" value="delivery" <?= ($listing['delivery_option'] ?? '') === 'delivery' ? 'checked' : '' ?>>
          <div class="delivery-opt-icon">&#128666;</div>
          <div class="delivery-opt-label">Delivery</div>
          <div class="delivery-opt-sub">You deliver (+R50)</div>
        </label>
        <label class="delivery-opt <?= ($listing['delivery_option'] ?? '') === 'both' ? 'selected' : '' ?>" onclick="selectDelivery('both', this)">
          <input type="radio" name="delivery_option" value="both" <?= ($listing['delivery_option'] ?? '') === 'both' ? 'checked' : '' ?>>
          <div class="delivery-opt-icon">&#10004;</div>
          <div class="delivery-opt-label">Both</div>
          <div class="delivery-opt-sub">Either option</div>
        </label>
      </div>

      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>
</div>

<script>
function selectDelivery(value, el) {
  document.querySelectorAll('.delivery-opt').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('input[type=radio]').checked = true;
}
function selectAvail(value, el) {
  document.querySelectorAll('.avail-opt').forEach(o => { o.classList.remove('sel-available','sel-unavailable'); });
  el.classList.add(value === 'available' ? 'sel-available' : 'sel-unavailable');
  el.querySelector('input[type=radio]').checked = true;
}
</script>
</body>
</html>
