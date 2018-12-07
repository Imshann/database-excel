<?php

require '../../vendor/autoload.php';
require '../../database.php';

new Database('../database/');

$navs = Nav::find()->all();
var_dump($navs);