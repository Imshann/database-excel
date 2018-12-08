<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Database
{
    protected $where;
    protected static $excelPath;

    /**
     * 数据表递增编号
     * @var int
     */
    protected $auto_increment = 0;

    public function __construct($excelPath)
    {
        static::$excelPath = $excelPath;
        $rootPath = dirname(__FILE__);
        $list = scandir(static::$excelPath);
        $list = array_filter($list, function ($var) {
            return stripos($var, '.xlsx') && !stripos($var, '$');
        });
        foreach ($list as $item) {
            $model_classname = ucfirst(str_replace('.xlsx', '', $item));
            $model_classpath = ucfirst(str_replace('.xlsx', '.php', $item));
            $tableName = strtolower($model_classname);

            $this->getFields($excelPath . $item);
            $attributes = $this->buildAttributes($this->getFields($excelPath . $item));
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
        \$this->spreadsheet = IOFactory::load(\$this->getExcelPath());
    }
    
$attributes
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

    private function getFields($filename)
    {
        $fields = [];
        $spreadsheet = IOFactory::load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(TRUE);
            foreach ($cellIterator as $cell) {
                $fields[] = $cell->getValue();
            }
            break;
        }
        return $fields;
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

    public static function findOne($id)
    {
        $model = new static();
        $record = $model->where([$model->getFields($model->getExcelPath())[0] => $id])
                        ->one();
        foreach ($record as $index => $item) {
            $model->$index = $item;
        }
        return $model;
    }

    public function where($conditions)
    {
        $this->where = $conditions;
        return $this;
    }

    public function insert()
    {
        $properties = get_object_vars($this);
        $records = $this->all();
        foreach ($records as $key => $record) {
            $records[$key] = (array)$record;
        }
        $id = $this->getAutoIncrement();
        $records[$id][$this->attributes()[0]] = $id;
        foreach ($properties as $attribute => $value) {
            if (!empty($value) && in_array($attribute, $this->attributes())) {
                $records[$id][$attribute] = $value;
            }
        }
        $worksheet = $this->spreadsheet->getActiveSheet();
        $worksheet->fromArray($records, NULL, 'A2');
        $styleArray = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];
        $worksheet->getStyle('A1:B30')
                  ->applyFromArray($styleArray);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        $writer->save($this->getExcelPath());
    }

    public function buildAttributes($attributes)
    {
        $item = '';
        foreach ($attributes as $attribute) {
            $item .= "'$attribute',\n            ";
        }
        $item = rtrim($item);
        $text = <<<text
    public function attributes()
    {
        return [
            $item
        ];
    }
text;
        return $text;
    }

    protected function getAutoIncrement()
    {
        $worksheet = $this->spreadsheet->getActiveSheet();
        return current(max($worksheet->rangeToArray("A2:A100"))) + 1;
    }

    protected function getExcelPath()
    {
        return static::$excelPath . $this->tableName . '.xlsx';
    }

    public function update(){
        $properties = get_object_vars($this);
        $records = $this->all();
        foreach ($records as $key => $record) {
            $records[$key] = (array)$record;
        }
        foreach ($properties as $attribute => $value) {
            if (!empty($value) && in_array($attribute, $this->attributes())) {
                $records[$this->id][$attribute] = $value;
            }
        }
        $worksheet = $this->spreadsheet->getActiveSheet();
        $worksheet->fromArray($records, NULL, 'A2');
        $styleArray = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];
        $worksheet->getStyle('A1:B30')
                  ->applyFromArray($styleArray);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        $writer->save($this->getExcelPath());
    }
}

