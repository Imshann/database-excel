<?php

require '../../vendor/autoload.php';

use Douzhi\Database as DB;

DB::bootstrap()
  ->setExcelPath('../database/')
  ->connect();

$nav = Nav::findOne(2);
$nav->delete();
exit;