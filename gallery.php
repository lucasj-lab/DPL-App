<?php
// 1) DB connection (same as in save_payment.php)
$dbHost = 'localhost';
$dbName = 'ar_payment_db';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
$pdo = new PDO($dsn, $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 2) Query all payments that have a receipt_image
$sql = "SELECT payment_id, defendant_name, receipt_image
        FROM payment
        WHERE receipt_image IS NOT NULL AND receipt_image != ''";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Uploaded Receipts Gallery</title>
  <style>
    .gallery-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .gallery-item {
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 4px;
      text-align: center;
      width: 200px;
    }
    .gallery-item img {
      max-width: 100%;
      height: auto;
      display: block;
      margin: 0 auto 8px;
    }
    .download-link {
      text-decoration: none;
      color: #007BFF;
    }
    .download-link:hover {
      text-decoration: underline;
    }
    
  </style>
</head>
<body>
<h1>Uploaded Receipts Gallery</h1>

<div class="gallery-container">
  <?php foreach ($rows as $row): ?>
    <div class="gallery-item">
      <h4>Defendant: <?= htmlspecialchars($row['defendant_name']) ?></h4>

      <?php if (file_exists(__DIR__ . '/' . $row['receipt_image'])): ?>
        <!-- Show a thumbnail -->
        <img src="<?= htmlspecialchars($row['receipt_image']) ?>" alt="Receipt" />
      <?php else: ?>
        <p style="color:red;">File not found!</p>
      <?php endif; ?>

      <!-- Download link to same file -->
      <p>
        <a class="download-link" href="<?= htmlspecialchars($row['receipt_image']) ?>" download>Download</a>
      </p>
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>
