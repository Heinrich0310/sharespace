<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$listing_id = (int)$_GET['id'];
$user_id    = $_SESSION['user_id'];

// Only delete if owner
$check = $pdo->prepare("SELECT listing_id FROM listings WHERE listing_id = ? AND user_id = ?");
$check->execute([$listing_id, $user_id]);
if ($check->fetch()) {
    $pdo->prepare("DELETE FROM listings WHERE listing_id = ?")->execute([$listing_id]);
}

header("Location: dashboard.php");
exit();
