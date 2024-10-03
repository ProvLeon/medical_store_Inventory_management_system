<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';
require_once 'notifications.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'doctor' && $_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'med_admin')) {
    error_log('Access denied to get_notifications.php. Role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();
$notificationsManager = new Notifications($dbconn);
$notifications = $notificationsManager->getNotifications();

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($notifications);
