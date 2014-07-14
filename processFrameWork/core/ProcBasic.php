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
		protected $cmp = array();
		protected $components = array();
		protected $startProcs = NULL;

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

		public function setComponent($component)
		{   
				$this->components = $component;
		}   

		public function setStartProcs($startProcs)
		{
				$this->startProcs = $startProcs;
		}

		public function log($log, $error = false)
		{
				$startProcs = substr($this->startProcs, 0, 32);
				$pid = posix_getpid();
				$log_type = '[info]';
				if ($error)  $log_type = '[error]';
				$log = date("Y-m-d H:i:s", time()).' '.$this->procName.'['.$pid.']'.$log_type.':'.$log."\n";
				$logLocation = LOG.DIRECTORY_SEPARATOR.$this->startProcs.'.log';
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

		protected function loopRun()
		{
				$this->registSingal();
				while($this->isRunning) {
						$this->run();
						if ($this->maxLoop!=0) {
								$this->loopTimes++;
								if($this->loopTimes >= $this->maxLoop) {
										$this->log("loop times big than max loop ".$this->maxLoop.", exit(0) for free memory");
										exit(0);
								}
						}
						$this->log('loop ended sleep 1');
						sleep(1);
				}
				$this->log("loop run function ended");
		}

		protected function singleRun()
		{
				$this->registSingal();
				$this->run();
				$this->log("run function ended");
		}

		protected function getCmt($property_name, $force=false)
		{   
				if (isset($this->cmp[$property_name]) && $force == false) {
						return $this->cmp[$property_name];
				}

				if (isset($this->components[$property_name])) {
						$this->cmp[$property_name] = $this->objectFactory($this->components[$property_name]['componentName'], $this->components[$property_name]['initParam']);
						return $this->cmp[$property_name];
				}

				trigger_error('components '.$property_name." is not exists"); exit;
		}   

		protected function objectFactory($classname, $initparam)
		{   

				$param_string = implode(',', array_map(array($this, 'repr'), $initparam));
				$exp = sprintf('$tmp_object = new %s(%s);', $classname, $param_string);
				eval($exp);
				return $tmp_object;
		}   

		protected function  repr($item) 
		{   
				return var_export($item, true);
		} 

		protected function errorlog($errno, $errstr, $errfile, $errline)
		{
				$error_str = '['.$errno.']'.$errstr.' in '.$errfile.' at line '.$errline;
				echo $error_str."\n";
				$this->log($error_str);
		}

		protected function run() { }
}
