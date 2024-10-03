<?php
class PDFGenerator {
    private $data;
    private $title;
    private $reportType;

    public function __construct($data, $title, $reportType) {
        $this->data = $data;
        $this->title = $title;
        $this->reportType = $reportType;
    }

    public function generatePDF($filename) {
        $pdf = $this->createPDF();
        $pdf .= $this->addHeader();
        $pdf .= $this->addContent();
        $pdf .= $this->addFooter();

        file_put_contents($filename, $pdf);
    }

    private function createPDF() {
        $pdf = "%PDF-1.7\n";
        $pdf .= "1 0 obj\n<</Type /Catalog /Pages 2 0 R>>\nendobj\n";
        $pdf .= "2 0 obj\n<</Type /Pages /Kids [3 0 R] /Count 1>>\nendobj\n";
        $pdf .= "3 0 obj\n<</Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 6 0 R>>\nendobj\n";
        $pdf .= "4 0 obj\n<</Font <</F1 5 0 R>>>>\nendobj\n";
        $pdf .= "5 0 obj\n<</Type /Font /Subtype /Type1 /BaseFont /Helvetica>>\nendobj\n";
        return $pdf;
    }

    private function addHeader() {
        $header = "6 0 obj\n<< /Length 1000 >>\nstream\nBT\n/F1 16 Tf\n50 700 Td\n({$this->title}) Tj\nET\n";
        return $header;
    }

    private function addContent() {
        $content = "";
        $y = 670;

        foreach ($this->data as $index => $row) {
            if ($index === 0) {
                $content .= $this->addTableHeader($row, $y);
                $y -= 20;
            }
            $content .= $this->addTableRow($row, $y);
            $y -= 20;

            if ($y < 50) {
                $content .= "endstream\nendobj\n";
                $content .= $this->createNewPage();
                $y = 750;
            }
        }

        return $content;
    }

    private function addTableHeader($row, $y) {
        $header = "BT\n/F1 12 Tf\n";
        $x = 50;
        foreach ($row as $key => $value) {
            $header .= "{$x} {$y} Td\n({$key}) Tj\n";
            $x += 100;
        }
        $header .= "ET\n";
        return $header;
    }

    private function addTableRow($row, $y) {
        $rowContent = "BT\n/F1 10 Tf\n";
        $x = 50;
        foreach ($row as $value) {
            $rowContent .= "{$x} {$y} Td\n({$value}) Tj\n";
            $x += 100;
        }
        $rowContent .= "ET\n";
        return $rowContent;
    }

    private function createNewPage() {
        $newPage = "7 0 obj\n<</Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 8 0 R>>\nendobj\n";
        $newPage .= "8 0 obj\n<< /Length 1000 >>\nstream\nBT\n/F1 16 Tf\n50 750 Td\n({$this->title} - Continued) Tj\nET\n";
        return $newPage;
    }

    private function addFooter() {
        $footer = "ET\nendstream\nendobj\nxref\n0 9\n0000000000 65535 f \n0000000010 00000 n \n0000000056 00000 n \n0000000111 00000 n \n0000000212 00000 n \n0000000250 00000 n \n0000000310 00000 n \ntrailer\n<</Size 9/Root 1 0 R>>\nstartxref\n1000\n%%EOF";
        return $footer;
    }
}
