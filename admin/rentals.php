<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }

// Update rental status
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE rentals SET status='active' WHERE rental_id=?")->execute([(int)$_GET['approve']]);
    header("Location: rentals.php"); exit();
}
if (isset($_GET['complete'])) {
    $pdo->prepare("UPDATE rentals SET status='completed' WHERE rental_id=?")->execute([(int)$_GET['complete']]);
    header("Location: rentals.php"); exit();
}

$rentals = $pdo->query("SELECT r.*, l.title, u.full_name FROM rentals r JOIN listings l ON r.listing_id=l.listing_id JOIN users u ON r.renter_id=u.user_id ORDER BY r.booked_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Rentals - Admin ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--accent:#D4530A;--border:#E8D5C0;--bg:#F7F3EE;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);display:flex;min-height:100vh;font-size:14px;color:#1A1208}
.sidebar{width:200px;background:#1A1208;flex-shrink:0;padding:0 0 20px}
.sidebar-logo{padding:18px 16px;font-family:var(--fh);font-size:18px;font-weight:800;color:#fff;border-bottom:1px solid rgba(255,255,255,0.08)}
.sidebar-logo span{color:#F4A261}
.nav-item{display:block;padding:10px 16px;color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;border-left:3px solid transparent}
.nav-item:hover,.nav-item.active{background:rgba(212,83,10,0.15);color:#fff;border-left-color:var(--accent)}
.main{flex:1}
.topbar{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;height:54px;display:flex;align-items:center}
.topbar-title{font-family:var(--fh);font-size:15px;font-weight:700}
.content{padding:24px}
.table-card{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
.table-header{padding:14px 16px;border-bottom:1px solid var(--border)}
.table-header h3{font-family:var(--fh);font-size:14px;font-weight:700}
table{width:100%;border-collapse:collapse}
th{font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#6B5C4A;text-align:left;padding:10px 14px;background:#FDFAF7;border-bottom:1px solid var(--border)}
td{padding:10px 14px;border-bottom:1px solid var(--border);font-size:13px}
tr:last-child td{border-bottom:none}
.status{font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px;display:inline-block}
.status.pending{background:#FFF5E0;color:#B8860B}
.status.active{background:#E8F5EE;color:#2D7A4F}
.status.completed{background:#E8EEF5;color:#1A6BA0}
.btn-sm{font-size:12px;padding:4px 10px;border-radius:6px;cursor:pointer;border:1px solid var(--border);background:transparent;color:#6B5C4A;text-decoration:none;display:inline-block}
.btn-sm.approve{background:var(--accent);color:#fff;border-color:var(--accent)}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-logo">Share<span>Space</span></div>
  <a href="index.php" class="nav-item">&#9783; Dashboard</a>
  <a href="users.php" class="nav-item">&#128100; Users</a>
  <a href="listings.php" class="nav-item">&#127981; Listings</a>
  <a href="rentals.php" class="nav-item active">&#128203; Rentals</a>
  <a href="../index.php" class="nav-item">&#127760; View Site</a>
  <a href="../logout.php" class="nav-item">&#128682; Logout</a>
</div>
<div class="main">
  <div class="topbar"><div class="topbar-title">Rental Transactions</div></div>
  <div class="content">
    <div class="table-card">
      <div class="table-header"><h3>All Rentals (<?= count($rentals) ?>)</h3></div>
      <table>
        <thead><tr><th>#</th><th>Item</th><th>Renter</th><th>Start Date</th><th>Days</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($rentals as $r): ?>
          <tr>
            <td>#<?= $r['rental_id'] ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= $r['start_date'] ?></td>
            <td><?= $r['num_days'] ?></td>
            <td>R<?= number_format($r['total_price'],2) ?></td>
            <td><span class="status <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td style="display:flex;gap:6px">
              <?php if($r['status']==='pending'): ?>
                <a href="rentals.php?approve=<?= $r['rental_id'] ?>" class="btn-sm approve">Approve</a>
              <?php elseif($r['status']==='active'): ?>
                <a href="rentals.php?complete=<?= $r['rental_id'] ?>" class="btn-sm">Complete</a>
              <?php else: ?>
                <span style="color:#6B5C4A;font-size:12px">Done</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
