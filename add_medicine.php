<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], [ 'med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

// Debug: Check received POST data
// var_dump($_POST);

$name = mysqli_real_escape_string($dbconn, $_POST['name']);
$quantity = intval($_POST['quantity']);
$cp = floatval($_POST['cp']);
$sp = floatval($_POST['sp']);
$expiry_date = mysqli_real_escape_string($dbconn, $_POST['expiry_date']);
$chem_amount = mysqli_real_escape_string($dbconn, $_POST['chem_amount']);
// $pharmaco = mysqli_real_escape_string($dbconn, $_POST['itemPharmaco']);
// $compound = mysqli_real_escape_string($dbconn, $_POST['itemCompound']);

mysqli_begin_transaction($dbconn);

try {

    $medicine_id = mysqli_insert_id($dbconn);

    $query = "INSERT INTO " . DB_TABLE_MEDICINE . " (name, quantity, cp, sp, expiry_date, chem_amount, buy_timestamp)
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "siddss", $name, $quantity, $cp, $sp, $expiry_date, $chem_amount);
    mysqli_stmt_execute($stmt);

    $medicine_id = mysqli_insert_id($dbconn);

    // if (!empty($pharmaco)) {
    //     $query = "INSERT INTO name_pharma (medicine_id, pharmaco) VALUES (?, ?)";
    //     $stmt = mysqli_prepare($dbconn, $query);
    //     mysqli_stmt_bind_param($stmt, "is", $medicine_id, $pharmaco);
    //     mysqli_stmt_execute($stmt);
    // }

    // if (!empty($compound)) {
    //     $query = "INSERT INTO name_compound (medicine_id, compound) VALUES (?, ?)";
    //     $stmt = mysqli_prepare($dbconn, $query);
    //     mysqli_stmt_bind_param($stmt, "is", $medicine_id, $compound);
    //     mysqli_stmt_execute($stmt);
    // }

    // Check if the quantity is below the threshold
       $lowStockThreshold = 10; // Set your desired threshold
       if ($quantity <= $lowStockThreshold) {
           $notificationQuery = "INSERT INTO notifications (type, message, related_id, threshold) VALUES (?, ?, ?, ?)";
           $notificationStmt = mysqli_prepare($dbconn, $notificationQuery);
           $notificationType = 'low_stock';
           $notificationMessage = "Low stock alert: {$name} (Quantity: {$quantity})";
           mysqli_stmt_bind_param($notificationStmt, "ssii", $notificationType, $notificationMessage, $medicine_id, $lowStockThreshold);
           mysqli_stmt_execute($notificationStmt);
           mysqli_stmt_close($notificationStmt);
       }

    mysqli_commit($dbconn);
    $response['success'] = true;
    $response['message'] = "Item added successfully";
} catch (Exception $e) {
    mysqli_rollback($dbconn);
    $response['success'] = false;
    $response['message'] = "Error adding item: " . $e->getMessage();
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
