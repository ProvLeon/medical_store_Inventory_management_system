<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';
require_once 'notifications.php';

if (!isset($_SESSION['receptionist'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();
$notificationsManager = new Notifications($dbconn);
$notifications = $notificationsManager->getNotifications();

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($notifications);
