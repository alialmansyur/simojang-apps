<?php

namespace App\Libraries;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelUploader
{
    protected $uploadPath;

    public function __construct()
    {
        $this->uploadPath = ROOTPATH . 'public/uploads/excel/';
    }

    public function validateFile($file)
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('File tidak valid atau tidak terunggah.');
        }

        $allowedMimes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Tipe file tidak didukung.');
        }

        $allowedExtensions = ['xls', 'xlsx'];
        if (!in_array($file->getExtension(), $allowedExtensions)) {
            throw new \Exception('Ekstensi file tidak diizinkan.');
        }
    }

    public function parseExcel($filePath)
    { 
        // $newName = $file->getRandomName();
        // $file->move($this->uploadPath, $newName);
        // $filePath = $this->uploadPath . $newName;
        $spreadsheet = IOFactory::load($filePath);
        // $rows = $spreadsheet->getActiveSheet()->toArray();
        $rows  = $spreadsheet->getSheet(0)->toArray();
        // $highestRow = $sheet->getHighestRow();
        // $rows = $sheet->rangeToArray(
        //     'A:BG' . $highestRow,
        //     null,  // nilai default untuk sel kosong
        //     true,  // calculate formulas
        //     true,  // format data
        //     false  // jangan pakai array key as Excel (A,B,C), tetap pakai 0-based index
        // );

        // unlink($filePath);
        return $rows;
    }

    public static function cleanNumber($value)
    {
        return (int) str_replace([',', '.'], '', $value);
    }

    public static function excelDate($value, $format = 'Y-m-d')
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format($format);
        }

        $time = strtotime($value);
        return $time ? date($format, $time) : null;
    }

    public static function boolFromExcel($value)
    {
        return strtoupper(trim($value)) === 'TRUE' ? 1 : 0;
    }    
}
