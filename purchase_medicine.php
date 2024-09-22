<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['receptionist'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$medicineName = mysqli_real_escape_string($dbconn, $_POST['medicineName']);
$expiryDate = mysqli_real_escape_string($dbconn, $_POST['expiryDate']);
$chemicalAmount = mysqli_real_escape_string($dbconn, $_POST['chemicalAmount']);
$quantity = intval($_POST['quantity']);
$costPrice = floatval($_POST['costPrice']);
$sellingPrice = floatval($_POST['sellingPrice']);
$supplierName = mysqli_real_escape_string($dbconn, $_POST['supplierName']);

mysqli_begin_transaction($dbconn);

try {
    // Insert or update medicine
    $query = "INSERT INTO " . DB_TABLE_MEDICINE . "
              (name, quantity, cp, sp, expiry_date, chem_amount)
              VALUES (?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
              quantity = quantity + ?,
              cp = ?,
              sp = ?,
              expiry_date = ?,
              chem_amount = ?";

    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "siddssiidss",
        $medicineName, $quantity, $costPrice, $sellingPrice, $expiryDate, $chemicalAmount,
        $quantity, $costPrice, $sellingPrice, $expiryDate, $chemicalAmount
    );
    mysqli_stmt_execute($stmt);

    $medicineId = mysqli_insert_id($dbconn);
    if ($medicineId == 0) {
        // If it was an update, get the existing medicine id
        $query = "SELECT id FROM " . DB_TABLE_MEDICINE . " WHERE name = ?";
        $stmt = mysqli_prepare($dbconn, $query);
        mysqli_stmt_bind_param($stmt, "s", $medicineName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $medicineId = $row['id'];
    }

    // Create transaction
    $totalAmount = $quantity * $costPrice;
    $query = "INSERT INTO " . DB_TABLE_TRANSACTION . " (txn_timestamp, buy_sell, amount, notes) VALUES (NOW(), 'B', ?, ?)";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "ds", $totalAmount, $supplierName);
    mysqli_stmt_execute($stmt);
    $transactionId = mysqli_insert_id($dbconn);

    // Add to transaction_items
    $query = "INSERT INTO " . DB_TABLE_TRANSACTION_ITEMS . " (transaction_id, medicine_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "iiid", $transactionId, $medicineId, $quantity, $costPrice);
    mysqli_stmt_execute($stmt);

    // Add supplier to person table if not exists
    $query = "INSERT INTO " . DB_TABLE_PERSON . " (name) VALUES (?) ON DUPLICATE KEY UPDATE pid = LAST_INSERT_ID(pid)";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "s", $supplierName);
    mysqli_stmt_execute($stmt);
    $personId = mysqli_insert_id($dbconn);

    // Link transaction to person
    $query = "INSERT INTO " . DB_TABLE_TXN_PERSON . " (id, pid_person, pid_employee) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($dbconn, $query);
    $employeeId = $_SESSION['employee_id']; // Assuming you store the employee ID in the session
    mysqli_stmt_bind_param($stmt, "iii", $transactionId, $personId, $employeeId);
    mysqli_stmt_execute($stmt);

    mysqli_commit($dbconn);

    $response = [
        'success' => true,
        'message' => "Purchase completed successfully",
        'transactionId' => $transactionId,
        'totalAmount' => $totalAmount
    ];
} catch (Exception $e) {
    mysqli_rollback($dbconn);
    $response = [
        'success' => false,
        'message' => "Error completing purchase: " . $e->getMessage()
    ];
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
