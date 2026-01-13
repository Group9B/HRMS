<?php
/**
 * PayslipPDF - Generates professionally formatted PDF payslips using FPDF
 */

require_once __DIR__ . '/../fpdf/fpdf.php';

class PayslipPDF extends FPDF
{
    private $payslipData = [];

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(true, 15);
    }

    public function generateFromHTML($html, $filename = 'payslip.pdf', $options = [])
    {
        $outputDir = __DIR__ . '/../../uploads/payslips';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $uniqueFilename = uniqid('payslip_') . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
        if (!preg_match('/\.pdf$/i', $uniqueFilename)) {
            $uniqueFilename .= '.pdf';
        }
        $outputPath = $outputDir . '/' . $uniqueFilename;

        // Store data
        $this->payslipData = [
            'company_name' => $options['company_name'] ?? 'Company',
            'title' => $options['title'] ?? 'Payslip',
            'employee_name' => $options['employee_name'] ?? 'N/A',
            'employee_code' => $options['employee_code'] ?? 'N/A',
            'department' => $options['department_name'] ?? 'N/A',
            'designation' => $options['designation_name'] ?? 'N/A',
            'period' => $options['period'] ?? 'N/A',
            'currency' => $options['currency'] ?? 'INR',
            'gross_salary' => $options['gross_salary'] ?? 0,
            'net_salary' => $options['net_salary'] ?? 0,
            'earnings' => $options['earnings'] ?? [],
            'deductions' => $options['deductions'] ?? [],
        ];

        $this->createPayslip();
        $this->Output('F', $outputPath);

        if (file_exists($outputPath) && filesize($outputPath) > 0) {
            return $outputPath;
        }

        return false;
    }

    private function createPayslip()
    {
        $this->AddPage();
        $this->SetMargins(15, 15, 15);

        // === HEADER ===
        $this->SetFillColor(41, 128, 185);
        $this->Rect(0, 0, 210, 35, 'F');

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 18);
        $this->SetXY(15, 10);
        $this->Cell(180, 10, strtoupper($this->payslipData['company_name']), 0, 1, 'C');

        $this->SetFont('Helvetica', '', 11);
        $this->SetXY(15, 22);
        $this->Cell(180, 8, $this->payslipData['title'], 0, 1, 'C');

        // Reset text color
        $this->SetTextColor(0, 0, 0);
        $this->SetY(42);

        // === EMPLOYEE INFORMATION ===
        $this->sectionHeader('Employee Information');
        $this->Ln(3);

        $this->SetFont('Helvetica', '', 9);
        $col1 = 45;
        $col2 = 45;
        $startX = 15;

        // Row 1
        $this->SetX($startX);
        $this->SetTextColor(100, 100, 100);
        $this->Cell($col1, 6, 'Employee Name:', 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($col2, 6, $this->payslipData['employee_name'], 0, 0);

        $this->SetX(105);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell($col1, 6, 'Employee Code:', 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($col2, 6, $this->payslipData['employee_code'], 0, 1);

        // Row 2
        $this->SetX($startX);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell($col1, 6, 'Department:', 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($col2, 6, $this->payslipData['department'], 0, 0);

        $this->SetX(105);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell($col1, 6, 'Designation:', 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($col2, 6, $this->payslipData['designation'], 0, 1);

        // Row 3
        $this->SetX($startX);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell($col1, 6, 'Pay Period:', 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($col2, 6, $this->payslipData['period'], 0, 0);

        $this->SetX(105);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell($col1, 6, 'Currency:', 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 9);
        $this->Cell($col2, 6, $this->payslipData['currency'], 0, 1);

        $this->Ln(8);

        // === EARNINGS TABLE ===
        $this->sectionHeader('Earnings');
        $this->Ln(2);

        $this->tableHeader(['Description', 'Amount']);

        $totalEarnings = 0;
        if (!empty($this->payslipData['earnings'])) {
            foreach ($this->payslipData['earnings'] as $item) {
                $desc = is_array($item) ? ($item['description'] ?? $item['label'] ?? 'Item') : 'Item';
                $amt = is_array($item) ? ($item['amount'] ?? $item['value'] ?? 0) : 0;
                $amount = floatval(preg_replace('/[^0-9.]/', '', strval($amt)));
                $totalEarnings += $amount;
                $this->tableRow([$desc, $this->formatAmount($amount)]);
            }
        } else {
            $this->tableRow(['No earnings data', '-']);
        }

        // Total Earnings
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetFillColor(232, 245, 233);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(120, 7, 'Total Earnings', 1, 0, 'L', true);
        $this->Cell(60, 7, $this->formatAmount($totalEarnings), 1, 1, 'R', true);

        $this->Ln(8);

        // === DEDUCTIONS TABLE ===
        $this->sectionHeader('Deductions');
        $this->Ln(2);

        $this->tableHeader(['Description', 'Amount']);

        $totalDeductions = 0;
        if (!empty($this->payslipData['deductions'])) {
            foreach ($this->payslipData['deductions'] as $item) {
                $desc = is_array($item) ? ($item['description'] ?? $item['label'] ?? 'Item') : 'Item';
                $amt = is_array($item) ? ($item['amount'] ?? $item['value'] ?? 0) : 0;
                $amount = floatval(preg_replace('/[^0-9.]/', '', strval($amt)));
                $totalDeductions += $amount;
                $this->tableRow([$desc, $this->formatAmount($amount)]);
            }
        } else {
            $this->tableRow(['No deductions', '-']);
        }

        // Total Deductions
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetFillColor(255, 235, 238);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(120, 7, 'Total Deductions', 1, 0, 'L', true);
        $this->Cell(60, 7, $this->formatAmount($totalDeductions), 1, 1, 'R', true);

        $this->Ln(10);

        // === NET SALARY ===
        $netSalary = $totalEarnings - $totalDeductions;

        $this->SetFillColor(39, 174, 96);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 11);
        $this->Cell(120, 10, 'NET SALARY PAYABLE', 1, 0, 'L', true);
        $this->Cell(60, 10, 'Rs. ' . number_format($netSalary, 2), 1, 1, 'R', true);

        // Reset
        $this->SetTextColor(0, 0, 0);

        // === FOOTER ===
        $this->SetY(-30);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(3);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 5, 'This is a computer-generated payslip. No signature is required.', 0, 1, 'C');
        $this->Cell(0, 5, 'Generated on: ' . date('d M Y, h:i A'), 0, 1, 'C');
    }

    private function sectionHeader($title)
    {
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(52, 73, 94);
        $this->Cell(180, 7, '  ' . $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
    }

    private function tableHeader($headers)
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(52, 73, 94);
        $this->Cell(120, 7, '  ' . $headers[0], 1, 0, 'L', true);
        $this->Cell(60, 7, $headers[1] . '  ', 1, 1, 'R', true);
        $this->SetTextColor(0, 0, 0);
    }

    private function tableRow($values)
    {
        $this->SetFont('Helvetica', '', 9);
        $this->SetFillColor(250, 250, 250);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(120, 6, '  ' . $values[0], 1, 0, 'L', true);
        $this->Cell(60, 6, $values[1] . '  ', 1, 1, 'R', true);
    }

    private function formatAmount($amount)
    {
        return 'Rs. ' . number_format($amount, 2);
    }

    public function cleanupOldFiles($days = 7)
    {
        $outputDir = __DIR__ . '/../../uploads/payslips';
        if (!is_dir($outputDir))
            return;

        $threshold = time() - ($days * 24 * 60 * 60);
        $files = glob($outputDir . '/payslip_*');

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                @unlink($file);
            }
        }
    }
}
