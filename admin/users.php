<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    $stmt->execute([$_POST['role'], $_POST['user_id']]);
}
// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $target_id = (int)($_POST['user_id'] ?? 0);
    $new_pw    = $_POST['new_password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (strlen($new_pw) < 6) {
        header("Location: users.php?msg=" . urlencode("Password must be at least 6 characters."));
        exit();
    }
    if ($new_pw !== $confirm) {
        header("Location: users.php?msg=" . urlencode("Passwords did not match."));
        exit();
    }
    $row = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
    $row->execute([$target_id]);
    $tgt = $row->fetch();
    if (!$tgt) {
        header("Location: users.php?msg=" . urlencode("User not found."));
        exit();
    }
    $hash = password_hash($new_pw, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?")->execute([$hash, $target_id]);
    header("Location: users.php?ok=" . urlencode("Password updated for " . $tgt['email']));
    exit();
}
// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->beginTransaction();
        // payments for rentals on user's listings
        $pdo->prepare("DELETE p FROM payments p JOIN rentals r ON p.rental_id = r.rental_id JOIN listings l ON r.listing_id = l.listing_id WHERE l.user_id = ?")->execute([$id]);
        // payments for rentals where user is renter
        $pdo->prepare("DELETE p FROM payments p JOIN rentals r ON p.rental_id = r.rental_id WHERE r.renter_id = ?")->execute([$id]);
        // reviews on rentals of user's listings
        $pdo->prepare("DELETE rv FROM reviews rv JOIN rentals r ON rv.rental_id = r.rental_id JOIN listings l ON r.listing_id = l.listing_id WHERE l.user_id = ?")->execute([$id]);
        // reviews written by user
        $pdo->prepare("DELETE FROM reviews WHERE reviewer_id = ?")->execute([$id]);
        // rentals on user's listings
        $pdo->prepare("DELETE r FROM rentals r JOIN listings l ON r.listing_id = l.listing_id WHERE l.user_id = ?")->execute([$id]);
        // rentals where user is renter
        $pdo->prepare("DELETE FROM rentals WHERE renter_id = ?")->execute([$id]);
        // wishlists pointing at user's listings
        $pdo->prepare("DELETE w FROM wishlists w JOIN listings l ON w.listing_id = l.listing_id WHERE l.user_id = ?")->execute([$id]);
        // user's listings
        $pdo->prepare("DELETE FROM listings WHERE user_id = ?")->execute([$id]);
        // user (messages + own wishlists cascade automatically)
        $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'")->execute([$id]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
    header("Location: users.php"); exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users - Admin ShareSpace</title>
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
.role-badge{font-size:11px;font-weight:500;padding:3px 10px;border-radius:6px;background:#F0EAE2;color:#6B5C4A}
.role-badge.admin{background:#FFF0E8;color:var(--accent)}
.role-badge.moderator{background:#E8F5EE;color:#2D7A4F}
select{border:1px solid var(--border);border-radius:6px;padding:4px 8px;font-size:12px;background:var(--bg)}
.btn-sm{font-size:12px;padding:4px 10px;border-radius:6px;cursor:pointer;border:1px solid var(--border);background:transparent;color:#6B5C4A}
.btn-sm.danger{border-color:#E88;color:#C0392B}
.btn-sm.danger:hover{background:#FDE8E8}
.btn-sm[type=submit]{background:var(--accent);color:#fff;border-color:var(--accent)}
.btn-sm.warn{border-color:var(--accent);color:var(--accent)}
.btn-sm.warn:hover{background:#FFF5ED}
.banner{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
.banner.ok{background:#E8F5EE;border:1px solid #2D7A4F;color:#1A4A2E}
.banner.err{background:#FDE8E8;border:1px solid #E88;color:#8B1A1A}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center}
.modal.open{display:flex}
.modal-card{background:#fff;border-radius:14px;padding:28px;width:100%;max-width:420px;margin:0 16px}
.modal-card h3{font-family:var(--fh);font-weight:700;font-size:16px;margin-bottom:6px}
.modal-card .sub{font-size:13px;color:#6B5C4A;margin-bottom:16px}
.modal-card label{font-size:13px;font-weight:500;display:block;margin-bottom:4px;margin-top:10px}
.modal-card input{width:100%;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:var(--ff);font-size:14px;background:var(--bg);outline:none}
.modal-card input:focus{border-color:var(--accent)}
.modal-actions{display:flex;gap:10px;margin-top:18px}
.modal-actions button{flex:1;border:none;border-radius:8px;padding:10px;font-size:13px;cursor:pointer;font-family:var(--ff)}
.modal-actions .primary{background:var(--accent);color:#fff}
.modal-actions .secondary{background:#F0EAE2;color:#1A1208}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-logo">Share<span>Space</span></div>
  <a href="index.php" class="nav-item">Dashboard</a>
  <a href="users.php" class="nav-item active">Users</a>
  <a href="listings.php" class="nav-item">Listings</a>
  <a href="rentals.php" class="nav-item">Rentals</a>
  <a href="../index.php" class="nav-item">View Site</a>
  <a href="../logout.php" class="nav-item">Logout</a>
</div>
<div class="main">
  <div class="topbar"><div class="topbar-title">User Management</div></div>
  <div class="content">
    <?php if(!empty($_GET['ok'])): ?>
      <div class="banner ok"><?= htmlspecialchars($_GET['ok']) ?></div>
    <?php elseif(!empty($_GET['msg'])): ?>
      <div class="banner err"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <div class="table-card">
      <div class="table-header"><h3>All Users (<?= count($users) ?>)</h3></div>
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Location</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($users as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['full_name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['location']) ?></td>
            <td>
              <form method="POST" style="display:flex;gap:6px;align-items:center">
                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                <input type="hidden" name="update_role" value="1">
                <select name="role">
                  <option value="user" <?= $u['role']==='user'?'selected':'' ?>>User</option>
                  <option value="moderator" <?= $u['role']==='moderator'?'selected':'' ?>>Moderator</option>
                  <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
                </select>
                <button type="submit" class="btn-sm" style="background:var(--accent);color:#fff;border-color:var(--accent)">Save</button>
              </form>
            </td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap">
              <button class="btn-sm warn" type="button"
                      onclick="openReset(<?= $u['user_id'] ?>, '<?= htmlspecialchars(addslashes($u['email']), ENT_QUOTES) ?>')">Reset PW</button>
              <?php if($u['user_id'] !== $_SESSION['user_id']): ?>
                <a href="users.php?delete=<?= $u['user_id'] ?>" class="btn-sm danger" onclick="return confirm('Delete this user?')">Delete</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Reset password modal -->
<div class="modal" id="resetModal">
  <div class="modal-card">
    <h3>Reset Password</h3>
    <div class="sub" id="resetSub">User</div>
    <form method="POST" id="resetForm" autocomplete="off">
      <input type="hidden" name="reset_password" value="1">
      <input type="hidden" name="user_id" id="resetUserId">
      <label>New password</label>
      <input type="password" name="new_password" id="newPw" minlength="6" required placeholder="At least 6 characters">
      <label>Confirm new password</label>
      <input type="password" name="confirm_password" id="confirmPw" minlength="6" required placeholder="Type it again">
      <div class="modal-actions">
        <button type="submit" class="primary">Update password</button>
        <button type="button" class="secondary" onclick="closeReset()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReset(id, email) {
  document.getElementById('resetUserId').value = id;
  document.getElementById('resetSub').textContent = email;
  document.getElementById('newPw').value = '';
  document.getElementById('confirmPw').value = '';
  document.getElementById('resetModal').classList.add('open');
  setTimeout(() => document.getElementById('newPw').focus(), 50);
}
function closeReset() {
  document.getElementById('resetModal').classList.remove('open');
}
document.getElementById('resetModal').addEventListener('click', function(e) {
  if (e.target === this) closeReset();
});
document.getElementById('resetForm').addEventListener('submit', function(e) {
  if (document.getElementById('newPw').value !== document.getElementById('confirmPw').value) {
    e.preventDefault();
    alert('Passwords do not match.');
  }
});
</script>
</body>
</html>
