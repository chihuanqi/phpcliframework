<?php
require './config/common.php';

if (isset($argv[1])) {
	$option = $argv[1];
}

$server = new MainProc(require(CONF.DIRECTORY_SEPARATOR.'config.php'));
switch($option) {
	case 'start' :
		$server->start();
		break;
	case 'stop':
		$server->stop();
		break;
	case 'ps':
		$server->ps();
		break;
	default :
		$server->help();
}
