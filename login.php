<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password'])) {
    $username = stripslashes($_POST['username']);
    $password = stripslashes($_POST['password']);

    $dbconn = Connect();

    // For debugging
    // error_log("Login attempt for username: " . $username);

    $query = "SELECT u.id, u.role, u.password, p.pid as employee_id FROM " . DB_TABLE_USERS . " u
              LEFT JOIN " . DB_TABLE_PERSON . " p ON u.person_id = p.pid
              WHERE u.username=?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password === $user['password']) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $username;

        if($user['role'] == 'receptionist') {
            $_SESSION['receptionist'] = true;
        }

        if($user['role'] == 'receptionist' || $user['role'] == 'doctor') {
            $_SESSION['employee_id'] = $user['employee_id'];
        }

        // For debugging
        // error_log('Login successful. Session data: ' . print_r($_SESSION, true));

        mysqli_close($dbconn);

        switch($user['role']) {
            case 'med_admin':
                header("Location: med_admin_screen.php");
                exit();
            case 'receptionist':
                header("Location: med_store_reception.php");
                exit();
            case 'doctor':
                header("Location: med_store_doctor.php");
                exit();
            default:
                error_log("Unknown role: " . $user['role']);
                header("Location: index.html?error=2");
                exit();
        }
    } else {
        // Login failed
        error_log("Login failed for username: " . $username);
        mysqli_close($dbconn);

        // Redirect back to index.html with error parameter
        header("Location: index.html?error=1");
        exit();
    }
} else {
    header("Location: index.html");
    exit();
}
?>
