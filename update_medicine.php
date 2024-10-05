<?php
require_once 'session_config.php';

require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$id = intval($_POST['id']);
$name = mysqli_real_escape_string($dbconn, $_POST['name']);
$quantity = intval($_POST['quantity']);
$cp = floatval($_POST['cp']);
$sp = floatval($_POST['sp']);
$expiry_date = !empty($_POST['expirty_date']) ? mysqli_real_escape_string($dbconn, $_POST['expirty_date']) : null;
$chem_amount = mysqli_real_escape_string($dbconn, $_POST['chem_amount']);

// Prepare the base query
$query = "UPDATE " . DB_TABLE_MEDICINE . " SET
            name = ?,
            quantity = ?,
            cp = ?,
            sp = ?,
            chem_amount = ?";

// Prepare the parameters array
$params = [$name, $quantity, $cp, $sp, $chem_amount];
$types = "sidds";

// If expiry_date is provided, add it to the query and parameters
if ($expiry_date !== null) {
    $query .= ", expiry_date = ?";
    $params[] = $expiry_date;
    $types .= "s";
}

$query .= " WHERE id = ?";
$params[] = $id;
$types .= "i";

$stmt = mysqli_prepare($dbconn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);

try {
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        $response['success'] = true;
        $response['message'] = "Item updated successfully";

        // Check if the quantity is above the threshold
        $lowStockThreshold = 10; // Set your desired threshold
        if ($quantity > $lowStockThreshold) {
            // Remove low stock notification if it exists
            $deleteNotificationQuery = "DELETE FROM notifications WHERE type = 'low_stock' AND related_id = ?";
            $deleteStmt = mysqli_prepare($dbconn, $deleteNotificationQuery);
            mysqli_stmt_bind_param($deleteStmt, "i", $id);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);
        }
    } else {
        throw new Exception(mysqli_error($dbconn));
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "Error updating item: " . $e->getMessage();
}

mysqli_stmt_close($stmt);
mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
