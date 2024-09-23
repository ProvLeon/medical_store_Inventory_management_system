<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$id = intval($_POST['id']);

$query = "DELETE FROM users WHERE id = $id";
$result = mysqli_query($dbconn, $query);

$response = [];
if ($result) {
    $response['success'] = true;
    $response['message'] = "User deleted successfully";
} else {
    $response['success'] = false;
    $response['message'] = "Error deleting user: " . mysqli_error($dbconn);
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
