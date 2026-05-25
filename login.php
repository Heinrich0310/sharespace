<?php
session_start();
require 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        session_write_close();

        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Incorrect email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--accent:#D4530A;--border:#E8D5C0;--bg:#FFF9F4;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:32px;width:100%;max-width:400px}
.logo{font-family:var(--fh);font-weight:800;font-size:24px;color:var(--accent);text-align:center;margin-bottom:4px;text-decoration:none;display:block}
.logo span{color:#E8870A}
.subtitle{text-align:center;font-size:13px;color:#6B5C4A;margin-bottom:24px}
label{font-size:13px;font-weight:500;display:block;margin-bottom:4px;margin-top:14px;color:#1A1208}
input{width:100%;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:var(--ff);font-size:14px;color:#1A1208;background:var(--bg);outline:none}
input:focus{border-color:var(--accent)}
.btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:500;cursor:pointer;margin-top:20px;font-family:var(--ff)}
.btn:hover{background:#b8440a}
.error{background:#FDE8E8;border:1px solid #E88;color:#8B1A1A;padding:10px 14px;border-radius:8px;font-size:13px;margin-top:14px}
.register-link{text-align:center;margin-top:16px;font-size:13px;color:#6B5C4A}
.register-link a{color:var(--accent);font-weight:500;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <div class="subtitle">Welcome back</div>

  <?php if($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Email</label>
    <input type="email" name="email" placeholder="you@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

    <label>Password</label>
    <input type="password" name="password" placeholder="Your password" required>

    <button type="submit" class="btn">Login</button>
  </form>

  <div class="register-link">Don't have an account? <a href="register.php">Sign up free</a></div>
</div>
</body>
</html>
