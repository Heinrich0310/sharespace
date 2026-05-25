<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title           = trim($_POST['title']);
    $description     = trim($_POST['description']);
    $price           = (float)$_POST['price_per_day'];
    $category_id     = (int)$_POST['category_id'];
    $delivery_option = $_POST['delivery_option'] ?? 'collection';
    $image_path      = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // Use finfo for reliable MIME detection (not $_FILES['type'] which can be spoofed/wrong)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mime     = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        $allowed  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB — safe for shared hosting

        if (!in_array(strtolower($mime), $allowed)) {
            $error = "Only JPG, PNG, WEBP or GIF images are allowed. Detected: $mime";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error = "Image must be under 2MB.";
        } else {
            $upload_dir = __DIR__ . '/uploads/listings/';
            $url_dir    = 'uploads/listings/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            // Always save as .jpg for JPEGs, keep extension otherwise
            $ext_map  = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg',
                         'image/png'  => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            $ext      = $ext_map[strtolower($mime)] ?? pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('listing_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                $image_path = $url_dir . $filename;
            } else {
                $error = "Upload failed — the uploads folder may not be writable on this server.";
            }
        }
    }

    if (!$error) {
        if (!$title || !$price || !$category_id) {
            $error = "Please fill in all required fields.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO listings
                (user_id, category_id, title, description, price_per_day, image_path, delivery_option, availability_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $price, $image_path, $delivery_option]);
            $new_listing_id = $pdo->lastInsertId();
            $success = "Your listing has been submitted and is awaiting admin approval.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>List an Item - ShareSpace</title>
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
.upload-area{border:2px dashed var(--border);border-radius:12px;padding:32px;text-align:center;cursor:pointer;transition:all .2s;position:relative;margin-top:6px}
.upload-area:hover{border-color:var(--accent);background:#FFF5ED}
.upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.upload-icon{font-size:36px;margin-bottom:8px}
.upload-text{font-size:14px;font-weight:500;color:#1A1208;margin-bottom:4px}
.upload-sub{font-size:12px;color:#6B5C4A}
.preview-wrap{margin-top:12px;display:none}
.preview-wrap img{width:100%;max-height:200px;object-fit:cover;border-radius:10px;border:1px solid var(--border)}
.preview-name{font-size:12px;color:#6B5C4A;margin-top:6px;text-align:center}
.delivery-options{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:6px}
.delivery-opt{border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center;cursor:pointer;transition:.2s;position:relative}
.delivery-opt input[type=radio]{position:absolute;opacity:0;width:0;height:0}
.delivery-opt:hover{border-color:var(--accent)}
.delivery-opt.selected{border-color:var(--accent);background:#FFF5ED}
.delivery-opt-icon{font-size:22px;margin-bottom:4px}
.delivery-opt-label{font-size:12px;font-weight:500;color:#1A1208}
.delivery-opt-sub{font-size:11px;color:#6B5C4A;margin-top:2px}
.btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:500;cursor:pointer;margin-top:20px;font-family:var(--ff)}
.btn:hover{background:#b8440a}
.error{background:#FDE8E8;border:1px solid #E88;color:#8B1A1A;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
.success{background:#E8F5EE;border:1px solid #2D7A4F;color:#1A4A2E;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
</style>
</head>
<body>
<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <a class="back" href="index.php">&#8592; Back</a>
</nav>

<div class="container">
  <div class="page-title">List an Item</div>
  <div class="page-sub">Share your unused item and start earning</div>

  <div class="card">
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?> <a href="listing.php?id=<?= $new_listing_id ?>" style="color:var(--accent)">View your listing</a></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

      <label>Item Photo</label>
      <div class="upload-area" id="uploadArea">
        <input type="file" name="image" accept="image/*" id="imageInput" onchange="previewImage(this)">
        <div id="uploadPrompt">
          <div class="upload-icon">&#128247;</div>
          <div class="upload-text">Click to upload a photo</div>
          <div class="upload-sub">JPG, PNG or WEBP &mdash; max 5MB</div>
        </div>
        <div class="preview-wrap" id="previewWrap">
          <img id="previewImg" src="" alt="Preview">
          <div class="preview-name" id="previewName"></div>
        </div>
      </div>

      <label>Item Title *</label>
      <input type="text" name="title" placeholder="e.g. Heavy-duty Power Drill" required>

      <label>Category *</label>
      <select name="category_id" required>
        <option value="">Select a category</option>
        <?php foreach($cats as $c): ?>
          <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Price per Day (R) *</label>
      <input type="number" name="price_per_day" placeholder="e.g. 80" min="1" step="0.01" required>

      <label>Description</label>
      <textarea name="description" placeholder="Describe the item, its condition, and pickup arrangements..."></textarea>

      <label>Delivery Option</label>
      <div class="delivery-options">
        <label class="delivery-opt selected" id="opt-collection" onclick="selectDelivery('collection', this)">
          <input type="radio" name="delivery_option" value="collection" checked>
          <div class="delivery-opt-icon">&#128075;</div>
          <div class="delivery-opt-label">Collection</div>
          <div class="delivery-opt-sub">Renter collects</div>
        </label>
        <label class="delivery-opt" id="opt-delivery" onclick="selectDelivery('delivery', this)">
          <input type="radio" name="delivery_option" value="delivery">
          <div class="delivery-opt-icon">&#128666;</div>
          <div class="delivery-opt-label">Delivery</div>
          <div class="delivery-opt-sub">You deliver (+R50)</div>
        </label>
        <label class="delivery-opt" id="opt-both" onclick="selectDelivery('both', this)">
          <input type="radio" name="delivery_option" value="both">
          <div class="delivery-opt-icon">&#10004;</div>
          <div class="delivery-opt-label">Both</div>
          <div class="delivery-opt-sub">Either option</div>
        </label>
      </div>

      <button type="submit" class="btn">Publish Listing</button>
    </form>
  </div>
</div>

<script>
function previewImage(input) {
  const wrap   = document.getElementById('previewWrap');
  const prompt = document.getElementById('uploadPrompt');
  const img    = document.getElementById('previewImg');
  const name   = document.getElementById('previewName');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      img.src  = e.target.result;
      name.textContent = input.files[0].name;
      wrap.style.display  = 'block';
      prompt.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function selectDelivery(value, el) {
  document.querySelectorAll('.delivery-opt').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('input[type=radio]').checked = true;
}
</script>
</body>
</html>