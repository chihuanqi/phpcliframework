<?php
class ProcTest extends ProcBasic
{
	public function run()
	{
		foreach($this->getCmt('fileOp')->open(VARDIR) as $filename)
		{
			echo $filename."\n";
		}
	}

}
