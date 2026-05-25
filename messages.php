<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $listing_id  = (int)$_POST['listing_id'];
    $message     = trim($_POST['message']);
    if ($message && $receiver_id) {
        $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, listing_id, message) VALUES (?,?,?,?)")
            ->execute([$user_id, $receiver_id, $listing_id, $message]);
    }
    header("Location: messages.php?with=" . $receiver_id . "&listing=" . $listing_id);
    exit();
}

// Get all conversations — fixed for MySQL strict mode
$conversations = $pdo->prepare("
    SELECT 
        sub.other_user_id,
        MAX(u.full_name) AS other_name,
        MAX(l.title) AS listing_title,
        MAX(m.listing_id) AS listing_id,
        MAX(m.created_at) AS last_message_time,
        SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM messages m
    JOIN (
        SELECT message_id,
               CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_user_id
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
    ) sub ON sub.message_id = m.message_id
    JOIN users u ON u.user_id = sub.other_user_id
    LEFT JOIN listings l ON m.listing_id = l.listing_id
    GROUP BY sub.other_user_id
    ORDER BY last_message_time DESC
");
$conversations->execute([$user_id, $user_id, $user_id, $user_id]);
$conversations = $conversations->fetchAll();

// Active conversation
$active_with    = isset($_GET['with']) ? (int)$_GET['with'] : 0;
$active_listing = isset($_GET['listing']) ? (int)$_GET['listing'] : 0;
$thread = [];
$other_user = null;
$active_listing_info = null;

if ($active_with) {
    // Mark as read
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")
        ->execute([$active_with, $user_id]);

    $thread = $pdo->prepare("
        SELECT m.*, u.full_name AS sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
        AND (m.listing_id = ? OR ? = 0)
        ORDER BY m.created_at ASC
    ");
    $thread->execute([$user_id, $active_with, $active_with, $user_id, $active_listing, $active_listing]);
    $thread = $thread->fetchAll();

    $other_user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $other_user->execute([$active_with]);
    $other_user = $other_user->fetch();

    if ($active_listing) {
        $active_listing_info = $pdo->prepare("SELECT * FROM listings WHERE listing_id = ?");
        $active_listing_info->execute([$active_listing]);
        $active_listing_info = $active_listing_info->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#FFF9F4;--bg2:#FFF2E6;--card:#fff;--text:#1A1208;--muted:#6B5C4A;--accent:#D4530A;--green:#2D7A4F;--border:#E8D5C0;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);color:var(--text);font-size:14px;height:100vh;display:flex;flex-direction:column}
nav{background:var(--card);border-bottom:1px solid var(--border);padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:58px;flex-shrink:0}
.logo{font-family:var(--fh);font-weight:800;font-size:22px;color:var(--accent);text-decoration:none}
.logo span{color:#E8870A}
.nav-links a{font-size:13px;color:var(--muted);text-decoration:none;padding:6px 10px;border-radius:6px}
.nav-links a:hover{background:var(--bg2)}
.msg-layout{display:grid;grid-template-columns:300px 1fr;flex:1;overflow:hidden;max-width:1000px;margin:0 auto;width:100%;padding:20px 24px;gap:16px}
.convo-list{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow-y:auto}
.convo-header{padding:14px 16px;border-bottom:1px solid var(--border);font-family:var(--fh);font-size:14px;font-weight:700}
.convo-item{display:flex;gap:12px;align-items:center;padding:12px 16px;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);transition:.15s;cursor:pointer}
.convo-item:hover,.convo-item.active{background:var(--bg2)}
.convo-item:last-child{border-bottom:none}
.convo-avatar{width:38px;height:38px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:14px;flex-shrink:0}
.convo-info{flex:1;min-width:0}
.convo-name{font-size:13px;font-weight:500;margin-bottom:2px}
.convo-listing{font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.convo-meta{display:flex;flex-direction:column;align-items:flex-end;gap:4px}
.convo-time{font-size:10px;color:var(--muted)}
.unread-badge{background:var(--accent);color:#fff;font-size:10px;padding:1px 6px;border-radius:10px}
.chat-area{background:var(--card);border:1px solid var(--border);border-radius:12px;display:flex;flex-direction:column;overflow:hidden}
.chat-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
.chat-header-avatar{width:36px;height:36px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:13px}
.chat-header-info h4{font-size:14px;font-weight:500}
.chat-header-info p{font-size:12px;color:var(--muted)}
.chat-messages{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px}
.msg-bubble{max-width:70%;padding:10px 14px;border-radius:12px;font-size:13px;line-height:1.5}
.msg-bubble.sent{background:var(--accent);color:#fff;align-self:flex-end;border-bottom-right-radius:4px}
.msg-bubble.received{background:var(--bg2);color:var(--text);align-self:flex-start;border-bottom-left-radius:4px}
.msg-time{font-size:10px;opacity:0.7;margin-top:4px}
.chat-input{padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:10px}
.chat-input textarea{flex:1;border:1px solid var(--border);border-radius:8px;padding:10px 12px;font-family:var(--ff);font-size:13px;color:var(--text);background:var(--bg);outline:none;resize:none;height:42px}
.chat-input textarea:focus{border-color:var(--accent)}
.btn-send{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:0 18px;font-size:13px;font-weight:500;cursor:pointer;height:42px;font-family:var(--ff)}
.btn-send:hover{background:#b8440a}
.no-chat{display:flex;align-items:center;justify-content:center;flex:1;flex-direction:column;gap:12px;color:var(--muted)}
.no-chat-icon{font-size:48px}
@media(max-width:650px){.msg-layout{grid-template-columns:1fr}.convo-list{display:none}}
</style>
</head>
<body>
<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <div class="nav-links">
    <a href="dashboard.php">&#8592; My Account</a>
  </div>
</nav>

<div class="msg-layout">
  <div class="convo-list">
    <div class="convo-header">&#128172; Messages</div>
    <?php if(count($conversations) > 0): ?>
      <?php foreach($conversations as $c): ?>
      <a href="messages.php?with=<?= $c['other_user_id'] ?>&listing=<?= $c['listing_id'] ?>"
         class="convo-item <?= $active_with == $c['other_user_id'] ? 'active' : '' ?>">
        <div class="convo-avatar"><?= strtoupper(substr($c['other_name'], 0, 1)) ?></div>
        <div class="convo-info">
          <div class="convo-name"><?= htmlspecialchars($c['other_name']) ?></div>
          <div class="convo-listing"><?= htmlspecialchars($c['listing_title'] ?: 'General') ?></div>
        </div>
        <div class="convo-meta">
          <div class="convo-time"><?= date('d M', strtotime($c['last_message_time'])) ?></div>
          <?php if($c['unread'] > 0): ?><div class="unread-badge"><?= $c['unread'] ?></div><?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="padding:24px;text-align:center;color:var(--muted);font-size:13px">No messages yet</div>
    <?php endif; ?>
  </div>

  <div class="chat-area">
    <?php if($active_with && $other_user): ?>
      <div class="chat-header">
        <div class="chat-header-avatar"><?= strtoupper(substr($other_user['full_name'], 0, 1)) ?></div>
        <div class="chat-header-info">
          <h4><?= htmlspecialchars($other_user['full_name']) ?></h4>
          <?php if($active_listing_info): ?>
            <p>Re: <?= htmlspecialchars($active_listing_info['title']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div class="chat-messages" id="messages">
        <?php if(count($thread) > 0): ?>
          <?php foreach($thread as $m): ?>
          <div class="msg-bubble <?= $m['sender_id'] == $user_id ? 'sent' : 'received' ?>">
            <?= nl2br(htmlspecialchars($m['message'])) ?>
            <div class="msg-time"><?= date('d M, H:i', strtotime($m['created_at'])) ?></div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="text-align:center;color:var(--muted);font-size:13px;margin-top:20px">Start the conversation!</div>
        <?php endif; ?>
      </div>
      <form method="POST" class="chat-input">
        <input type="hidden" name="receiver_id" value="<?= $active_with ?>">
        <input type="hidden" name="listing_id" value="<?= $active_listing ?>">
        <input type="hidden" name="send" value="1">
        <textarea name="message" placeholder="Type a message..." required></textarea>
        <button type="submit" class="btn-send">Send</button>
      </form>
    <?php else: ?>
      <div class="no-chat">
        <div class="no-chat-icon">&#128172;</div>
        <div style="font-size:14px;font-weight:500">Select a conversation</div>
        <div style="font-size:13px">Or message an owner from a listing page</div>
      </div>
    <?php endif; ?>
  </div>
</div>
<script>
const msgs = document.getElementById('messages');
if(msgs) msgs.scrollTop = msgs.scrollHeight;
</script>
</body>
</html>
