<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['receptionist','doctor', 'med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing medicine ID']);
    exit;
}

$dbconn = Connect();
$medicineId = intval($_GET['id']);

// Fetch medicine details
$query = "SELECT m.*,
                 GROUP_CONCAT(DISTINCT np.pharmaco) as pharmacos,
                 GROUP_CONCAT(DISTINCT nc.compound) as compounds
          FROM " . DB_TABLE_MEDICINE . " m
          LEFT JOIN name_pharma np ON m.id = np.medicine_id
          LEFT JOIN name_compound nc ON m.id = nc.medicine_id
          WHERE m.id = ?
          GROUP BY m.id";

$stmt = mysqli_prepare($dbconn, $query);
mysqli_stmt_bind_param($stmt, "i", $medicineId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$medicine = mysqli_fetch_assoc($result);

if ($medicine) {
    $response = [
        'success' => true,
        'medicine' => [
            'id' => $medicine['id'],
            'name' => $medicine['name'],
            'quantity' => $medicine['quantity'],
            'cp' => $medicine['cp'],
            'sp' => $medicine['sp'],
            'expiry_date' => $medicine['expiry_date'],
            'chem_amount' => $medicine['chem_amount'],
            'buy_timestamp' => $medicine['buy_timestamp'],
            'pharmacos' => $medicine['pharmacos'] ? explode(',', $medicine['pharmacos']) : [],
            'compounds' => $medicine['compounds'] ? explode(',', $medicine['compounds']) : []
        ]
    ];
} else {
    $response = ['success' => false, 'message' => 'Medicine not found'];
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
exit;
