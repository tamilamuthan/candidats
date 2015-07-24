<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

$path   = realpath(dirname(__FILE__) . '/../');

$loader = require $path . '/vendor/autoload.php';
