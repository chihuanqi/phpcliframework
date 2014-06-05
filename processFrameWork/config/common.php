<?php

define('ROOT',dirname(dirname(__FILE__)));
define('LIB', ROOT.DIRECTORY_SEPARATOR.'lib');
define('CONF', ROOT.DIRECTORY_SEPARATOR.'config');
define('LOG', ROOT.DIRECTORY_SEPARATOR.'log');
define('PROC', ROOT.DIRECTORY_SEPARATOR.'proc');
define('VARDIR', ROOT.DIRECTORY_SEPARATOR.'var');

ini_set("display_errors",1);
error_reporting(E_ALL);


function __autoload($classname)
{
	$load_file = LIB.DIRECTORY_SEPARATOR.$classname.'.php';
	if (file_exists($load_file)) {
		require($load_file);
	}
}


function errorlog($errno, $errstr, $errfile, $errline)
{
		$pid = posix_getpid();
		$error_str = '['.$errno.']'.$errstr.' in '.$errfile.' at line '.$errline;
		//echo $error_str;
		$log = date("Y-m-d H:i:s", time()).'unkown['.$pid.'] error info :'.$error_str."\n";
		$logLocation = LOG.DIRECTORY_SEPARATOR.date("Y-m-d", time()).".error.log";
		file_put_contents($logLocation, $log, FILE_APPEND);
}

set_error_handler("errorlog");
date_default_timezone_set('Asia/shanghai');
