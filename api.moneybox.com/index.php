<?php
//настройки
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Credentials: true');
header('Content-type: json/application');

//подключение файлов
define('ROOT', dirname(__FILE__));
require_once(ROOT.'/components/Db.php');
require_once(ROOT.'/components/Router.php');