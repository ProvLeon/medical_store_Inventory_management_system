<?php
require_once 'config.php';
require_once 'notifications.php';

$dbconn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
if (!$dbconn) {
    die('Error connecting to database: ' . mysqli_connect_error());
}

$notificationsManager = new Notifications($dbconn);
$notifications = $notificationsManager->getNotifications();

header('Content-Type: application/json');
echo json_encode($notifications);

mysqli_close($dbconn);
?>
