<?php


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

for ($i=0; $i < count($parser->data); $i++) { 

    $sheet->setCellValue('A' . $i, $parser->data[$i]['title']);
    

    // var_dump($parser->data[$i]['title']);

    
}




$writer = new Xlsx($spreadsheet);
$writer->save('Parse.xlsx');