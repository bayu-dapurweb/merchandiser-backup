<?php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWritter;

class ExcelService
{
    public static function import($filePath)
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $row_index = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE);
            foreach ($cellIterator as $cell) {
                $data[$row_index][] = $cell->getValue();
            }
            $row_index++;
        }
        return $data;
    }    

    public static function export($module = 'export', Array $data, $return_url = false)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $i = 1;
        
        foreach ($data as $val) {
            $alpha_index = "a";
            foreach ($val as $v) {
                $sheet->setCellValue(strtoupper($alpha_index) . $i, $v);
                $alpha_index++;
            }
            $i++;
        }

        $writer = new XlsxWritter($spreadsheet);
        $path = __DIR__ . '/../../storage/app/uploads/excel-tmp/';
        $file_name =  $module . '-' .  date("YmdHis") . '.xlsx';
        $file_url = uri('uploads/excel-tmp/' . $file_name);
        $writer->save($path . $file_name);
        if ($return_url) {
            return $file_url;
        } else {
            return $path . $file_name;
        }
        
    
    }
}
?>
