<?php
namespace App\Services;

use App\Core\FixedFPDF;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class DirectorProductionReportService{
   
    public static function generatePdf(array $data, bool $summary=false): Response
    {
        define('FPDF_FONTPATH','../../public/fonts');
        $pdf = new FixedFPDF();
        $pdf->AddPage('L');
        $fontname = 'Iosevka';
        $pdf->AddFont($fontname, '', 'IosevkaNerdFont_Regular.php', '/var/www/html/public/fonts/unifont');
        $pdf->AddFont($fontname, 'B', 'IosevkaNerdFont-Bold.php', '/var/www/html/public/fonts/unifont');

        $headers = array_merge(['Продукт'], ($summary ? [] : ['Дата']), ['Выручка от продаж', 'Выручка от заказов', 'Расходы на производство', 'Произведено', 'Продано', 'Заказано', 'Индекс реализации', 'Индекс заказа', 'Чистая прибыль']);
        $fields = array_merge(['product_name'], ($summary ? [] : ['date']), ['sells_revenue', 'orders_revenue', 'production_cost', 'producted_count', 'sold_count', 'ordered_count', 'realisation_index', 'order_index', 'net_revenue']);

        $valueFormatter = function($value, $field, $row) {
            if ($field === 'date' && $value instanceof \DateTimeInterface) {
                return $value->format('d-m-Y');
            }
            return $value;
        };

        FixedFPDF::printTable($pdf, $fontname, $headers, $fields, $data, 10, 10, 6, $valueFormatter);

        $pdfContent = $pdf->Output('S', 'production_report.pdf');
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="production_report.pdf"');
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        return $response;
    }

    public static function generateExcel(array $data, bool $summary=false): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $cells = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $sheet = $spreadsheet->getActiveSheet();

        $i = 0;
        $sheet->setCellValue($cells[$i++] . '1', 'Продукт');
        if (!$summary) {
            $sheet->setCellValue($cells[$i++] . '1', 'Дата');
        }
        $sheet->setCellValue($cells[$i++] . '1', 'Выручка от продаж');
        $sheet->setCellValue($cells[$i++] . '1', 'Выручка от заказов');
        $sheet->setCellValue($cells[$i++] . '1', 'Расходы на производство');
        $sheet->setCellValue($cells[$i++] . '1', 'Произведено');
        $sheet->setCellValue($cells[$i++] . '1', 'Продано');
        $sheet->setCellValue($cells[$i++] . '1', 'Заказано');
        $sheet->setCellValue($cells[$i++] . '1', 'Индекс реализации');
        $sheet->setCellValue($cells[$i++] . '1', 'Индекс заказа');
        $sheet->setCellValue($cells[$i++] . '1', 'Чистая прибыль');

        $rowIndex = 2;
        foreach ($data as $row) {
            $i = 0;
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['product_name']);
            if (!$summary) {
                $sheet->setCellValue($cells[$i++] . $rowIndex, $row['date']);
            }
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['sells_revenue']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['orders_revenue']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['production_cost']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['producted_count']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['sold_count']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['ordered_count']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['realisation_index']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['order_index']);
            $sheet->setCellValue($cells[$i++] . $rowIndex, $row['net_revenue']);
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        $response = new BinaryFileResponse($tempFile);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename=\"production_report.xlsx\"');
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $response->deleteFileAfterSend(true);
        return $response;  
    }
}