<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';
require_once 'ReportGenerator.php';

if (!isset($_SESSION['med_admin']) && !isset($_SESSION['doctor'])) {
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
            $query = "SELECT * FROM transaction WHERE txn_timestamp BETWEEN '$startDate' AND '$endDate' AND buy_sell = 'S'";
            break;
        case 'inventory':
            $query = "SELECT * FROM medicine";
            break;
        case 'profitLoss':
            $query = "SELECT SUM(CASE WHEN buy_sell = 'S' THEN amount ELSE -amount END) as profit_loss FROM transaction WHERE txn_timestamp BETWEEN '$startDate' AND '$endDate'";
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
        $reportGenerator = new ReportGenerator($data, $reportType);
        $filename = $reportsDir . '/' . $reportType . '_report_' . date('Y-m-d_H-i-s') . '.html';
        $reportGenerator->saveReport($filename);

        $response['success'] = true;
        $response['reportUrl'] = str_replace(__DIR__, '', $filename);
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
