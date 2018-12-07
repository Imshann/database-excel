<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

class Nav extends Database 
{
    public $spreadsheet;
    public $tableName = 'nav';
    
    public function __construct()
    {
        $this->spreadsheet = IOFactory::load(parent::$excelPath . $this->tableName . '.xlsx');
    }
}