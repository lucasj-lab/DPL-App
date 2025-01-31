<?php
require 'dbConnection.php';  // PDO connection is in dbConnection.php

header('Content-Type: application/json');

// 1) Read JSON input (from fetch POST body)
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);  // decode as associative array

// 2) Validate/parse the incoming data
$office          = $data["office"] ?? null;
$county          = $data["county"] ?? null;
$employeeName    = $data["employeeName"] ?? null;
$paymentAmount   = $data["paymentAmount"] ?? 0.0;
$partialPayment  = $data["partialPayment"] ?? false;
$partialAmt      = $data["partialPaymentAmount"] ?? 0.0;
$receiptFiles    = $data["receiptFileNames"] ?? [];

// You can do further validation here if needed

try {
    // 3) Insert into payment table
    $stmt = $pdo->prepare("
        INSERT INTO payment
        (office, county, employee_name, payment_amount, partial_payment, partial_payment_amount, payment_timestamp)
        VALUES (:office, :county, :employee_name, :payment_amount, :partial_payment, :partial_payment_amount, NOW())
    ");

    $stmt->execute([
        ':office'                => $office,
        ':county'                => $county,
        ':employee_name'         => $employeeName,
        ':payment_amount'        => $paymentAmount,
        ':partial_payment'       => $partialPayment ? 1 : 0,
        ':partial_payment_amount'=> $partialAmt
    ]);

    // 4) Get the new payment_id
    $paymentId = $pdo->lastInsertId();

    // 5) Insert file names (if any) into the receipt table
    if (!empty($receiptFiles)) {
        $stmtReceipt = $pdo->prepare("
            INSERT INTO receipt (payment_id, file_name)
            VALUES (:payment_id, :file_name)
        ");

        foreach ($receiptFiles as $fileName) {
            $stmtReceipt->execute([
                ':payment_id' => $paymentId,
                ':file_name'  => $fileName
            ]);
        }
    }

    // 6) Return success in JSON
    echo json_encode([
        "success"   => true,
        "message"   => "Payment data saved successfully.",
        "paymentId" => $paymentId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => $e->getMessage()
    ]);
}
