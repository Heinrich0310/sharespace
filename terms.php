<?php session_start(); require 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terms & Conditions - ShareSpace</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--accent:#D4530A;--border:#E8D5C0;--bg:#F7F3EE;--ff:'DM Sans',sans-serif;--fh:'Syne',sans-serif}
body{font-family:var(--ff);background:var(--bg);color:#1A1208;font-size:15px;line-height:1.7}
nav{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:58px;position:sticky;top:0;z-index:100}
.logo{font-family:var(--fh);font-weight:800;font-size:22px;color:var(--accent);text-decoration:none}
.logo span{color:#E8870A}
.back{font-size:13px;color:#6B5C4A;text-decoration:none}
.container{max-width:780px;margin:0 auto;padding:48px 24px}
h1{font-family:var(--fh);font-size:32px;font-weight:800;margin-bottom:8px}
.date{font-size:13px;color:#6B5C4A;margin-bottom:36px}
h2{font-family:var(--fh);font-size:18px;font-weight:700;margin:36px 0 10px;color:var(--accent)}
p{margin-bottom:14px;color:#3A2A1A}
ul{padding-left:22px;margin-bottom:14px}
li{margin-bottom:6px;color:#3A2A1A}
.footer-links{margin-top:48px;padding-top:24px;border-top:1px solid var(--border);font-size:13px;color:#6B5C4A;display:flex;gap:20px}
.footer-links a{color:var(--accent);text-decoration:none}
</style>
</head>
<body>
<nav>
  <a class="logo" href="index.php">Share<span>Space</span></a>
  <a class="back" href="index.php">&#8592; Back to Home</a>
</nav>
<div class="container">
  <h1>Terms &amp; Conditions</h1>
  <div class="date">Last updated: May 2026</div>

  <h2>1. Acceptance of Terms</h2>
  <p>By registering or using the ShareSpace platform ("the Platform"), you agree to be bound by these Terms and Conditions. If you do not agree, please do not use the Platform.</p>

  <h2>2. About ShareSpace</h2>
  <p>ShareSpace is a peer-to-peer rental marketplace that allows users ("Owners") to list personal items for rent and other users ("Renters") to book those items. ShareSpace acts only as an intermediary and is not a party to any rental agreement between users.</p>

  <h2>3. User Accounts</h2>
  <ul>
    <li>You must be 18 years or older to register.</li>
    <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
    <li>You agree to provide accurate, current, and complete information during registration.</li>
    <li>ShareSpace reserves the right to suspend or terminate accounts that violate these terms.</li>
  </ul>

  <h2>4. Listing Items</h2>
  <ul>
    <li>Owners must only list items they own or have legal authority to rent out.</li>
    <li>All listings are subject to admin review and approval before becoming publicly visible.</li>
    <li>Listings must not include illegal, dangerous, or prohibited items.</li>
    <li>Owners are responsible for the accuracy of their listing descriptions, photos, and pricing.</li>
  </ul>

  <h2>5. Renting Items</h2>
  <ul>
    <li>Renters agree to use rented items only for their stated purpose and to return them in the same condition.</li>
    <li>Any damage caused during the rental period is the responsibility of the Renter.</li>
    <li>Renters must arrange collection or delivery directly with the Owner as agreed at the time of booking.</li>
  </ul>

  <h2>6. Payments</h2>
  <ul>
    <li>All prices are listed in South African Rand (ZAR).</li>
    <li>Payment arrangements are currently made directly between Owner and Renter.</li>
    <li>ShareSpace does not process payments and accepts no liability for payment disputes.</li>
  </ul>

  <h2>7. Reviews and Ratings</h2>
  <p>Users may leave honest reviews after completed rentals. ShareSpace reserves the right to remove reviews that are abusive, fraudulent, or in violation of these terms.</p>

  <h2>8. Prohibited Conduct</h2>
  <ul>
    <li>Posting false, misleading, or fraudulent listings.</li>
    <li>Harassing, abusing, or threatening other users.</li>
    <li>Attempting to bypass the platform to avoid fees or obligations.</li>
    <li>Listing illegal items including but not limited to weapons, drugs, or stolen property.</li>
  </ul>

  <h2>9. Limitation of Liability</h2>
  <p>ShareSpace provides the Platform "as is" and makes no warranties regarding the condition of listed items, the behaviour of users, or the outcome of any rental transaction. ShareSpace shall not be liable for any direct, indirect, or consequential damages arising from use of the Platform.</p>

  <h2>10. Changes to Terms</h2>
  <p>ShareSpace may update these Terms at any time. Continued use of the Platform after changes are posted constitutes acceptance of the revised Terms.</p>

  <h2>11. Governing Law</h2>
  <p>These Terms are governed by the laws of the Republic of South Africa. Any disputes shall be subject to the jurisdiction of South African courts.</p>

  <h2>12. Contact</h2>
  <p>For questions about these Terms, contact us at: <a href="mailto:hpotgieter0310@gmail.com" style="color:var(--accent)">hpotgieter0310@gmail.com</a></p>

  <div class="footer-links">
    <a href="popia.php">Privacy Policy (POPIA)</a>
    <a href="index.php">Back to ShareSpace</a>
  </div>
</div>
</body>
</html>
