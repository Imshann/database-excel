<?php

require '../../vendor/autoload.php';
require '../../database.php';

new Database('../database/');

$nav = Nav::findOne(1);
$nav->nav_name = '中文';
$nav->update();
exit;