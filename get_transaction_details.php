<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['doctor']) && !isset($_SESSION['receptionist'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing transaction ID']);
    exit;
}

$dbconn = Connect();
$transactionId = intval($_GET['id']);

// Fetch transaction details including customer/supplier information
$query = "SELECT t.*, p.name as person_name
          FROM " . DB_TABLE_TRANSACTION . " t
          LEFT JOIN " . DB_TABLE_TXN_PERSON . " tp ON t.id = tp.id
          LEFT JOIN " . DB_TABLE_PERSON . " p ON tp.pid_person = p.pid
          WHERE t.id = ?";

$stmt = mysqli_prepare($dbconn, $query);
mysqli_stmt_bind_param($stmt, "i", $transactionId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaction = mysqli_fetch_assoc($result);

if ($transaction) {
    // Fetch transaction items
    $itemsQuery = "SELECT m.name, ti.quantity, ti.price
                   FROM " . DB_TABLE_TRANSACTION_ITEMS . " ti
                   JOIN " . DB_TABLE_MEDICINE . " m ON ti.medicine_id = m.id
                   WHERE ti.transaction_id = ?";

    $itemsStmt = mysqli_prepare($dbconn, $itemsQuery);
    mysqli_stmt_bind_param($itemsStmt, "i", $transactionId);
    mysqli_stmt_execute($itemsStmt);
    $itemsResult = mysqli_stmt_get_result($itemsStmt);

    $items = [];
    while ($item = mysqli_fetch_assoc($itemsResult)) {
        $items[] = $item;
    }

    $response = [
        'success' => true,
        'transaction' => [
            'date' => $transaction['txn_timestamp'],
            'type' => $transaction['buy_sell'] == 'B' ? 'Purchase' : 'Sale',
            'amount' => $transaction['amount'],
            'person' => $transaction['person_name'] ?? 'N/A',
            'items' => $items
        ]
    ];
} else {
    $response = ['success' => false, 'message' => 'Transaction not found'];
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
