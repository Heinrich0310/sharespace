<?php
session_start();
require 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (!$name || !$email || !$password) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "An account with that email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, location, password_hash) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $location, $hash]);
            $new_user_id = $pdo->lastInsertId();
            $_SESSION['user_id']   = $new_user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['role']      = 'user';
            session_write_close();
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--accent:#D4530A;--border:#E8D5C0;--bg:#FFF9F4;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:32px;width:100%;max-width:420px}
.logo{font-family:var(--fh);font-weight:800;font-size:24px;color:var(--accent);text-align:center;margin-bottom:4px;text-decoration:none;display:block}
.logo span{color:#E8870A}
.subtitle{text-align:center;font-size:13px;color:#6B5C4A;margin-bottom:24px}
label{font-size:13px;font-weight:500;display:block;margin-bottom:4px;margin-top:14px;color:#1A1208}
input,select{width:100%;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:var(--ff);font-size:14px;color:#1A1208;background:var(--bg);outline:none}
input:focus,select:focus{border-color:var(--accent)}
.btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:500;cursor:pointer;margin-top:20px;font-family:var(--ff)}
.btn:hover{background:#b8440a}
.error{background:#FDE8E8;border:1px solid #E88;color:#8B1A1A;padding:10px 14px;border-radius:8px;font-size:13px;margin-top:14px}
.success{background:#E8F5EE;border:1px solid #2D7A4F;color:#1A4A2E;padding:10px 14px;border-radius:8px;font-size:13px;margin-top:14px}
.login-link{text-align:center;margin-top:16px;font-size:13px;color:#6B5C4A}
.login-link a{color:var(--accent);font-weight:500;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <div class="subtitle">Create your free account</div>

  <?php if($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?> <a href="login.php" style="color:var(--accent);font-weight:500">Login here</a></div>
  <?php endif; ?>

  <form method="POST">
    <label>Full Name *</label>
    <input type="text" name="full_name" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">

    <label>Email *</label>
    <input type="email" name="email" placeholder="you@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

    <label>Phone Number</label>
    <input type="tel" name="phone" placeholder="082 000 0000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

    <label>Location / Township</label>
    <select name="location">
      <option value="Soweto">Soweto</option>
      <option value="Alexandra">Alexandra</option>
      <option value="Diepsloot">Diepsloot</option>
      <option value="Khayelitsha">Khayelitsha</option>
      <option value="Mitchells Plain">Mitchells Plain</option>
      <option value="Tembisa">Tembisa</option>
      <option value="Other">Other</option>
    </select>

    <label>Password *</label>
    <input type="password" name="password" placeholder="At least 6 characters" required>

    <label>Confirm Password *</label>
    <input type="password" name="confirm_password" placeholder="Repeat your password" required>

    <div style="margin-top:16px;display:flex;align-items:flex-start;gap:10px">
      <input type="checkbox" name="agree_terms" id="agree_terms" required style="width:auto;margin-top:3px;flex-shrink:0">
      <label for="agree_terms" style="font-size:13px;font-weight:400;margin:0;cursor:pointer">
        I agree to the <a href="terms.php" target="_blank" style="color:var(--accent)">Terms &amp; Conditions</a>
        and <a href="popia.php" target="_blank" style="color:var(--accent)">Privacy Policy (POPIA)</a>
      </label>
    </div>

    <button type="submit" class="btn">Create Account</button>
  </form>

  <div class="login-link">Already have an account? <a href="login.php">Login here</a></div>
</div>
</body>
</html>
