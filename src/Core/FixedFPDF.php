<?php

namespace App\Core;
use Fawno\FPDF\FawnoFPDF;

class FixedFPDF extends FawnoFPDF
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function toWin1251(?string $text): ?string {
        if ($text === null){
            return null;
        }
        return iconv('UTF-8', 'windows-1251//IGNORE', $text);
    }

    /**
     * Универсальный вывод таблицы с автошириной и переносом текста для FPDF/FawnoFPDF.
     *
     * @param FawnoFPDF $pdf
     * @param string $fontname
     * @param array $headers  Массив заголовков колонок (строки)
     * @param array $fields   Массив ключей для данных (строки)
     * @param array $data     Массив данных (каждая строка — ассоциативный массив)
     * @param int $headerFontSize
     * @param int $cellFontSize
     * @param int $cellHeight
     * @param callable|null $valueFormatter  (опционально) функция для форматирования значения: function($value, $field, $row) { ... }
     */
    public static function printTable($pdf, $fontname, array $headers, array $fields, array $data, int $headerFontSize = 12, int $cellFontSize = 12, int $cellHeight = 6, callable $valueFormatter = null)
    {
        // 1. Считаем максимальную ширину для каждой колонки
        $colWidths = [];
        $pdf->SetFont($fontname, 'B', $headerFontSize);
        foreach ($headers as $i => $header) {
            $colWidths[$i] = $pdf->GetStringWidth(self::toWin1251($header));
        }
        $pdf->SetFont($fontname, '', $cellFontSize);
        foreach ($data as $row) {
            foreach ($fields as $i => $field) {
                $value = $row[$field] ?? '';
                if ($valueFormatter) {
                    $value = $valueFormatter($value, $field, $row);
                }
                $w = $pdf->GetStringWidth(self::toWin1251((string)$value));
                if ($w > $colWidths[$i]) {
                    $colWidths[$i] = $w;
                }
            }
        }
        foreach ($colWidths as &$w) {
            $w += 8; // padding
        }
        unset($w);

        $pageWidth = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $totalWidth = array_sum($colWidths);

        if ($totalWidth > $pageWidth) {
            $scale = $pageWidth / $totalWidth;
            foreach ($colWidths as &$w) {
                $w = $w * $scale;
            }
            unset($w);
        }

        // 2. Выводим заголовки
        $pdf->SetFont($fontname, 'B', $headerFontSize);
        foreach ($headers as $i => $header) {
            $pdf->Cell($colWidths[$i], $cellHeight+4, self::toWin1251($header), 1, 0, 'C');
        }
        $pdf->Ln();

        // 3. Выводим строки с переносом (MultiCell)
        $pdf->SetFont($fontname, '', $cellFontSize);
        foreach ($data as $row) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $cellHeights = [];
            $cellTexts = [];
            // 1. Считаем высоту каждой ячейки (сколько строк займет)
            foreach ($fields as $i => $field) {
                $value = $row[$field] ?? '';
                if ($valueFormatter) {
                    $value = $valueFormatter($value, $field, $row);
                }
                $txt = self::toWin1251((string)$value);
                $lines = $pdf->NbLines($colWidths[$i], $txt);
                $cellHeights[$i] = $lines * $cellHeight;
                $cellTexts[$i] = $txt;
            }
            $rowHeight = max($cellHeights);

            // 2. Выводим ячейки с только боковыми границами
            for ($i = 0; $i < count($fields); $i++) {
                $curX = $pdf->GetX();
                $curY = $pdf->GetY();
                $pdf->MultiCell($colWidths[$i], $cellHeight, $cellTexts[$i], 'LR', 'L', false);
                $cellH = $cellHeights[$i];
                if ($cellH < $rowHeight) {
                    $pdf->SetXY($curX, $curY + $cellH);
                    $pdf->Cell($colWidths[$i], $rowHeight - $cellH, '', 'LR', 0, 'L', false);
                }
                $pdf->SetXY($curX + $colWidths[$i], $curY);
            }
            // 3. После строки рисуем нижнюю границу по всей ширине ряда
            $pdf->SetXY($x, $y + $rowHeight);
            $pdf->Cell(array_sum($colWidths), 0, '', 'T');

            // Сброс X на левый отступ страницы
            $pdf->SetX($pdf->lMargin);
        }
    }

    public function NbLines(float $w, string $txt): int {
        $cw = $this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',(string)$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c] ?? 0;
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}