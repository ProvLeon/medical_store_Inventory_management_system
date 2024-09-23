<?php
require_once 'session_config.php';

require_once 'config.php';
require_once 'db_connection.php';

$dbconn = Connect();

$query = "SELECT * FROM medicine";
$result = mysqli_query($dbconn, $query);

$inventory = [];
while ($row = mysqli_fetch_assoc($result)) {
    $inventory[] = $row;
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($inventory);
