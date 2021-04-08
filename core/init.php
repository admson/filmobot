<?php
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);


	function classAutoLoad($class_name) {
		if (file_exists('core/class/'.strtolower($class_name).'.class.php')) {
	    	include_once 'core/class/'.strtolower($class_name).'.class.php';
	    }
	}
	spl_autoload_register('classAutoLoad');

    // Дебаг любых данных в файл
    function dumpData($data) {
        file_put_contents(__DIR__ . '/dumpdata.txt', print_r($data, true));
    }

    include 'config.php'; // Основной конфиг
    include 'lang/'.LANG.'.php';

	//Подключаем Базу Данных
	$db = new DB();
	$dbconnection = $db->open();

	include 'core/controllers/rpcController.php';
    include "core/controllers/admin/messages.php";
    include "core/controllers/main/messages.php";
    include "core/controllers/main/views.php";