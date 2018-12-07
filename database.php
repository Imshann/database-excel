<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

class Database
{
    protected $where;
    protected static $excelPath;

    public function __construct($excelPath)
    {
        self::$excelPath = $excelPath;
        $rootPath = dirname(__FILE__);
        $list = scandir(self::$excelPath);
        $list = array_filter($list, function ($var) {
            return stripos($var, '.xlsx') && !stripos($var, '$');
        });
        foreach ($list as $item) {
            $model_classname = ucfirst(str_replace('.xlsx', '', $item));
            $model_classpath = ucfirst(str_replace('.xlsx', '.php', $item));
            $tableName = strtolower($model_classname);

            if (!file_exists("{$rootPath}/model/{$model_classpath}")) {
                $class_template = <<<text
<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

class {$model_classname} extends Database 
{
    public \$spreadsheet;
    public \$tableName = '{$tableName}';
    
    public function __construct()
    {
        \$this->spreadsheet = IOFactory::load(parent::\$excelPath . \$this->tableName . '.xlsx');
    }
}
text;

                file_put_contents("{$rootPath}/model/{$model_classpath}", $class_template);
            }

            if (file_exists("{$rootPath}/model/{$model_classpath}") && !class_exists($model_classname)) {
                require "{$rootPath}/model/{$model_classpath}";
            }
        }
    }

    public function setExcelPath($path)
    {
        $this->excelPath = $path;
    }

    public static function find()
    {
        return new static();
    }

    public function all()
    {
        $worksheet = $this->spreadsheet->getActiveSheet();
        $records = [];
        $fields = [];
        foreach ($worksheet->getRowIterator() as $key => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(TRUE);
            if ($key === 1) {
                foreach ($cellIterator as $cell) {
                    $fields[] = $cell->getValue();
                }
                continue;
            } else {
                $record = [];
                $key = 0;
                foreach ($cellIterator as $cell) {
                    $record[$fields[$key]] = $cell->getValue();
                    $key++;
                }
                $records[$record['id']] = (object)$record;
            }

        }
        return $records;
    }

    public function one()
    {
        return $this->all()[$this->where['id']];
    }

    public function where($conditions)
    {
        $this->where = $conditions;
        return $this;
    }
}

