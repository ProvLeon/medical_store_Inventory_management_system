<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['doctor'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

// Get low stock items count
$lowStockThreshold = 10; // You can adjust this value
$query = "SELECT COUNT(*) as count FROM " . DB_TABLE_MEDICINE . " WHERE quantity <= ?";
$stmt = mysqli_prepare($dbconn, $query);
mysqli_stmt_bind_param($stmt, "i", $lowStockThreshold);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lowStockItems = mysqli_fetch_assoc($result)['count'];

// Get expiring soon count (within 30 days)
$query = "SELECT COUNT(*) as count FROM " . DB_TABLE_MEDICINE . " WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$result = mysqli_query($dbconn, $query);
$expiringSoon = mysqli_fetch_assoc($result)['count'];

mysqli_close($dbconn);

$response = [
    'lowStockItems' => $lowStockItems,
    'expiringSoon' => $expiringSoon
];

header('Content-Type: application/json');
echo json_encode($response);
