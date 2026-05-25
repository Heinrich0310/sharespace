<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$total_users    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_listings = $pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$total_rentals  = $pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
$pending        = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status='pending'")->fetchColumn();

$recent_rentals = $pdo->query("
    SELECT r.*, l.title, u.full_name
    FROM rentals r
    JOIN listings l ON r.listing_id = l.listing_id
    JOIN users u ON r.renter_id = u.user_id
    ORDER BY r.booked_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--accent:#D4530A;--border:#E8D5C0;--bg:#F7F3EE;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif;--sidebar:#1A1208}
body{font-family:var(--ff);background:var(--bg);display:flex;min-height:100vh;font-size:14px;color:#1A1208}
.sidebar{width:200px;background:var(--sidebar);flex-shrink:0;padding:0 0 20px}
.sidebar-logo{padding:18px 16px;font-family:var(--fh);font-size:18px;font-weight:800;color:#fff;border-bottom:1px solid rgba(255,255,255,0.08)}
.sidebar-logo span{color:#F4A261}
.nav-item{display:block;padding:10px 16px;color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;border-left:3px solid transparent;transition:.15s}
.nav-item:hover,.nav-item.active{background:rgba(212,83,10,0.15);color:#fff;border-left-color:var(--accent)}
.nav-section{font-size:10px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.3);padding:14px 16px 4px}
.main{flex:1;display:flex;flex-direction:column}
.topbar{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;height:54px;display:flex;align-items:center;justify-content:space-between}
.topbar-title{font-family:var(--fh);font-size:15px;font-weight:700}
.content{padding:24px}
.metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.metric{background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px}
.metric-label{font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#6B5C4A;margin-bottom:8px}
.metric-val{font-family:var(--fh);font-size:28px;font-weight:700}
.table-card{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
.table-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.table-header h3{font-family:var(--fh);font-size:14px;font-weight:700}
table{width:100%;border-collapse:collapse}
th{font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#6B5C4A;text-align:left;padding:10px 14px;background:#FDFAF7;border-bottom:1px solid var(--border)}
td{padding:10px 14px;border-bottom:1px solid var(--border);font-size:13px}
tr:last-child td{border-bottom:none}
.status{font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px;display:inline-block}
.status.pending{background:#FFF5E0;color:#B8860B}
.status.active{background:#E8F5EE;color:#2D7A4F}
.status.completed{background:#E8EEF5;color:#1A6BA0}
@media(max-width:700px){.sidebar{display:none}.metrics{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-logo">Share<span>Space</span></div>
  <div class="nav-section">Main</div>
  <a href="index.php" class="nav-item active">&#9783; Dashboard</a>
  <a href="users.php" class="nav-item">&#128100; Users</a>
  <a href="listings.php" class="nav-item">&#127981; Listings</a>
  <a href="rentals.php" class="nav-item">&#128203; Rentals</a>
  <div class="nav-section">System</div>
  <a href="../index.php" class="nav-item">&#127760; View Site</a>
  <a href="../logout.php" class="nav-item">&#128682; Logout</a>
</div>

<div class="main">
  <div class="topbar">
    <div class="topbar-title">Dashboard Overview</div>
    <div style="font-size:13px;color:#6B5C4A">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
  </div>
  <div class="content">
    <div class="metrics">
      <div class="metric"><div class="metric-label">Total Users</div><div class="metric-val"><?= $total_users ?></div></div>
      <div class="metric"><div class="metric-label">Active Listings</div><div class="metric-val"><?= $total_listings ?></div></div>
      <div class="metric"><div class="metric-label">Total Rentals</div><div class="metric-val"><?= $total_rentals ?></div></div>
      <div class="metric"><div class="metric-label">Pending</div><div class="metric-val" style="color:var(--accent)"><?= $pending ?></div></div>
    </div>

    <div class="table-card">
      <div class="table-header"><h3>Recent Rentals</h3><a href="rentals.php" style="font-size:13px;color:var(--accent);text-decoration:none">View all</a></div>
      <table>
        <thead><tr><th>Item</th><th>Renter</th><th>Days</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach($recent_rentals as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= $r['num_days'] ?></td>
            <td>R<?= number_format($r['total_price'], 2) ?></td>
            <td><span class="status <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($recent_rentals)): ?>
          <tr><td colspan="5" style="text-align:center;color:#6B5C4A;padding:24px">No rentals yet</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
