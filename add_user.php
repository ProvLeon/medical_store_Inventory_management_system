<?php
// add_user.php
session_start();
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$username = mysqli_real_escape_string($dbconn, $_POST['username']);
$password = $_POST['password']; // Store password as-is (NOT RECOMMENDED for production)
$role = mysqli_real_escape_string($dbconn, $_POST['role']);
$name = mysqli_real_escape_string($dbconn, $_POST['name']);
$address = mysqli_real_escape_string($dbconn, $_POST['address']);

mysqli_begin_transaction($dbconn);

try {
    // Insert into person table
    $query = "INSERT INTO person (name, address) VALUES (?, ?)";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $name, $address);
    mysqli_stmt_execute($stmt);
    $person_id = mysqli_insert_id($dbconn);

    // Insert into users table
    $query = "INSERT INTO users (username, password, role, person_id) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "sssi", $username, $password, $role, $person_id);
    mysqli_stmt_execute($stmt);

    // If the role is receptionist or doctor, add to employee table
    if ($role === 'receptionist' || $role === 'doctor') {
        $salary = 50000; // Default salary
        $duty_timings = '9AM-5PM'; // Default duty timings
        $query = "INSERT INTO employee (pid, salary, duty_timings) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($dbconn, $query);
        mysqli_stmt_bind_param($stmt, "ids", $person_id, $salary, $duty_timings);
        mysqli_stmt_execute($stmt);
    }

    mysqli_commit($dbconn);

    $response = [
        'success' => true,
        'message' => "User added successfully"
    ];
} catch (Exception $e) {
    mysqli_rollback($dbconn);
    $response = [
        'success' => false,
        'message' => "Error adding user: " . $e->getMessage()
    ];
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($response);
