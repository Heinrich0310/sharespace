<?php
// ─────────────────────────────────────────────────────────────
// COPY THIS FILE TO db.php AND FILL IN YOUR OWN CREDENTIALS.
// db.php is listed in .gitignore and will NEVER be committed.
// ─────────────────────────────────────────────────────────────

if ($_SERVER['HTTP_HOST'] === 'localhost:8888' || $_SERVER['HTTP_HOST'] === 'localhost') {
    // Local (MAMP) settings
    $host     = "localhost";
    $port     = "8889";
    $dbname   = "sharespace_db";
    $username = "root";
    $password = "root";
} else {
    // Production (InfinityFree / your live host) settings
    $host     = "your-db-host.example.com";
    $port     = "3306";
    $dbname   = "your_database_name";
    $username = "your_db_username";
    $password = "your_db_password";
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
