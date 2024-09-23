<?php
require_once 'session_config.php';

require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['doctor', 'med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

$startDate = isset($_GET['startDate']) ? mysqli_real_escape_string($dbconn, $_GET['startDate']) : null;
$endDate = isset($_GET['endDate']) ? mysqli_real_escape_string($dbconn, $_GET['endDate']) : null;

$query = "SELECT id, txn_timestamp as date, buy_sell as type, amount FROM transaction";

if ($startDate && $endDate) {
    $query .= " WHERE txn_timestamp BETWEEN '$startDate' AND '$endDate'";
}

$query .= " ORDER BY txn_timestamp DESC";

$result = mysqli_query($dbconn, $query);

$transactions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['type'] = $row['type'] == 'S' ? 'Sale' : 'Purchase';
    $transactions[] = $row;
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($transactions);
