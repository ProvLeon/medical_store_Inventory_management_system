<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';
require_once 'ReportGenerator.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['doctor', 'med_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

function ensureReportDirectoryExists() {
    $dir = __DIR__ . '/reports';
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
            throw new Exception('Failed to create reports directory');
        }
    }
    if (!is_writable($dir)) {
        throw new Exception('Reports directory is not writable');
    }
    return $dir;
}

try {
    $dbconn = Connect();

    $reportType = mysqli_real_escape_string($dbconn, $_POST['reportType']);
    $startDate = mysqli_real_escape_string($dbconn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($dbconn, $_POST['endDate']);

    // Generate report based on type
    switch ($reportType) {
        case 'sales':
            $query = "SELECT t.id, t.txn_timestamp, m.name AS medicine_name, ti.quantity, ti.price, (ti.quantity * ti.price) AS total_amount
                      FROM transaction t
                      JOIN transaction_items ti ON t.id = ti.transaction_id
                      JOIN medicine m ON ti.medicine_id = m.id
                      WHERE t.txn_timestamp BETWEEN '$startDate' AND '$endDate' AND t.buy_sell = 'S'
                      ORDER BY t.txn_timestamp DESC";
            break;
        case 'inventory':
            $query = "SELECT name, quantity, cp AS cost_price, sp AS selling_price, expiry_date
                      FROM medicine
                      ORDER BY name";
            break;
        case 'profitLoss':
            $query = "SELECT
                        DATE(t.txn_timestamp) AS date,
                        SUM(CASE WHEN t.buy_sell = 'S' THEN ti.quantity * ti.price ELSE 0 END) AS sales,
                        SUM(CASE WHEN t.buy_sell = 'B' THEN ti.quantity * ti.price ELSE 0 END) AS purchases,
                        SUM(CASE WHEN t.buy_sell = 'S' THEN ti.quantity * ti.price ELSE -ti.quantity * ti.price END) AS profit_loss
                      FROM transaction t
                      JOIN transaction_items ti ON t.id = ti.transaction_id
                      WHERE t.txn_timestamp BETWEEN '$startDate' AND '$endDate'
                      GROUP BY DATE(t.txn_timestamp)
                      ORDER BY date";
            break;
        default:
            throw new Exception("Invalid report type");
    }

    $result = mysqli_query($dbconn, $query);

    if ($result) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        // Ensure reports directory exists
        $reportsDir = ensureReportDirectoryExists();

        // Generate HTML report
        $reportGenerator = new ReportGenerator($data, $reportType, $startDate, $endDate);
        $filename = $reportsDir . '/' . $reportType . '_report_' . date('Y-m-d_H-i-s') . '.html';
        $reportGenerator->saveReport($filename);

        $response['success'] = true;
        $response['reportUrl'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filename);
    } else {
        throw new Exception("Error generating report: " . mysqli_error($dbconn));
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} finally {
    if (isset($dbconn)) {
        mysqli_close($dbconn);
    }
}

header('Content-Type: application/json');
echo json_encode($response);
