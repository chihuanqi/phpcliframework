<?php
require './config/common.php';

if (isset($argv[1])) {
	$option = $argv[1];
}

if (isset($argv[2])) {
	echo $argv[2]; exit;
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
