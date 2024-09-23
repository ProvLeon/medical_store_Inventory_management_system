<?php
require_once 'session_config.php';

require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$query = "SELECT id, username, role FROM users";
$result = mysqli_query($dbconn, $query);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($users);
