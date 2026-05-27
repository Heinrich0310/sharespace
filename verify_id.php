<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$error   = '';
$photo_path = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number = trim($_POST['id_number'] ?? '');

    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === 0) {
        // Detect MIME from the file bytes — never trust the client-supplied
        // filename or $_FILES['type']. This blocks .php / .phtml / .htaccess
        // uploads that would otherwise be executed by Apache.
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mime     = finfo_file($finfo, $_FILES['id_photo']['tmp_name']);
        finfo_close($finfo);

        $allowed  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2 MB

        if (!in_array(strtolower($mime), $allowed, true)) {
            $error = "Only JPG, PNG or WEBP images are allowed for ID documents.";
        } elseif ($_FILES['id_photo']['size'] > $max_size) {
            $error = "ID photo must be under 2MB.";
        } elseif ($_FILES['id_photo']['size'] <= 0) {
            $error = "Uploaded file appears to be empty.";
        } else {
            $fs_dir  = __DIR__ . '/uploads/id_docs/';
            $url_dir = 'uploads/id_docs/';
            if (!is_dir($fs_dir)) mkdir($fs_dir, 0755, true);

            // Force the extension from the detected MIME — never use the
            // client's pathinfo extension, which could be ".php".
            $ext_map = [
                'image/jpeg' => 'jpg', 'image/jpg' => 'jpg',
                'image/png'  => 'png', 'image/webp' => 'webp',
            ];
            $ext      = $ext_map[strtolower($mime)] ?? 'jpg';
            $filename = 'id_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

            if (move_uploaded_file($_FILES['id_photo']['tmp_name'], $fs_dir . $filename)) {
                @chmod($fs_dir . $filename, 0644);
                $photo_path = $url_dir . $filename;
            } else {
                $error = "Upload failed. Please try again.";
            }
        }
    } else {
        $error = "Please attach a clear photo of your ID document.";
    }

    if (!$error && $photo_path) {
        // For demo purposes auto-approve — in production admin would review
        // For demo purposes auto-approve — in production admin would review
        $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?")
            ->execute([$user_id]);
        header("Location: dashboard.php?verified=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify ID - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--accent:#D4530A;--border:#E8D5C0;--bg:#FFF9F4;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);min-height:100vh;padding:24px;color:#1A1208}
nav{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:58px;margin:-24px -24px 24px}
.logo{font-family:var(--fh);font-weight:800;font-size:22px;color:var(--accent);text-decoration:none}
.logo span{color:#E8870A}
.back{font-size:13px;color:#6B5C4A;text-decoration:none}
.back:hover{color:var(--accent)}
.card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:32px;max-width:480px;margin:24px auto}
h1{font-family:var(--fh);font-size:22px;margin-bottom:6px}
.sub{font-size:13px;color:#6B5C4A;margin-bottom:20px}
label{font-size:13px;font-weight:500;display:block;margin-bottom:4px;margin-top:14px}
input[type=text]{width:100%;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:var(--ff);font-size:14px;background:var(--bg);outline:none}
input[type=text]:focus{border-color:var(--accent)}
.upload{border:2px dashed var(--border);border-radius:12px;padding:24px;text-align:center;cursor:pointer;position:relative;margin-top:6px}
.upload:hover{border-color:var(--accent);background:#FFF5ED}
.upload input{position:absolute;inset:0;opacity:0;cursor:pointer}
.btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:500;cursor:pointer;margin-top:20px;font-family:var(--ff)}
.btn:hover{background:#b8440a}
.error{background:#FDE8E8;border:1px solid #E88;color:#8B1A1A;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
.notice{background:#FFF5ED;border:1px solid var(--border);padding:10px 14px;border-radius:8px;font-size:12px;color:#6B5C4A;margin-top:14px;line-height:1.5}
</style>
</head>
<body>
<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <a class="back" href="dashboard.php">&#8592; My Account</a>
</nav>

<div class="card">
  <h1>ID Verification</h1>
  <div class="sub">Upload a clear photo of your South African ID. This helps build trust with people who rent from you.</div>

  <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>ID Number</label>
    <input type="text" name="id_number" placeholder="13-digit SA ID number" required pattern="[0-9]{13}">

    <label>Photo of ID *</label>
    <div class="upload">
      <input type="file" name="id_photo" accept="image/jpeg,image/png,image/webp" required>
      <div style="font-size:13px;font-weight:500">Click to upload</div>
      <div style="font-size:12px;color:#6B5C4A;margin-top:4px">JPG, PNG or WEBP &mdash; max 2MB</div>
    </div>

    <div class="notice">
      Your ID is stored securely and only used to verify your identity in line with the POPIA Act.
      It is never shown publicly on your listings or profile.
    </div>

    <button type="submit" class="btn">Submit for Verification</button>
  </form>
</div>
</body>
</html>
