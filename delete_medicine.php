<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

if (!isset($_POST['id'])) {
    $response = [
        'success' => false,
        'message' => 'Missing medicine ID'
    ];
    echo json_encode($response);
    exit;
}

$dbconn = Connect();
$medicineId = mysqli_real_escape_string($dbconn, $_POST['id']);

// Start transaction
mysqli_begin_transaction($dbconn);

try {
    // Check if the medicine exists
    $query = "SELECT * FROM " . DB_TABLE_MEDICINE . " WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "i", $medicineId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        throw new Exception('Medicine not found');
    }

    // Delete related records from name_compound table
    $query = "DELETE FROM name_compound WHERE medicine_id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "i", $medicineId);
    mysqli_stmt_execute($stmt);

    // Delete related records from name_pharma table
    $query = "DELETE FROM name_pharma WHERE medicine_id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "i", $medicineId);
    mysqli_stmt_execute($stmt);

    // Delete related records from transaction_items table
    $query = "DELETE FROM transaction_items WHERE medicine_id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "i", $medicineId);
    mysqli_stmt_execute($stmt);

    // Finally, delete the medicine
    $query = "DELETE FROM " . DB_TABLE_MEDICINE . " WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "i", $medicineId);
    mysqli_stmt_execute($stmt);

    // Commit the transaction
    mysqli_commit($dbconn);

    $response = [
        'success' => true,
        'message' => 'Medicine and related records deleted successfully'
    ];
} catch (Exception $e) {
    // Rollback the transaction on error
    mysqli_rollback($dbconn);
    $response = [
        'success' => false,
        'message' => 'Error deleting medicine: ' . $e->getMessage()
    ];
}

mysqli_close($dbconn);
echo json_encode($response);
