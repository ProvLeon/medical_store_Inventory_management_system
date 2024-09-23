<?php
require_once 'session_config.php';

require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$id = intval($_POST['updateItemId']);
$name = mysqli_real_escape_string($dbconn, $_POST['updateItemName']);
$quantity = intval($_POST['updateItemQuantity']);
$cp = floatval($_POST['updateItemCp']);
$sp = floatval($_POST['updateItemSp']);
$expiry_date = !empty($_POST['updateItemExpiryDate']) ? mysqli_real_escape_string($dbconn, $_POST['updateItemExpiryDate']) : null;
$chem_amount = mysqli_real_escape_string($dbconn, $_POST['updateItemChemAmount']);

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
