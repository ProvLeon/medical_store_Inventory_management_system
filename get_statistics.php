<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';
require_once 'notifications.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['doctor', 'med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();
$notificationsManager = new Notifications($dbconn);

$lowStockItems = count($notificationsManager->checkLowStock());
$expiringItems = count($notificationsManager->checkExpiringItems());

mysqli_close($dbconn);

$response = [
    'lowStockItems' => $lowStockItems,
    'expiringSoon' => $expiringItems
];

header('Content-Type: application/json');
echo json_encode($response);
