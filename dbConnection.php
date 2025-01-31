<?php
// dbConnection.php
try {
    $dsn = "mysql:host=localhost;dbname=ar_payment_db;charset=utf8mb4";
    $username = "root";
    $password = "";  // XAMPP default, if unchanged
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
