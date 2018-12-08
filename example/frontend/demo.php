<?php

require '../../vendor/autoload.php';

use Douzhi\Database as DB;

DB::bootstrap()
  ->setExcelPath('../database/')
  ->connect();

$nav = Nav::findOne(1);
$nav->nav_name = '中文';
$nav->update();
exit;