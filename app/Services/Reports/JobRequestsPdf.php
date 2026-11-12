<?php

namespace App\Services\Reports;

use setasign\Fpdi\Tcpdf\Fpdi;

class JobRequestsPdf
{
    public function render(array $data): string
    {
        $pdf = new class('P', 'pt', [595.44, 841.68], true, 'UTF-8', false) extends Fpdi {
            public function __construct(...$args) { parent::__construct(...$args); $this->tcpdflink = false; }
        };
        $pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
        $pdf->setMargins(0, 0, 0); $pdf->setAutoPageBreak(false, 0);
        $pdf->SetCreator('PMMA Helpdesk'); $pdf->SetTitle('Job Requests Report');
        $pdf->setSourceFile(resource_path('reports/ar_blank.pdf'));
        $template = $pdf->importPage(1);
        $left = 108.02; $width = 453.94; $bottom = 796;
        $pdf->setCellPaddings(5, 5, 5, 5);
        $pdf->setCellHeightRatio(1.2);
        $page = 0;
        foreach ($data['reportServices']->chunk(3) as $part => $services) {
            $columns = array_merge(['Department / Unit'], $services->pluck('name')->all(), ['Subtotal']);
            $serviceWidth = ($width - 145 - 44) / max(1, $services->count());
            $widths = array_merge([145], array_fill(0, $services->count(), $serviceWidth), [44]);
            $startPage = function () use ($pdf, $template, &$page, $part, $data, $left, $width, $columns, $widths) {
                $pdf->AddPage('P', [595.44, 841.68]);
                $pdf->useImportedPage($template, 0, 0, 595.44, 841.68);
                $pdf->SetTextColor(16, 38, 75); $pdf->SetFont('helvetica', 'B', 14);
                $pdf->MultiCell($width, 20, 'JOB REQUESTS REPORT', 0, 'C', false, 1, $left, 108);
                $pdf->SetFont('helvetica', '', 8);
                $scope = implode(' | ', $data['scope']);
                $height = max(28, $pdf->getStringHeight($width, $scope));
                $pdf->MultiCell($width, $height, $scope, 0, 'L', false, 1, $left, 134);
                $pdf->MultiCell($width, 18, 'Total requests: '.$data['total'].' | Service group '.($part + 1).' | Report page '.(++$page), 0, 'L', false, 1, $left, 134 + $height);
                $y = 156 + $height;
                $pdf->SetFont('helvetica', 'B', 8);
                $h = 32;
                foreach ($columns as $i => $label) $h = max($h, $pdf->getStringHeight($widths[$i], $label));
                $this->row($pdf, $left, $y, $widths, $columns, $h, true, false);
                return $y + $h;
            };
            $y = $startPage();
            foreach ($data['rows'] as $index => $row) {
                $values = array_merge([$row['name']], $services->map(fn ($service) => (string) $row['counts'][$service->id])->all(),
                    [(string) $services->sum(fn ($service) => $row['counts'][$service->id])]);
                $pdf->SetFont('helvetica', '', 9);
                $h = max(28, $pdf->getStringHeight(145, $row['name']));
                if ($y + $h > $bottom) $y = $startPage();
                $this->row($pdf, $left, $y, $widths, $values, $h, false, $index % 2 === 0); $y += $h;
            }
            if ($y + 30 > $bottom) $y = $startPage();
            $values = array_merge(['TOTAL'], $services->map(fn ($service) => (string) $data['columnTotals'][$service->id])->all(),
                [(string) $services->sum(fn ($service) => $data['columnTotals'][$service->id])]);
            $this->row($pdf, $left, $y, $widths, $values, 30, true, false);
        }
        if ($page === 0) {
            $pdf->AddPage(); $pdf->useImportedPage($template, 0, 0, 595.44, 841.68);
            $pdf->SetFont('helvetica', '', 12); $pdf->Text($left, 120, 'No services are available for this report.');
        }
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
