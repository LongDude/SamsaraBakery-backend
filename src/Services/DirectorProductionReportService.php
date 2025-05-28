<?php
namespace App\Services;

use Fawno\FPDF\FawnoFPDF;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class DirectorProductionReportService{
    public static function generateDetailedPdf(array $data): Response
    {
        function toWin1251(?string $text): ?string {
            if ($text === null){
                return null;
            }
            return iconv('UTF-8', 'windows-1251//IGNORE', $text);
        }

        define('FPDF_FONTPATH','../../public/fonts');
        $pdf = new FawnoFPDF();
        $pdf->AddPage('L');
        $fontname = 'Iosevka';
        
        $pdf->AddFont($fontname, '', 'IosevkaNerdFont_Regular.php', '/var/www/html/public/fonts/unifont');
        $pdf->AddFont($fontname, 'B', 'IosevkaNerdFont-Bold.php', '/var/www/html/public/fonts/unifont');

        $pdf->SetFont($fontname, 'B', 12);
        $pdf->Cell(50, 10, toWin1251('Партнер'), 1);
        $pdf->Cell(50, 10, toWin1251('Продукт'), 1);
        $pdf->Cell(30, 10, toWin1251('Цена'), 1);
        $pdf->Cell(25, 10, toWin1251('Количество'), 1);
        $pdf->Cell(40, 10, toWin1251('Статус'), 1);
        $pdf->Cell(35, 10, toWin1251('Дата'), 1);
        $pdf->Ln();

        $pdf->SetFont($fontname, '', 12);
        foreach ($data as $row) {
            $pdf->Cell(50, 10, toWin1251($row['partner_firmname']), 1);
            $pdf->Cell(50, 10, toWin1251($row['product']), 1);
            $pdf->Cell(30, 10, toWin1251((string)$row['price']), 1);
            $pdf->Cell(25, 10, toWin1251((string)$row['quantity']), 1);
            $pdf->Cell(40, 10, toWin1251($row['status']), 1);
            $pdf->Cell(35, 10, toWin1251((string)(is_a($row['date'], '\DateTimeInterface') ? $row['date']->format('Y-m-d') : $row['date'])), 1);
            $pdf->Ln();
        }
        $pdfContent = $pdf->Output('S', 'orders_report.pdf');

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders_report.pdf"');
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        return $response;
    }

    public static function generateDetailedExcel(array $data): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $cells = ['A', 'B', 'C', 'D', 'E', 'F'];
        $sheet = $spreadsheet->getActiveSheet();

        $i = 0;
        $sheet->setCellValue($cells[$i++] . '1', 'Партнер');
        $sheet->setCellValue($cells[$i++] . '1', 'Продукт');
        $sheet->setCellValue($cells[$i++] . '1', 'Цена');
        $sheet->setCellValue($cells[$i++] . '1', 'Количество');
        $sheet->setCellValue($cells[$i++] . '1', 'Статус');
        $sheet->setCellValue($cells[$i++] . '1', 'Дата');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['partner_firmname']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['product']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['price']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['quantity']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['status']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, is_a($row['date'], '\DateTimeInterface') ? $row['date']->format('Y-m-d') : $row['date']);
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        $response = new BinaryFileResponse($tempFile);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders_report.xlsx"');
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $response->deleteFileAfterSend(true);
        return $response;  
    }
}