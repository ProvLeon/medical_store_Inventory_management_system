<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$dbconn = Connect();

// Get sales data for the last 30 days
$query = "SELECT DATE(t.txn_timestamp) as date, SUM(ti.quantity * ti.price) as total_sales
          FROM " . DB_TABLE_TRANSACTION . " t
          JOIN " . DB_TABLE_TRANSACTION_ITEMS . " ti ON t.id = ti.transaction_id
          WHERE t.buy_sell = 'S' AND t.txn_timestamp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          GROUP BY DATE(t.txn_timestamp)
          ORDER BY DATE(t.txn_timestamp)";

$result = mysqli_query($dbconn, $query);

if (!$result) {
    error_log("Database query failed in get_sales_data.php: " . mysqli_error($dbconn));
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

$salesData = [];

// Initialize all days with 0 sales
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $salesData[] = ['x' => $date, 'y' => 0];
}

while ($row = mysqli_fetch_assoc($result)) {
    $index = array_search($row['date'], array_column($salesData, 'x'));
    if ($index !== false) {
        $salesData[$index]['y'] = (float)$row['total_sales'];
    }
}

mysqli_close($dbconn);

header('Content-Type: application/json');
echo json_encode($salesData);
