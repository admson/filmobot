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

	function getLang($lang) {
        if ($lang == "ru" || $lang == "uk" || $lang == "kz" || $lang == "be" || is_null($lang)) {
            $lang = "ru";
        }else{
            $lang = "en";
        }
	    return $lang;
    }

    function decl($int, $expr){
        settype($int, "integer");
        $count = $int % 100;
        if ($count >= 5 && $count <= 20) {
            $result = $int." ".$expr['2'];
        } else {
            $count = $count % 10;
            if ($count == 1) {
                $result = $int." ".$expr['0'];
            } elseif ($count >= 2 && $count <= 4) {
                $result = $int." ".$expr['1'];
            } else {
                $result = $int." ".$expr['2'];
            }
        }
        return $result;
    }

    // Дебаг любых данных в файл
    function dumpData($data) {
        file_put_contents(__DIR__ . '/dumpdata.txt', print_r($data, true));
    }

    include 'core/config.php'; // Основной конфиг
    include 'core/lang.php'; // Файл языков

	//Подключаем Базу Данных
	$db = new DB();
	$dbconnection = $db->open();