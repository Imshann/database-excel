<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use Douzhi\Database;

class Nav extends Database 
{
    public $spreadsheet;
    public $tableName = 'nav';
    
    public function __construct()
    {
        $this->spreadsheet = IOFactory::load($this->getExcelPath());
    }
    
    public function attributes()
    {
        return [
            'id',
            'nav_name',
        ];
    }
}