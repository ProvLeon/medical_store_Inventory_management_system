<?php
require_once 'session_config.php';

require_once 'config.php';
require_once 'db_connection.php';

$dbconn = Connect();

$query = "SELECT id, name FROM medicine WHERE quantity > 0";
$result = mysqli_query($dbconn, $query);

$medicines = [];
while ($row = mysqli_fetch_assoc($result)) {
    $medicines[] = $row;
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($medicines);
