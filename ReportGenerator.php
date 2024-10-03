<?php
class ReportGenerator {
    private $data;
    private $reportType;
    private $startDate;
    private $endDate;

    public function __construct($data, $reportType, $startDate, $endDate) {
        $this->data = $data;
        $this->reportType = $reportType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function generateHTML() {
        $html = $this->getHTMLHeader();
        $html .= $this->getReportContent();
        $html .= $this->getHTMLFooter();
        return $html;
    }

    private function getHTMLHeader() {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->reportType} Report</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; margin: auto; overflow: hidden; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background-color: #3498db; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .report-info { margin-bottom: 20px; }
        .print-button { display: inline-block; padding: 10px 20px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 5px; }
        @media print {
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$this->reportType} Report</h1>
        <div class="report-info">
            <p><strong>Report Type:</strong> {$this->reportType}</p>
            <p><strong>Date Range:</strong> {$this->startDate} to {$this->endDate}</p>
            <p><strong>Generated on:</strong> {$this->getCurrentDateTime()}</p>
        </div>
        <button class="print-button" onclick="window.print();">Print Report</button>
HTML;
    }

    private function getReportContent() {
        $html = "<table><thead><tr>";

        // Add table headers
        if (!empty($this->data)) {
            foreach (array_keys($this->data[0]) as $header) {
                $html .= "<th>" . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . "</th>";
            }
        }

        $html .= "</tr></thead><tbody>";

        // Add table rows
        foreach ($this->data as $row) {
            $html .= "<tr>";
            foreach ($row as $value) {
                $html .= "<td>" . htmlspecialchars($value) . "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";

        return $html;
    }

    private function getHTMLFooter() {
        return <<<HTML
    </div>
    <script>
        window.onload = function() {
            document.title = "{$this->reportType} Report - {$this->getCurrentDateTime()}";
        }
    </script>

</body>
</html>
HTML;
    }

    private function getCurrentDateTime() {
        return date('Y-m-d H:i:s');
    }

    public function saveReport($filename) {
        $html = $this->generateHTML();
        if (file_put_contents($filename, $html) === false) {
            throw new Exception("Failed to save report to file: $filename");
        }
    }
}
