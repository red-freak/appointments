<?php

error_reporting(E_ALL);
define ('BASE_DIR', dirname(__FILE__));
define ('BASE_URL', 'http://auc.test/');
//define ('BASE_URL', 'https://amor.cms.hu-berlin.de/~georgesv/auc/');

if(!function_exists('classAutoLoader')){
	function classAutoLoader($class){

		require_once BASE_DIR . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'helpers.php';

		$class = str_replace('App\\', '', $class);

		$path = BASE_DIR . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
		if(is_file($path) && !class_exists($class)) require_once($path);
	}
}
spl_autoload_register('classAutoLoader');