<?php

define('ROOT', dirname(__FILE__));

require ROOT.'/config/common.php';

if (isset($argv[1])) {
	$option = $argv[1];
}

if (isset($argv[2])) {
    $start_procs = $argv[2];
    if (strlen($start_procs) > 250 ) { 
        echo "your procs is too long, please try short it litter than 250. \n";
        exit(); 
    }
} else if($option == 'ps') { 
    $start_procs = '';
} else {
    echo "please input the proc name your want to start|stop, using a or a-b , or a+b \n";
    exit(); 
}

$server = new MainProc(require(CONF.DIRECTORY_SEPARATOR.'config.php'), $start_procs);
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
