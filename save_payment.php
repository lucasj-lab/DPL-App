<?php
header('Content-Type: application/json');

// 1) Use Composer's autoload for Dompdf
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 2) Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success"=>false, "error"=>"Invalid request method."]);
    exit;
}

// 3) Gather POST data
// Because we used FormData, file input is in $_FILES['receiptFile'] (if any).
$office       = $_POST['office']        ?? '';
$county       = $_POST['county']        ?? '';
$employeeName = $_POST['employeeName']  ?? '';
$dateValue    = $_POST['dateValue']     ?? '';
$defendant    = $_POST['defendantName'] ?? '';
$cash         = (float)($_POST['cash']         ?? 0);
$check        = (float)($_POST['check']        ?? 0);
$moneyOrder   = (float)($_POST['moneyOrder']   ?? 0);
$creditCard   = (float)($_POST['creditCard']   ?? 0);
$partialYN    = (int)($_POST['partialPayment'] ?? 0);
$actualAmtDue = (float)($_POST['actualAmtDue'] ?? 0);

// 4) Handle optional image upload
$uploadedImagePath = "";
if (isset($_FILES['receiptFile']) && $_FILES['receiptFile']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = [
        'image/jpeg','image/png','image/gif','image/webp',
        'image/avif','image/heic','image/heif'
    ];
    $tmpName  = $_FILES['receiptFile']['tmp_name'];
    $fileType = @mime_content_type($tmpName);

    if (in_array($fileType, $allowedTypes)) {
        // ensure uploads folder
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename   = basename($_FILES['receiptFile']['name']);
        $uniqueName = time() . "_" . $filename; 
        $targetPath = $uploadDir . $uniqueName;
        if (move_uploaded_file($tmpName, $targetPath)) {
            // store relative path that we'll later embed in PDF
            $uploadedImagePath = 'uploads/' . $uniqueName;
        }
    }
}

// 5) Insert into DB
$dbHost = 'localhost';
$dbName = 'ar_payment_db'; // Adjust if needed
$dbUser = 'root';
$dbPass = ''; // empty password

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Example schema: you must have these columns in `payment` table
    // Adjust as needed
    $sql = "
      INSERT INTO payment (
        office, county, employee_name, date_value, defendant_name,
        cash, checks, money_order, credit_card,
        partial_payment, actual_amt_due, payment_timestamp, receipt_image
      )
      VALUES (
        :office, :county, :employee_name, :date_value, :defendant_name,
        :cash, :checks, :money_order, :credit_card,
        :partial_payment, :actual_amt_due, NOW(), :receipt_image
      )
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':office'          => $office,
        ':county'          => $county,
        ':employee_name'   => $employeeName,
        ':date_value'      => $dateValue,
        ':defendant_name'  => $defendant,
        ':cash'            => $cash,
        ':checks'          => $check,
        ':money_order'     => $moneyOrder,
        ':credit_card'     => $creditCard,
        ':partial_payment' => $partialYN,
        ':actual_amt_due'  => $actualAmtDue,
        ':receipt_image'   => $uploadedImagePath
    ]);
    $paymentId = $pdo->lastInsertId();

} catch (PDOException $e) {
    echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
    exit;
}

// 6) Generate PDF via Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true); // needed if referencing images in local path
$dompdf = new Dompdf($options);

// Decide layout: if we have an uploaded image, show 2-column table.
$html = '<!DOCTYPE html><html><head>
  <meta charset="utf-8">
  <title>Payment PDF</title>
  <style>
    body { font-family: Arial, sans-serif; }
    table {
      border-collapse: collapse;
      margin-top: 20px;
      width: 100%;
    }
    th, td {
      border: 1px solid #000;
      text-align: center;
      padding: 6px;
    }
    .header-table { width: 100%; border: none; }
    .header-table td { border: none; vertical-align: top; }
  </style>
</head><body>';

if (!empty($uploadedImagePath)) {
  // 2-column layout
  $html .= '<table class="header-table">
    <tr>
      <td style="width:60%;">
        <table>
          <tr style="background-color:#f2f2f2;">
            <th>Date</th>
            <th>Defendant</th>
            <th>County</th>
            <th>Cash</th>
            <th>Check</th>
            <th>MO</th>
            <th>CC</th>
            <th>Partial(Y/N)</th>
            <th>Actual Due</th>
          </tr>
          <tr>
            <td>'.htmlspecialchars($dateValue).'</td>
            <td>'.htmlspecialchars($defendant).'</td>
            <td>'.htmlspecialchars($county).'</td>
            <td>'.number_format($cash,2).'</td>
            <td>'.number_format($check,2).'</td>
            <td>'.number_format($moneyOrder,2).'</td>
            <td>'.number_format($creditCard,2).'</td>
            <td>'.($partialYN ? 'Y':'N').'</td>
            <td>'.number_format($actualAmtDue,2).'</td>
          </tr>
        </table>
      </td>
      <td style="width:40%; text-align:center;"> 
        <p><strong>Receipt Image</strong></p>
        <img src="'.htmlspecialchars($uploadedImagePath).'" style="max-width:100%; height:auto;" alt="Receipt Image">
      </td>
    </tr>
  </table>';
} else {
  // single full-width table
  $html .= '<table>
    <tr style="background-color:#f2f2f2;">
      <th>Date</th>
      <th>Defendant</th>
      <th>County</th>
      <th>Cash</th>
      <th>Check</th>
      <th>MO</th>
      <th>CC</th>
      <th>Partial(Y/N)</th>
      <th>Actual Due</th>
    </tr>
    <tr>
      <td>'.htmlspecialchars($dateValue).'</td>
      <td>'.htmlspecialchars($defendant).'</td>
      <td>'.htmlspecialchars($county).'</td>
      <td>'.number_format($cash,2).'</td>
      <td>'.number_format($check,2).'</td>
      <td>'.number_format($moneyOrder,2).'</td>
      <td>'.number_format($creditCard,2).'</td>
      <td>'.($partialYN ? 'Y':'N').'</td>
      <td>'.number_format($actualAmtDue,2).'</td>
    </tr>
  </table>';
}

$html .= '</body></html>';

// 7) Render PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('Letter', 'portrait');
$dompdf->render();

// 8) Save PDF to file (e.g., inside 'uploads/')
$pdfOutput = $dompdf->output();
$pdfFilename = "payment_{$paymentId}.pdf";
$pdfFilepath = __DIR__ . "/uploads/" . $pdfFilename;
file_put_contents($pdfFilepath, $pdfOutput);

// 9) Return JSON with link to that PDF
// The front-end can do window.open() on it
$pdfUrl = "uploads/" . $pdfFilename;

echo json_encode([
    "success"   => true,
    "paymentId" => $paymentId,
    "pdfUrl"    => $pdfUrl
]);
