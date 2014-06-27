<?php
declare(ticks = 1)
	;
class ProcTwo extends ProcBasic
{
	private $count=0;

	public function __construct($count)
	{
		$this->count = $count;
	}
	public function run()
	{
		echo $this->count++;
		$this->log($this->count."aaaaaaaaaaaaaaaaaaaawhyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy");
	}

}
