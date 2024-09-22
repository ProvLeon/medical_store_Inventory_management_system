<?php
class ReportGenerator {
    private $data;
    private $reportType;

    public function __construct($data, $reportType) {
        $this->data = $data;
        $this->reportType = $reportType;
    }

    public function generateHTML() {
        $html = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>" . ucfirst($this->reportType) . " Report</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h1>" . ucfirst($this->reportType) . " Report</h1>
            <table>
                <thead>
                    <tr>";

        // Add table headers
        if (!empty($this->data)) {
            foreach (array_keys($this->data[0]) as $header) {
                $html .= "<th>" . htmlspecialchars($header) . "</th>";
            }
        }

        $html .= "
                    </tr>
                </thead>
                <tbody>";

        // Add table rows
        foreach ($this->data as $row) {
            $html .= "<tr>";
            foreach ($row as $value) {
                $html .= "<td>" . htmlspecialchars($value) . "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "
                </tbody>
            </table>
        </body>
        </html>";

        return $html;
    }

    public function saveReport($filename) {
        $html = $this->generateHTML();
        if (file_put_contents($filename, $html) === false) {
            throw new Exception("Failed to save report to file: $filename");
        }
    }
}
