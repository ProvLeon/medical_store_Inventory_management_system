<?php
session_start();
if(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password'])) {
    require_once 'config.php';
    require_once 'db_connection.php';

    $username = stripslashes($_POST['username']);
    $password = stripslashes($_POST['password']);

    $dbconn = Connect();

    $query = "SELECT role FROM " . DB_TABLE_USERS . " WHERE username=? AND password=?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $gotCreds = mysqli_fetch_assoc($result);

    if($gotCreds) {
        if($gotCreds['role'] == 'med_admin') {
            $_SESSION['med_admin'] = $username;
            header("Location: med_admin_screen.php");
        } elseif($gotCreds['role'] == 'receptionist') {
            $_SESSION['receptionist'] = $username;
            header("Location: med_store_reception.php");
        } elseif($gotCreds['role'] == 'doctor') {
            $_SESSION['doctor'] = $username;
            header("Location: med_store_doctor.php");
        }
    } else {
        header("Location: index.html?error=1");
    }

    mysqli_close($dbconn);
} else {
    header("Location: index.html");
}
?>
