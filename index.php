<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/core/autoload.php';

use core\MyAPI as mAPI;

try {
  $API = new mAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
  echo $API->processRequest();
} catch (Exception $e) {
  echo json_encode(array('error' => $e->getMessage()));
}