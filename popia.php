<?php session_start(); require 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy (POPIA) - ShareSpace</title>
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
.badge{display:inline-block;background:#FFF3CD;color:#856404;border:1px solid #FFEAA7;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin-bottom:16px}
h1{font-family:var(--fh);font-size:32px;font-weight:800;margin-bottom:8px}
.date{font-size:13px;color:#6B5C4A;margin-bottom:36px}
h2{font-family:var(--fh);font-size:18px;font-weight:700;margin:36px 0 10px;color:var(--accent)}
p{margin-bottom:14px;color:#3A2A1A}
ul{padding-left:22px;margin-bottom:14px}
li{margin-bottom:6px;color:#3A2A1A}
.highlight-box{background:#fff;border:1px solid var(--border);border-left:4px solid var(--accent);border-radius:8px;padding:16px 20px;margin:20px 0}
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
  <div class="badge">POPIA Compliant</div>
  <h1>Privacy Policy</h1>
  <div class="date">In accordance with the Protection of Personal Information Act (POPIA) No. 4 of 2013 &mdash; Last updated: May 2026</div>

  <div class="highlight-box">
    <strong>Your privacy matters.</strong> ShareSpace is committed to protecting your personal information in accordance with the Protection of Personal Information Act (POPIA). This policy explains what information we collect, how we use it, and your rights as a data subject.
  </div>

  <h2>1. Who We Are (Responsible Party)</h2>
  <p>ShareSpace is operated by Heinrich Potgieter, based in South Africa. We are the responsible party as defined under POPIA for all personal information collected through this platform.</p>
  <p>Contact: <a href="mailto:hpotgieter0310@gmail.com" style="color:var(--accent)">hpotgieter0310@gmail.com</a></p>

  <h2>2. What Personal Information We Collect</h2>
  <ul>
    <li><strong>Registration data:</strong> Full name, email address, phone number, and location.</li>
    <li><strong>Identity verification:</strong> ID number and a photo of your identity document (optional, for trust verification).</li>
    <li><strong>Listing data:</strong> Photos and descriptions of items you list for rent.</li>
    <li><strong>Transaction data:</strong> Rental bookings, dates, pricing, and payment status.</li>
    <li><strong>Communications:</strong> Messages exchanged between users on the platform.</li>
    <li><strong>Usage data:</strong> Pages visited, listings viewed, and wishlist items saved.</li>
  </ul>

  <h2>3. Why We Collect This Information (Purpose)</h2>
  <ul>
    <li>To create and manage your user account.</li>
    <li>To enable you to list, browse, and book rental items.</li>
    <li>To facilitate communication between Owners and Renters.</li>
    <li>To verify user identity and build community trust.</li>
    <li>To process and track rental transactions.</li>
    <li>To improve the platform and ensure a safe user experience.</li>
    <li>To comply with our legal obligations under South African law.</li>
  </ul>

  <h2>4. Legal Basis for Processing</h2>
  <p>We process your personal information on the following bases under POPIA:</p>
  <ul>
    <li><strong>Consent:</strong> You provide consent when you register and agree to these policies.</li>
    <li><strong>Contractual necessity:</strong> Processing is required to fulfil the rental service you signed up for.</li>
    <li><strong>Legitimate interest:</strong> To maintain platform security and prevent fraud.</li>
  </ul>

  <h2>5. How We Store and Protect Your Information</h2>
  <ul>
    <li>Passwords are stored as bcrypt hashes — never in plain text.</li>
    <li>Database access is restricted to authorised personnel only.</li>
    <li>We use prepared SQL statements to prevent unauthorised data access.</li>
    <li>ID documents are stored securely and only accessible to admins for verification purposes.</li>
  </ul>

  <h2>6. Who We Share Your Information With</h2>
  <p>We do <strong>not</strong> sell, rent, or trade your personal information to third parties. Your information may be shared:</p>
  <ul>
    <li>With other users only as necessary to complete a rental (e.g. your name and contact number shown to the item Owner upon booking).</li>
    <li>With our hosting provider (InfinityFree) as part of normal web hosting operations.</li>
    <li>Where required by law or a South African court order.</li>
  </ul>

  <h2>7. Your Rights as a Data Subject (POPIA Section 23)</h2>
  <p>Under POPIA, you have the right to:</p>
  <ul>
    <li><strong>Access:</strong> Request a copy of the personal information we hold about you.</li>
    <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information.</li>
    <li><strong>Deletion:</strong> Request deletion of your account and personal information.</li>
    <li><strong>Objection:</strong> Object to the processing of your personal information.</li>
    <li><strong>Complaint:</strong> Lodge a complaint with the Information Regulator of South Africa.</li>
  </ul>
  <p>To exercise any of these rights, email us at <a href="mailto:hpotgieter0310@gmail.com" style="color:var(--accent)">hpotgieter0310@gmail.com</a>. We will respond within 30 days.</p>

  <h2>8. Information Regulator Contact</h2>
  <p>If you believe your rights have been violated, you may contact the South African Information Regulator:</p>
  <ul>
    <li>Website: <a href="https://www.inforegulator.org.za" target="_blank" style="color:var(--accent)">www.inforegulator.org.za</a></li>
    <li>Email: inforeg@justice.gov.za</li>
  </ul>

  <h2>9. Cookies</h2>
  <p>ShareSpace uses PHP session cookies only to maintain your login state. We do not use third-party tracking cookies or advertising cookies.</p>

  <h2>10. Data Retention</h2>
  <p>We retain your personal information for as long as your account is active. If you request deletion of your account, your personal data will be removed from our systems within 30 days, except where retention is required by law.</p>

  <h2>11. Changes to This Policy</h2>
  <p>We may update this Privacy Policy from time to time. We will notify registered users of material changes via the platform inbox. Continued use of ShareSpace after changes are published constitutes acceptance.</p>

  <div class="footer-links">
    <a href="terms.php">Terms &amp; Conditions</a>
    <a href="index.php">Back to ShareSpace</a>
  </div>
</div>
</body>
</html>
