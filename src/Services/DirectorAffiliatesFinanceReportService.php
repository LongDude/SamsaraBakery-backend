<?php
namespace App\Services;

use App\Core\FixedFPDF;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class DirectorAffiliatesFinanceReportService{

   
    public static function generatePdf(array $data): Response
    {
        define('FPDF_FONTPATH','../../public/fonts');
        $pdf = new FixedFPDF();
        $pdf->AddPage('L');
        $fontname = 'Iosevka';
        $pdf->AddFont($fontname, '', 'IosevkaNerdFont_Regular.php', '/var/www/html/public/fonts/unifont');
        $pdf->AddFont($fontname, 'B', 'IosevkaNerdFont-Bold.php', '/var/www/html/public/fonts/unifont');

        $headers = [
            'Адрес филиала', 'Телефон', 'Менеджер', 'Тел. менеджера', 'Дата', 'Выручка', 'Траты', 'Чистая выручка'
        ];
        $fields = [
            'affiliate_address', 'contact_number', 'manager_name', 'manager_phone', 'day', 'revenue', 'cost', 'net_revenue'
        ];

        $valueFormatter = function($value, $field, $row) {
            if ($field === 'day' && $value instanceof \DateTimeInterface) {
                return $value->format('d-m-Y');
            }
            return $value;
        };

        FixedFPDF::printTable($pdf, $fontname, $headers, $fields, $data, 12, 12, 6, $valueFormatter);

        $pdfContent = $pdf->Output('S', 'affiliate_finance_report.pdf');
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="affiliate_finance_report.pdf"');
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        return $response;
    }

    public static function generateExcel(array $data): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $cells = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $sheet = $spreadsheet->getActiveSheet();

        $i = 0;
        $sheet->setCellValue($cells[$i++] . '1', 'Адрес филиала');
        $sheet->setCellValue($cells[$i++] . '1', 'Телефон');
        $sheet->setCellValue($cells[$i++] . '1', 'Менеджер');
        $sheet->setCellValue($cells[$i++] . '1', 'Тел. менеджера');
        $sheet->setCellValue($cells[$i++] . '1', 'Дата');
        $sheet->setCellValue($cells[$i++] . '1', 'Выручка');
        $sheet->setCellValue($cells[$i++] . '1', 'Траты');
        $sheet->setCellValue($cells[$i++] . '1', 'Чистая выручка');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['affiliate_address']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['contact_number']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['manager_name']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['manager_phone']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, is_a($row['day'], '\\DateTimeInterface') ? $row['day']->format('Y-m-d') : $row['day']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['revenue']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['cost']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['net_revenue']);
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        $response = new BinaryFileResponse($tempFile);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename=\"affiliate_finance_report.xlsx\"');
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $response->deleteFileAfterSend(true);
        return $response;  
    }
}