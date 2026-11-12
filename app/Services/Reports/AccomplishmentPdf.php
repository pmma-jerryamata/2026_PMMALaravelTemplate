<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use setasign\Fpdi\Tcpdf\Fpdi;

/** Draw body text over the unchanged official AR page. All coordinates are PDF points. */
class AccomplishmentPdf
{
    public const PAGE_WIDTH = 595.44;
    public const PAGE_HEIGHT = 841.68;
    public const LEFT = 108.02;
    public const DIVIDER = 256.85;
    public const RIGHT = 561.96;
    public const TABLE_TOP = 192.62;
    public const CONTINUATION_TABLE_TOP = 108.5;
    public const HEADER_HEIGHT = 14.64;
    public const LETTERHEAD_TOP_MARGIN = 33.48;
    public const TABLE_BOTTOM = self::PAGE_HEIGHT - self::LETTERHEAD_TOP_MARGIN;
    private float $tableTop = self::TABLE_TOP;
    public const LINE_HEIGHT = 13.44;

    private Fpdi $pdf;

    public function render(array $groups, string $from, string $to, array $signatories, string $contentHtml = ''): string
    {
        $this->pdf = new class extends Fpdi {
            public function __construct()
            {
                parent::__construct('P', 'pt', [AccomplishmentPdf::PAGE_WIDTH, AccomplishmentPdf::PAGE_HEIGHT], true, 'UTF-8', false);
                // Preserve the official document; do not append the library's optional visible branding line.
                $this->tcpdflink = false;
            }
        };
        $pdf = $this->pdf;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setMargins(0, 0, 0);
        $pdf->setAutoPageBreak(false, 0);
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->setCellMargins(0, 0, 0, 0);
        $pdf->setFontSubsetting(true);
        $pdf->SetCreator('PMMA Helpdesk');
        $pdf->SetTitle('Accomplishment Report');
        $pdf->SetAuthor($signatories['prepared_by'] ?? '');
        foreach (['' => 'cambria', 'B' => 'cambriab', 'I' => 'cambriai', 'BI' => 'cambriaz'] as $style => $file) {
            $pdf->AddFont('cambria', $style, resource_path('reports/fonts/'.$file.'.php'));
        }
        $pdf->AddFont('arial', '', resource_path('reports/fonts/arial.php'));
        $pdf->setSourceFile(resource_path('reports/ar_blank.pdf'));
        $template = $pdf->importPage(1);
        $signatureLines = $this->signatureLines($signatories);
        $extraSignatureLines = array_sum(array_map('count', $signatureLines)) - 4;
        $tableBottom = self::TABLE_BOTTOM;
        $contentHtml = ReportContent::clean($contentHtml);
        $this->tableTop = self::TABLE_TOP;
        if ($contentHtml !== '') {
            $pdf->AddPage();
            $this->tableTop = $this->customContent($contentHtml);
            $pdf->deletePage(1);
            if ($this->tableTop > 500) {
                throw \Illuminate\Validation\ValidationException::withMessages(['content_html' => 'Keep the report introduction short enough to leave room for the accomplishment table.']);
            }
        }
        $firstPageTableTop = $this->tableTop;
        $rows = $this->rows($groups);
        $pages = [[]];
        $y = $this->tableTop + self::HEADER_HEIGHT;
        foreach ($rows as $row) {
            $remaining = $row['lines'];
            $continued = false;
            while ($remaining) {
                $available = $tableBottom - $y;
                $fit = (int) floor(($available - 0.42) / self::LINE_HEIGHT);
                if ($fit < 1 || ($fit < 2 && count($remaining) > 1)) {
                    $pages[] = []; $y = self::CONTINUATION_TABLE_TOP + self::HEADER_HEIGHT; continue;
                }
                $part = array_slice($remaining, 0, $fit);
                $firstRow = count($pages) === 1 && ! $pages[0];
                $height = max($continued ? 28.8 : ($firstRow ? 21.60 : 18.96), count($part) * self::LINE_HEIGHT + 0.42);
                if ($height > $available) {
                    $pages[] = []; $y = self::CONTINUATION_TABLE_TOP + self::HEADER_HEIGHT; continue;
                }
                $pages[count($pages) - 1][] = ['date' => $row['date'], 'continued' => $continued,
                    'lines' => $part, 'height' => $height];
                $y += $height;
                $remaining = array_slice($remaining, count($part));
                $continued = true;
            }
        }
        $signatureHeight = 183.5 + max(0, $extraSignatureLines) * 14.04;
        $signaturePage = $y + $signatureHeight > $tableBottom;
        if ($signaturePage) { $pages[] = []; }
        $period = $this->period($from, $to);
        foreach ($pages as $index => $pageRows) {
            $this->tableTop = $index === 0 ? $firstPageTableTop : self::CONTINUATION_TABLE_TOP;
            $pdf->AddPage('P', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
            // Reuse the original PDF at its native dimensions. Never rebuild or scale its letterhead.
            $pdf->useImportedPage($template, 0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT);
            $pdf->SetTextColor(0, 0, 0);
            if ($index === 0 && $contentHtml !== '') { $this->customContent($contentHtml); }
            elseif ($index === 0) {
                $this->centered('ACCOMPLISHMENT REPORT', 119.42, 'B');
                $this->centered('('.$period.')', 133.46);
                $this->text(self::LEFT, 161.66, 'Submitted herewith is the accomplishment report of the undersigned for the period');
                $this->text(self::LEFT, 175.70, 'as of '.$period.'.');
            }
            $y = $this->tableTop;
            if ($pageRows) { $this->tableHeader(); $y += self::HEADER_HEIGHT; }
            foreach ($pageRows as $row) {
                $this->tableRow($row, $y);
                $y += $row['height'];
            }
            if ($index === count($pages) - 1) { $this->signatures($y, $signatureLines); }
            if (count($pages) > 1) {
                $pdf->SetFont('cambria', '', 8);
                $pdf->Text(self::RIGHT - 90, 814, 'Report page '.($index + 1).' of '.count($pages));
            }
        }
        return $pdf->Output('accomplishment-report.pdf', 'S');
    }

    private function customContent(string $html): float
    {
        $this->pdf->SetFont('cambria', '', 12);
        $this->pdf->setCellHeightRatio(1.17);
        $this->pdf->setHtmlVSpace(['p' => [0 => ['h' => 0, 'n' => 0], 1 => ['h' => 7.02, 'n' => 1]]]);
        $this->pdf->writeHTMLCell(self::RIGHT - self::LEFT, 0, self::LEFT, 108.5, $html, 0, 1, false, true, 'L', false);
        return max(self::TABLE_TOP, $this->pdf->GetY() + 10);
    }

    private function period(string $from, string $to): string
    {
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        if ($start->isSameDay($end)) { return $start->format('F j, Y'); }
        if ($start->format('Y-m') === $end->format('Y-m')) { return $start->format('F j').'-'.$end->format('j, Y'); }
        return $start->format('F j, Y').' - '.$end->format('F j, Y');
    }

    /** Wrap without reducing font sizes, including unbroken words and very long ticket findings. */
    private function wrap(string $text, float $width, string $font, float $size, string $style = ''): array
    {
        $this->pdf->SetFont($font, $style, $size);
        $lines = [];
        foreach (preg_split('/\R/u', trim($text)) as $paragraph) {
            $line = '';
            foreach (preg_split('/\s+/u', $paragraph, -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $candidate = $line === '' ? $word : $line.' '.$word;
                if ($this->pdf->GetStringWidth($candidate) <= $width) { $line = $candidate; continue; }
                if ($line !== '') { $lines[] = $line; $line = ''; }
                foreach (preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) as $character) {
                    if ($line !== '' && $this->pdf->GetStringWidth($line.$character) > $width) { $lines[] = $line; $line = ''; }
                    $line .= $character;
                }
            }
            $lines[] = $line;
        }
        return $lines ?: [''];
    }

    private function rows(array $groups): array
    {
        $rows = [];
        foreach ($groups as $group) {
            $lines = [];
            foreach ($group['items'] as $item) {
                foreach ($this->wrap($item, self::RIGHT - 298.27 - 5.64, 'arial', 11.04) as $index => $line) {
                    $lines[] = ['text' => $line, 'bullet' => $index === 0];
                }
            }
            $rows[] = ['date' => $group['date'],
                'lines' => $lines ?: [['text' => 'No completed requests recorded.', 'bullet' => false]]];
        }
        if (! $rows) {
            $rows[] = ['date' => '', 'lines' => [['text' => 'No completed requests in the selected period.', 'bullet' => false]]];
        }
        return $rows;
    }

    private function text(float $x, float $baseline, string $value, string $style = '', float $size = 12, string $font = 'cambria'): void
    {
        $this->pdf->SetFont($font, $style, $size);
        // L/T uses an explicit font baseline, matching the measured Word-generated sample.
        $this->pdf->Text($x, $baseline, $value, 0, false, true, 0, 0, '', false, '', 0, true, 'L', 'T');
    }

    private function centered(string $value, float $baseline, string $style = ''): void
    {
        $this->pdf->SetFont('cambria', $style, 12);
        $this->text(self::LEFT + (self::RIGHT - self::LEFT - $this->pdf->GetStringWidth($value)) / 2, $baseline, $value, $style);
    }

    private function border(float $top, float $height): void
    {
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetLineWidth(0.48);
        $this->pdf->Rect(self::LEFT + 0.24, $top, self::RIGHT - self::LEFT - 0.48, $height);
        $this->pdf->Line(self::DIVIDER, $top, self::DIVIDER, $top + $height);
    }

    private function tableHeader(): void
    {
        $this->border($this->tableTop, self::HEADER_HEIGHT);
        $this->pdf->SetFont('cambria', 'B', 12);
        foreach ([[self::LEFT, self::DIVIDER, 'DATE'], [self::DIVIDER, self::RIGHT, 'ACCOMPLISHMENTS']] as [$left, $right, $label]) {
            $this->text($left + ($right - $left - $this->pdf->GetStringWidth($label)) / 2, $this->tableTop + 11.76, $label, 'B');
        }
    }

    private function tableRow(array $row, float $top): void
    {
        $this->border($top, $row['height']);
        $this->text(113.66, $top + 11.64, $row['date']);
        if ($row['continued']) { $this->text(113.66, $top + 25.08, '(continued)'); }
        foreach ($row['lines'] as $index => $line) {
            $baseline = $top + 11.16 + $index * self::LINE_HEIGHT;
            if ($line['bullet']) {
                $this->pdf->SetFillColor(0, 0, 0);
                $this->pdf->Circle(282.80, $baseline - 3.0, 1.45, 0, 360, 'F');
            }
            $this->text(298.27, $baseline, $line['text'], '', 11.04, 'arial');
        }
    }

    private function signatureLines(array $values): array
    {
        $lines = [];
        foreach (['prepared_by', 'prepared_position', 'noted_by', 'noted_position'] as $key) {
            $lines[$key] = $this->wrap($values[$key] ?? '', self::RIGHT - self::LEFT, 'cambria', 12,
                in_array($key, ['prepared_by', 'noted_by'], true) ? 'B' : '');
        }
        return $lines;
    }

    private function signatureBlock(string $key, float $baseline, array $lines): float
    {
        foreach ($lines as $index => $line) {
            $this->text(self::LEFT, $baseline + $index * 14.04, $line,
                in_array($key, ['prepared_by', 'noted_by'], true) ? 'B' : '');
        }
        return $baseline + (count($lines) - 1) * 14.04;
    }

    private function signatures(float $tableEnd, array $values): void
    {
        $prepared = $tableEnd + 39.64;
        $this->text(self::LEFT, $prepared, 'Prepared by:');
        $baseline = $this->signatureBlock('prepared_by', $prepared + 28.20, $values['prepared_by']);
        $baseline = $this->signatureBlock('prepared_position', $baseline + 14.04, $values['prepared_position']);
        $baseline += 42.12;
        $this->text(self::LEFT, $baseline, 'Noted by:');
        $baseline = $this->signatureBlock('noted_by', $baseline + 42.30, $values['noted_by']);
        $this->signatureBlock('noted_position', $baseline + 14.04, $values['noted_position']);
    }

}
