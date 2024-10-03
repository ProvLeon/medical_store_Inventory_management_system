<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'receptionist') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

// Check if the required POST data is set
if (!isset($_POST['medicineId']) || !isset($_POST['quantity']) || !isset($_POST['customerName'])) {
    $response = [
        'success' => false,
        'message' => 'Missing required data'
    ];
    echo json_encode($response);
    exit;
}

$medicineIds = $_POST['medicineId'];
$quantities = $_POST['quantity'];
$customerName = mysqli_real_escape_string($dbconn, $_POST['customerName']);

if (empty($medicineIds) || empty($quantities)) {
    $response = [
        'success' => false,
        'message' => 'No medicines selected'
    ];
    echo json_encode($response);
    exit;
}

mysqli_begin_transaction($dbconn);

try {
    // Create transaction
    $query = "INSERT INTO " . DB_TABLE_TRANSACTION . " (txn_timestamp, buy_sell, amount, notes) VALUES (NOW(), 'S', 0, ?)";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "s", $customerName);
    mysqli_stmt_execute($stmt);
    $transactionId = mysqli_insert_id($dbconn);

    $totalAmount = 0;

    // Process each medicine
    for ($i = 0; $i < count($medicineIds); $i++) {
        $medicineId = intval($medicineIds[$i]);
        $quantity = intval($quantities[$i]);

        // Get medicine details
        $query = "SELECT * FROM " . DB_TABLE_MEDICINE . " WHERE id = ?";
        $stmt = mysqli_prepare($dbconn, $query);
        mysqli_stmt_bind_param($stmt, "i", $medicineId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $medicine = mysqli_fetch_assoc($result);

        if (!$medicine) {
            throw new Exception("Medicine with ID $medicineId not found");
        }

        if ($medicine['quantity'] < $quantity) {
            throw new Exception("Insufficient quantity for " . $medicine['name']);
        }

        // Update medicine quantity
        $newQuantity = $medicine['quantity'] - $quantity;
        $query = "UPDATE " . DB_TABLE_MEDICINE . " SET quantity = ? WHERE id = ?";
        $stmt = mysqli_prepare($dbconn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $newQuantity, $medicineId);
        mysqli_stmt_execute($stmt);

        // Add to transaction_items
        $amount = $quantity * $medicine['sp'];
        $query = "INSERT INTO " . DB_TABLE_TRANSACTION_ITEMS . " (transaction_id, medicine_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbconn, $query);
        mysqli_stmt_bind_param($stmt, "iiid", $transactionId, $medicineId, $quantity, $medicine['sp']);
        mysqli_stmt_execute($stmt);

        $totalAmount += $amount;
    }

    // Update transaction total amount
    $query = "UPDATE " . DB_TABLE_TRANSACTION . " SET amount = ? WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "di", $totalAmount, $transactionId);
    mysqli_stmt_execute($stmt);

    // Add customer to person table if not exists
    $query = "INSERT INTO " . DB_TABLE_PERSON . " (name) VALUES (?) ON DUPLICATE KEY UPDATE pid = LAST_INSERT_ID(pid)";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "s", $customerName);
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
        'message' => "Sale completed successfully",
        'transactionId' => $transactionId,
        'totalAmount' => $totalAmount,
        'updateMedicineList' => true // Add this flag
    ];
} catch (Exception $e) {
    mysqli_rollback($dbconn);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
