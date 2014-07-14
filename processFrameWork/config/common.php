<?php

define('LIB', ROOT.DIRECTORY_SEPARATOR.'lib');
define('CORE', ROOT.DIRECTORY_SEPARATOR.'core');
define('CONF', ROOT.DIRECTORY_SEPARATOR.'config');
define('PROC', ROOT.DIRECTORY_SEPARATOR.'proc');
define('VARDIR', ROOT.DIRECTORY_SEPARATOR.'var');
define('LOG', VARDIR.DIRECTORY_SEPARATOR.'log');
define('PID', VARDIR.DIRECTORY_SEPARATOR.'pids');
define('PSDIR', VARDIR.DIRECTORY_SEPARATOR.'psfile');
define('STDOUTDIR', VARDIR.DIRECTORY_SEPARATOR.'stdout');
define('OUTPUT', VARDIR.DIRECTORY_SEPARATOR.'output');
define('INPUT', VARDIR.DIRECTORY_SEPARATOR.'input');

require(CONF.DIRECTORY_SEPARATOR."vsdata_common.php");

ini_set("display_errors",1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/shanghai');


function __autoload($classname)
{
	$load_file = CORE.DIRECTORY_SEPARATOR.$classname.'.php';
	$app_lib_file = LIB.DIRECTORY_SEPARATOR.$classname.'.php';
	
	if (file_exists($load_file)) {
		require($load_file);
		return;
	}

	if (file_exists($app_lib_file)) {
		require($app_lib_file);
		return;
	}

	trigger_error("Can not find class ".$classname." in lib directory ", E_USER_ERROR);
}
