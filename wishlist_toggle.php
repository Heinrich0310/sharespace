<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id    = $_SESSION['user_id'];
$listing_id = (int)$_GET['id'];
$redirect   = $_GET['redirect'] ?? 'listing';

$check = $pdo->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND listing_id = ?");
$check->execute([$user_id, $listing_id]);

if ($check->fetch()) {
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND listing_id = ?")->execute([$user_id, $listing_id]);
} else {
    $pdo->prepare("INSERT INTO wishlists (user_id, listing_id) VALUES (?, ?)")->execute([$user_id, $listing_id]);
}

if ($redirect === 'dashboard') {
    header("Location: dashboard.php");
} else {
    header("Location: listing.php?id=" . $listing_id);
}
exit();
