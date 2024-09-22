<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$username = mysqli_real_escape_string($dbconn, $_POST['username']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = mysqli_real_escape_string($dbconn, $_POST['role']);

$query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
$result = mysqli_query($dbconn, $query);

$response = [];
if ($result) {
    $response['success'] = true;
    $response['message'] = "User added successfully";
} else {
    $response['success'] = false;
    $response['message'] = "Error adding user: " . mysqli_error($dbconn);
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
