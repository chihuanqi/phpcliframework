<?php
declare(ticks = 1)
	;
class ProcBasic
{
	protected $procName = NULL;
	protected $pid = NULL; 
	protected $isRunning = true;
	protected $multi = 1;
	protected $maxLoop = 0;
	protected $loopTimes = 0;

	public function setProcName($name)
	{
		$this->procName = $name;
	}

	public function setPid($pid)
	{
		$this->pid = $pid;
	}
	
	public function setMulti($multi)
	{
		$this->multi = $multi;
	}
	
	public function setMaxLoop($max_loop) 
	{
		$this->maxLoop = $max_loop;
	}

	public function log($log)
	{
		$pid = posix_getpid();
		$log = date("Y-m-d H:i:s", time()).' '.$this->procName.'['.$pid.']'.$log."\n";
		$logLocation = LOG.DIRECTORY_SEPARATOR.date("Y-m-d", time()).'.log';
		file_put_contents($logLocation, $log, FILE_APPEND);
	}
	
	protected function registSingal()
	{
		pcntl_signal(SIGTERM, array(
			$this,
			'signalHandler'
		));
		pcntl_signal(SIGINT, array(
			$this,
			'signalHandler'
		));
		pcntl_signal(SIGHUP, array(
			$this,
			'signalHandler'
		));
	}
	
	protected function signalHandler($signo)
	{
		switch ($signo) {
			case SIGTERM :
			case SIGINT :
			case SIGHUP :
				$this->isRunning = false;
		}
	}
	
	protected function loop_run()
	{
		$this->registSingal();
		while($this->isRunning) {
			$this->run();
			sleep(1);
			if ($this->maxLoop!=0) {
				$this->loopTimes++;
				if($this->loopTimes >= $this->maxLoop) {
					$this->log("loop times big than max loop ".$this->maxLoop.", exit(0) for free memory");
					exit(0);
				}
			}
		}
		$this->log("loop run function ended");
	}

	protected function single_run()
	{
		$this->registSingal();
		$this->run();
		$this->log("run function ended");
	}
}
