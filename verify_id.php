<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number = trim($_POST['id_number']);
    $user_id   = $_SESSION['user_id'];
    $photo_path = '';

    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === 0) {
        $uploads_dir = 'uploads/id_docs/';
        if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);
        $ext      = pathinfo($_FILES['id_photo']['name'], PATHINFO_EXTENSION);
        $filename = 'id_' . $user_id . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $uploads_dir . $filename);
        $photo_path = $uploads_dir . $filename;
    }

    // For demo purposes auto-approve — in production admin would review
    $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?")->execute([$user_id]);
}

header("Location: dashboard.php");
exit();
