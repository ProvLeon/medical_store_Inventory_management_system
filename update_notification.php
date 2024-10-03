<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$notificationId = $_POST['id'];
$notificationType = $_POST['type'];

if ($notificationType === 'low_stock') {
    // For low stock, we don't mark as read here
    $response = ['success' => true, 'message' => 'Notification acknowledged'];
} else {
    // For other types, mark as read
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "i", $notificationId);

    if (mysqli_stmt_execute($stmt)) {
        $response = ['success' => true, 'message' => 'Notification marked as read'];
    } else {
        $response = ['success' => false, 'message' => 'Error updating notification'];
    }
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
