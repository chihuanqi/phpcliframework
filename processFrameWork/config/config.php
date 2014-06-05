<?php
return array(
	'pid_file'=> VARDIR.'/pid.lock',
	'daemon'  => true,
	'maxloop' => 1000000,
	'process_file' => VARDIR.'/proccess.txt',
	'process'=> array(
		/*
		 * 'proc name' => array(
		 * 	'className' => 'className',
		 * 	'initParam' => array(),  #  __construct param display as an array
		 * 	'daemon' => true/false,  # if set true, your proc run() function will loop without ended, if set false, your proc will exited when run() function run ended; 
		 * 	'multi'  => int(num),    # the number of your proc, please not set too big , memory will run out. 
		 */
			'Writer' => array(
					'className' => 'ProcOne', 
					'initParam' => array(1 ), 
					'daemon'    => 1, 
					'multi'     => 2,
					'maxLoop'   => 10,
			),
			'Buger' => array(
					'className' => 'ProcTwo', 
					'initParam' => array(1 ), 
					'daemon'=>1, 
					'multi'=>4
			),
			'Baiduer' => array(
					'className' => 'Baidu', 
					'initParam' => array(), 
					'daemon'    =>1, 
					'multi'     =>2
			),
			'Sogouer' => array(
					'className' => 'Sougou', 
					'initParam' => array(), 
					'daemon'    => 1, 
					'multi'     => 2
			),
	),
);
