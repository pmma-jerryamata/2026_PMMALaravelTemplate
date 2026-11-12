<?php

namespace App\Services\Reports;

use setasign\Fpdi\Tcpdf\Fpdi;

class JobRequestsPdf
{
    public function render(array $data): string
    {
        $pdf = new class('L', 'pt', [841.68, 595.44], true, 'UTF-8', false) extends Fpdi {
            public function __construct(...$args) { parent::__construct(...$args); $this->tcpdflink = false; }
        };
        $pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
        $pdf->setMargins(0, 0, 0); $pdf->setAutoPageBreak(false, 0);
        $pdf->SetCreator('PMMA Helpdesk'); $pdf->SetTitle($data['reportTitle']);
        $pdf->setSourceFile(resource_path('reports/ar_blank.pdf'));
        $template = $pdf->importPage(1);
        $left = 78; $width = 739.68; $bottom = 567;
        $pdf->setCellPaddings(4, 4, 4, 4); $pdf->setCellHeightRatio(1.15);
        $services = $data['reportServices'];
        $columns = array_merge(['Department / Unit'], $services->pluck('name')->all(), ['Total']);
        $serviceWidth = ($width - 150 - 44) / max(1, $services->count());
        $widths = array_merge([150], array_fill(0, $services->count(), $serviceWidth), [44]);
        $page = 0;
        $startPage = function () use ($pdf, $template, &$page, $data, $left, $width, $columns, $widths) {
            $pdf->AddPage('L', [841.68, 595.44]);
            // Fit the original page to the landscape sheet's height, preserving its aspect ratio.
            // The official header/sidebar are imported as artwork, never rebuilt or covered.
            $pdf->useImportedPage($template, 0, 0, 595.44 * 595.44 / 841.68, 595.44);
            $pdf->SetTextColor(16, 38, 75); $pdf->SetFont('helvetica', 'B', 13);
            $titleHeight = max(22, $pdf->getStringHeight($width, $data['reportTitle']));
            $pdf->MultiCell($width, $titleHeight, $data['reportTitle'], 0, 'C', false, 1, $left, 82);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell($width, 20, '('.$data['period'].')', 0, 'C', false, 1, $left, 82 + $titleHeight);
            $pdf->SetFont('helvetica', '', 9);
            $introHeight = max(24, $pdf->getStringHeight($width, $data['introduction']));
            if ($introHeight > 145) throw \Illuminate\Validation\ValidationException::withMessages(['introduction' => 'Please shorten the introduction to leave room for the report table.']);
            $introY = 106 + $titleHeight;
            $pdf->MultiCell($width, $introHeight, $data['introduction'], 0, 'L', false, 1, $left, $introY);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($width, 18, 'Total requests: '.$data['total'].' | Report page '.(++$page), 0, 'L', false, 1, $left, $introY + $introHeight + 3);
            $y = $introY + $introHeight + 24;
            $pdf->SetFont('helvetica', 'B', 8);
            $h = 32;
            foreach ($columns as $i => $label) $h = max($h, $pdf->getStringHeight($widths[$i], $label));
            $this->row($pdf, $left, $y, $widths, $columns, $h, true, false);
            return $y + $h;
        };
        $y = $startPage();
        foreach ($data['rows'] as $index => $row) {
            $values = array_merge([$row['name']], $services->map(fn ($service) => (string) $row['counts'][$service->id])->all(), [(string) $row['total']]);
            $pdf->SetFont('helvetica', '', 9);
            $h = max(25, $pdf->getStringHeight(150, $row['name']));
            if ($y + $h > $bottom) $y = $startPage();
            $this->row($pdf, $left, $y, $widths, $values, $h, false, $index % 2 === 0); $y += $h;
        }
        if ($y + 28 > $bottom) $y = $startPage();
        $values = array_merge(['TOTAL'], $services->map(fn ($service) => (string) $data['columnTotals'][$service->id])->all(), [(string) $data['total']]);
        $this->row($pdf, $left, $y, $widths, $values, 28, true, false);
        return $pdf->Output('job-requests-report.pdf', 'S');
    }

    private function row(Fpdi $pdf, float $x, float $y, array $widths, array $values, float $height, bool $header, bool $striped): void
    {
        $pdf->SetDrawColor(190, 203, 222); $pdf->SetLineWidth(.4);
        $pdf->SetFont('helvetica', $header ? 'B' : '', $header ? 8 : 9);
        $pdf->SetTextColor(16, 38, 75);
        $pdf->SetFillColor(...($header ? [213, 228, 250] : ($striped ? [244, 246, 249] : [255, 255, 255])));
        foreach ($values as $i => $value) {
            $pdf->MultiCell($widths[$i], $height, $value, 1, $i === 0 ? 'L' : 'C', true, 0, $x, $y, true, 0, false, true, $height, 'M');
            $x += $widths[$i];
        }
    }
}
