<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }

// Approve pending listing
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE listings SET availability_status='available' WHERE listing_id=?")->execute([(int)$_GET['approve']]);
    header("Location: listings.php"); exit();
}
// Toggle availability (available ↔ unavailable, skips pending)
if (isset($_GET['toggle'])) {
    $l = $pdo->prepare("SELECT availability_status FROM listings WHERE listing_id=?");
    $l->execute([(int)$_GET['toggle']]);
    $current = $l->fetchColumn();
    $new = $current === 'available' ? 'unavailable' : 'available';
    $pdo->prepare("UPDATE listings SET availability_status=? WHERE listing_id=?")->execute([$new, (int)$_GET['toggle']]);
    header("Location: listings.php"); exit();
}
// Delete with reason
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_listing_id'])) {
    $lid    = (int)$_POST['delete_listing_id'];
    $reason = trim($_POST['reason'] ?? '');
    // Get listing + owner info
    $row = $pdo->prepare("SELECT l.title, l.user_id FROM listings l WHERE l.listing_id = ?");
    $row->execute([$lid]);
    $listing_row = $row->fetch();
    if ($listing_row) {
        $pdo->beginTransaction();
        try {
            // Notify the owner via messages inbox
            $msg = "Your listing \"" . $listing_row['title'] . "\" has been removed by an admin.";
            if ($reason) $msg .= " Reason: " . $reason;
            $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, listing_id, message) VALUES (?, ?, NULL, ?)")
                ->execute([$_SESSION['user_id'], $listing_row['user_id'], $msg]);
            // Cascade delete
            $pdo->prepare("DELETE p FROM payments p JOIN rentals r ON p.rental_id = r.rental_id WHERE r.listing_id = ?")->execute([$lid]);
            $pdo->prepare("DELETE rv FROM reviews rv JOIN rentals r ON rv.rental_id = r.rental_id WHERE r.listing_id = ?")->execute([$lid]);
            $pdo->prepare("DELETE FROM rentals WHERE listing_id = ?")->execute([$lid]);
            $pdo->prepare("DELETE FROM wishlists WHERE listing_id = ?")->execute([$lid]);
            $pdo->prepare("DELETE FROM listings WHERE listing_id = ?")->execute([$lid]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
    header("Location: listings.php"); exit();
}

$listings = $pdo->query("SELECT l.*, u.full_name, c.category_name FROM listings l JOIN users u ON l.user_id=u.user_id JOIN categories c ON l.category_id=c.category_id ORDER BY l.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Listings - Admin ShareSpace</title>
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
.status.available{background:#E8F5EE;color:#2D7A4F}
.status.unavailable{background:#F5E8E8;color:#C0392B}
.status.pending{background:#FFF3CD;color:#856404}
.btn-sm{font-size:12px;padding:4px 10px;border-radius:6px;cursor:pointer;border:1px solid var(--border);background:transparent;color:#6B5C4A;text-decoration:none;display:inline-block}
.btn-sm.danger{border-color:#E88;color:#C0392B}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-logo">Share<span>Space</span></div>
  <a href="index.php" class="nav-item">&#9783; Dashboard</a>
  <a href="users.php" class="nav-item">&#128100; Users</a>
  <a href="listings.php" class="nav-item active">&#127981; Listings</a>
  <a href="rentals.php" class="nav-item">&#128203; Rentals</a>
  <a href="../index.php" class="nav-item">&#127760; View Site</a>
  <a href="../logout.php" class="nav-item">&#128682; Logout</a>
</div>
<div class="main">
  <div class="topbar"><div class="topbar-title">Listings Management</div></div>
  <div class="content">
    <div class="table-card">
      <div class="table-header"><h3>All Listings (<?= count($listings) ?>)</h3></div>
      <table>
        <thead><tr><th>Title</th><th>Owner</th><th>Category</th><th>Price/Day</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($listings as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['title']) ?></td>
            <td><?= htmlspecialchars($l['full_name']) ?></td>
            <td><?= htmlspecialchars($l['category_name']) ?></td>
            <td>R<?= number_format($l['price_per_day'],2) ?></td>
            <td><span class="status <?= $l['availability_status'] ?>"><?= ucfirst($l['availability_status']) ?></span></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap">
              <?php if($l['availability_status'] === 'pending'): ?>
                <a href="listings.php?approve=<?= $l['listing_id'] ?>" class="btn-sm" style="border-color:#2D7A4F;color:#2D7A4F">Approve</a>
              <?php else: ?>
                <a href="listings.php?toggle=<?= $l['listing_id'] ?>" class="btn-sm">Toggle</a>
              <?php endif; ?>
              <button class="btn-sm danger" onclick="openDelete(<?= $l['listing_id'] ?>, '<?= htmlspecialchars(addslashes($l['title']), ENT_QUOTES) ?>')">Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- Delete modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:420px;margin:0 16px">
    <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;margin-bottom:6px">Remove Listing</div>
    <div id="modalListingName" style="font-size:13px;color:#6B5C4A;margin-bottom:16px"></div>
    <form method="POST">
      <input type="hidden" name="delete_listing_id" id="modalListingId">
      <label style="font-size:13px;font-weight:500;display:block;margin-bottom:4px">Reason for removal (shown to user)</label>
      <textarea name="reason" placeholder="e.g. Listing violates community guidelines" style="width:100%;border:1px solid #E8D5C0;border-radius:8px;padding:10px;font-size:13px;min-height:80px;font-family:'DM Sans',sans-serif;outline:none;box-sizing:border-box"></textarea>
      <div style="display:flex;gap:10px;margin-top:16px">
        <button type="submit" style="flex:1;background:#C0392B;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;cursor:pointer">Remove Listing</button>
        <button type="button" onclick="closeDelete()" style="flex:1;background:#F0EAE2;color:#1A1208;border:none;border-radius:8px;padding:10px;font-size:13px;cursor:pointer">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
function openDelete(id, title) {
  document.getElementById('modalListingId').value = id;
  document.getElementById('modalListingName').textContent = 'Listing: ' + title;
  var m = document.getElementById('deleteModal');
  m.style.display = 'flex';
}
function closeDelete() {
  document.getElementById('deleteModal').style.display = 'none';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeDelete();
});
</script>
</body>
</html>
