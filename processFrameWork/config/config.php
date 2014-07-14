<?php
return array(
	'daemon'  => false,
	'maxloop' => 1000000,
	'process'=> array(
		/*
		 * 'proc name' => array(
		 * 	'className' => 'className',
		 * 	'initParam' => array(),  #  __construct param display as an array
		 * 	'daemon' => true/false,  # if set true, your proc run() function will loop without ended, if set false, your proc will exited when run() function run ended; 
		 * 	'multi'  => int(num),    # the number of your proc, please not set too big , memory will run out. 
		 * 	'maxLoop'  => int(num),    # the number of your proc, please not set too big , memory will run out. 
		 */
		
			'One' => array(
					'className' => 'ProcOne', 
					'initParam' => array(1), 
					'daemon'    => false, 
					'multi'     => 1,
			),

			'Two' => array(
					'className' => 'ProcTest', 
					'initParam' => array(1), 
					'daemon'    => false, 
					'multi'     => 1,
			),

	),
	'component' => array(
		'mysqlrd' => array(
			'componentName' => 'pdo',
			'initParam'=> array(
				'dsn'    => 'mysql:dbname=test;host=127.0.0.1;port=3307',
				'user'   => 'root',
				'passwd' => '123456',
			)
		),
		'fileOp' => array(
			'componentName' => 'fileOpener',
			'initParam' => array(),		
		)
	)
) ;
