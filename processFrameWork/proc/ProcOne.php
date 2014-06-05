<?php
declare(ticks = 1)
	;
class ProcOne extends ProcBasic
{
	private $count=0;

	public function __construct($count)
	{
		$this->count    = $count;
	}

	public function run()
	{
		$this->log($this->count++);
	}

}
