<?php
declare(ticks = 1)
	;
class ProcOne extends ProcBasic
{
	private $count=0;

	public function __construct($count)
	{
		$this->count = $count;
	}


	public function run()
	{
		for($i=0;$i<=63;$i++) {
				echo $i."\n";
				sleep(3);
		}
	}

}
