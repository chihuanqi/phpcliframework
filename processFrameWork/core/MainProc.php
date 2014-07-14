<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/



/**
 * @file MainProc.php
 * @author suqian(com@baidu.com)
 * @date 2014/07/14 16:00:41
 * @brief 
 *  
 **/

declare(ticks=1)
    ;
class MainProc extends ProcBasic
{
    private $add = 0;
    private $process = array();
    private $processAll = array();
    private $childForked = false;
    private $pidFile    = 0;
    private $childrenProc = array();
    private $processFile = NULL;
    private $maxStopTime = 3;
    private $stdoutFile = '/dev/null';
    private $complicating = false;
    private $lastExitedProc = NULL;
    private $nextProc = array();

    public function __construct($config, $startProcs)
    {
        $this->initProcessConf($config['process']);
        $this->processFile = PSDIR.DIRECTORY_SEPARATOR.$startProcs.'.psinfo';
        $this->daemon  = $config['daemon'];
        if (isset($config['maxStopTime'])) {
            $this->maxStopTime = $config['maxStopTime'];
        }
        if (isset($config['component'])) {
            $this->setComponent($config['component']);
        }
        if (isset($config['stdoutFile'])) {
            $this->stdoutFile = $config['stdoutFile'];
        } else {
            $this->stdoutFile = STDOUTDIR.DIRECTORY_SEPARATOR.$startProcs.'.stdout';
        }
        $this->startProcs = $startProcs;
        $this->procName   = 'MainProc';
        set_error_handler(array(&$this, "errorlog"));
    }

    public function start()
    {
        $this->checkStartProcs();
        $this->checkIsRunning();
        if ($this->daemon) {
            $this->daemon();
        }
        $this->registSingal();
        $this->run();
    }

    public function stop()
    {
        $this->pidFile = PID.DIRECTORY_SEPARATOR.$this->startProcs.'.lock';
        #echo $this->pidFile; echo "\n";
        if (!file_exists($this->pidFile)) {
            echo "phpprocs is not running \n";
            exit;
        }
        $pid = (int)file_get_contents($this->pidFile);
        if(!posix_kill($pid, SIGTERM)) {
            echo 'failed to stop , please use linx term kill -9', PHP_EOL;
            exit(1);
        }
        $this->log('start stop php procs');
        echo "Stopping phpprocs:";
        while(file_exists($this->processFile)) {
            clearstatcache();
            usleep(100);
        }
        echo "[ \033[32m ok \033[0m ]";
        echo "\n";
    }

    public function help()
    {
        echo 'please use php server start | stop | help ';
        exit();
    }

    public function ps()
    {
        if (!function_exists('__cmp')) {

            function __cmp($a, $b)
            {
                if ($a[1] != $b[1]) {
                    return $a[1] > $b[1];
                } else {
                    return $a[0] > $b[0];
                }
            }
        }
        $psdir = opendir(PSDIR);
        while(($filename=readdir($psdir))!==false) {
            if (strpos($filename, '.psinfo') == false) continue;
            $processFile = PSDIR.DIRECTORY_SEPARATOR.$filename;
            $data = json_decode(file_get_contents($processFile), true);
            $tmp_data = array();
            foreach($data as $pid => $arr) {
                $tmp_data[$pid] = array($pid, $arr['procName'], $arr['multi']);
            }
            //print_r($data);
            $txt = "-------GROUP NAME :".substr($filename, 0, strrpos($filename, '.psinfo'))."------".PHP_EOL;
            $txt .= "STATUS\tPID\tNAME \tNUM" . PHP_EOL;
            usort($tmp_data, '__cmp');
            foreach ($tmp_data as $info) {
                $txt .= sprintf("%-6s\t%-6d\t%s\t%-6d" . PHP_EOL,
                    posix_kill($info[0], 0) ? 'ALIVE' : 'DEAD', $info[0], $info[1], $info[2]);
            }
            echo $txt;
        }
    }

    public function initProcessConf($proc_conf)
    {
        $this->processAll = $proc_conf;
        foreach(new DirectoryIterator(PROC) as $filename) {
            if ($filename =='.' || $filename =='..' || substr($filename, 0, 1) == '.' ||strpos($filename, '.php') === false ) continue;
            $procname = substr($filename, 0, strrpos($filename, '.php'));
            if(isset($this->processAll[$procname])) continue;
            $this->processAll[$procname] = array(
                'className' => $procname,
                'initParam' => array(),
                'daemon'    => false,
                'multi'     => 1,      
            );
        }
    }

    public function checkStartProcs()
    {
        if (strpos($this->startProcs, '+')!==false && strpos($this->startProcs, '-')!==false) {
            echo "you can not choose each mode using + and - at the same time \n";
            exit;
        }

        if (strpos($this->startProcs, '+')!==false) {
            $procs = explode("+", $this->startProcs);
            foreach($procs as $procname) {
                if (!isset($this->processAll[$procname])) {
                    echo "proc ".$procname." has not defined \n";
                    exit;
                }
                $this->process[$procname] = $this->processAll[$procname];
            }
        } else if (strpos($this->startProcs, '-')!==false) {
            $procs = explode("-", $this->startProcs);
            $prevproc = NULL;
            foreach($procs as $procname) {
                if (!isset($this->processAll[$procname])) {
                    echo "proc ".$procname." has not defined \n";
                    exit;
                }
                if ($prevproc == NULL) {
                    $this->process[$procname] = $this->processAll[$procname];
                    $prevproc = $procname;
                } else {
                    $this->nextProc[$prevproc] = $procname;
                    $prevproc = $procname;
                }
            }
            $this->nextProc[$procname] = NULL;
        } else {
            $procname = $this->startProcs;
            if (!isset($this->processAll[$procname])) {
                echo "proc ".$procname." has not defined \n";
                exit;
            }
            $this->process[$procname] = $this->processAll[$procname];
        }
    }

    public function checkIsRunning()
    {
        $this->pidFile = PID.DIRECTORY_SEPARATOR.$this->startProcs.'.lock';
        if (file_exists($this->pidFile)) {
            echo "php procs is already running, please not repeat start\n";
            exit;
        }
    }

    private function daemon()
    {
        $pid = pcntl_fork();
        if ($pid == -1 ) {
            echo 'fork process failed, please return the program and try again', PHP_EOL;
            exit(1);
        }

        if ($pid > 0 ) {
            echo "Starting phpprocs:";
            while(!file_exists($this->processFile)) {
                usleep(100000);
            }
            echo "[ \033[32m ok \033[0m ]";
            echo "\n";
            $this->log("daemon process start, parent cli process exited");
            exit(0);
        }

        $this->pid = posix_getpid();
        if(!file_put_contents($this->pidFile, $this->pid)) {
            echo "failed to write pid file".$this->pidFile." Daemon Master process exited ", PHP_EOL;
            exit(1);
        }
        $this->sessionId = posix_setsid();
        global $STDOUT, $STDERR;
        @fclose(STDOUT);
        @fclose(STDERR);
        $STDOUT = fopen($this->stdoutFile, "ar+");
        $STDERR = fopen($this->stdoutFile, "ar+");
        $this->log('Daemon Master process started');
    }

    private function startProc($procName)
    {
        $procArr = $this->processAll[$procName];
        if(!isset($procArr['className']))  return;  // not define which class to new , so jump;
        $proc_class_file = PROC.DIRECTORY_SEPARATOR.$procArr['className'].'.php';
        if (file_exists($proc_class_file)) {
            require($proc_class_file);
        } else {
            $this->log('file '.$proc_class_file.' not exists');
            trigger_error('file '.$proc_class_file.' not exists', E_USER_ERROR);
            continue;
        }
        if(!isset($procArr['initParam'])) $procArr['initParam'] = '';
        $multi = 1;
        if(isset($procArr['multi']) && is_numeric($procArr['multi']) && $procArr['multi']>0)
            $multi = $procArr['multi'];
        $maxLoop = 0;
        if (isset($procArr['maxLoop']) && is_numeric($procArr['maxLoop'])) {
            $maxLoop = $procArr['maxLoop'];
        }
        $daemon = false;
        if(isset($procArr['daemon'])) $daemon = $procArr['daemon'];
        for($i=1;$i<=$multi;$i++) 
            $this->forkChildren($procArr['className'], $procArr['initParam'], $procName, $i, $daemon, $maxLoop);

    }

    public function run()
    {
        foreach($this->process as $procName => $p) {
            $this->startProc($procName);
        }

        $this->freshProcessInfo();

        while(!empty($this->childrenProc)) {
            $exited_process_id = pcntl_wait($status, WNOHANG);
            if ($exited_process_id && array_key_exists($exited_process_id, $this->childrenProc)) {
                $this->log($this->childrenProc[$exited_process_id]['procName'].$this->childrenProc[$exited_process_id]['multi']." exited ");
                // if $this->isRunning is true  ,it means the children exited not parent kill;
                if ($this->childrenProc[$exited_process_id]['daemon'] && $this->isRunning) {
                    $this->forkChildren($this->childrenProc[$exited_process_id]['className'], 
                        $this->childrenProc[$exited_process_id]['initParam'],
                        $this->childrenProc[$exited_process_id]['procName'],
                        $this->childrenProc[$exited_process_id]['multi'],
                        $this->childrenProc[$exited_process_id]['daemon'],
                        $this->childrenProc[$exited_process_id]['maxLoop']
                    );
                }
                $this->lastExitedProc = $this->childrenProc[$exited_process_id]['procName'];
                unset($this->childrenProc[$exited_process_id]);
                $this->freshProcessInfo();
            }

            if (!$this->isRunning) {
                $this->stopChildren();
                if (time()-$this->stopTime > $this->maxStopTime) {
                    $this->log('exceed maximal stop time, force kill children');
                    $this->stopChildren(true);
                }
            }

            if (empty($this->childrenProc) && $this->isRunning && isset($this->nextProc[$this->lastExitedProc]) && !empty($this->nextProc[$this->lastExitedProc])) {
                $this->startProc($this->nextProc[$this->lastExitedProc]);
			}
			usleep(10);
        }

        unlink($this->processFile);
        if ($this->daemon) unlink($this->pidFile);

        $this->log('Master proc exited');
    }

    private function forkChildren($className, $initParam, $procName='unkown', $multi=1, $daemon=0, $maxLoop=0)
    {
        if (!class_exists($className)) {
            trigger_error('class:'.$className." is not exists");
            return;
        }
        $pid = pcntl_fork();
        if($pid == -1) {
            echo 'fork child failed';
            exit(1);
        }
        if($pid > 0) {
            $this->childrenProc[$pid]= array(
                'className' => $className,
                'initParam' => $initParam,
                'procName'  => $procName,
                'multi'     => $multi,
                'daemon'    => $daemon,
                'maxLoop'   => $maxLoop
            );
            $this->log('fork '.$className.' '.$multi.' successfully, pid is '.$pid);
        } else if ($pid == 0) {
            sleep(1); // sleep 1s ,avoid fetal error loop;
            $worker = $this->objectFactory($className, $initParam);
            $c_pid = posix_getpid();
            $worker->setProcName($procName);
            $worker->setPid($c_pid);
            $worker->setMulti($multi);
            $worker->setMaxLoop($maxLoop);
            $worker->setComponent($this->components);
            $worker->setStartProcs($this->startProcs);
            $worker->setDaemon($daemon);
            set_error_handler(array(&$worker, "errorlog"));
            if ($daemon) {
                $worker->loopRun();
            } else {
                $worker->singleRun();
            }
            exit(0);  // neccessary to  exit  , or will go back to run() funtion, makes trouble;
        }
    }

    protected function signalHandler($singal)
    {
        $this->log('Receive stop command');
        if(in_array($singal, array(SIGTERM, SIGINT, SIGHUP))) {
            $this->isRunning = false; //  set isRunning=false first, then send kill to childen!!!!
            $this->log('stop childrens');
            $this->stopTime = time();
            $this->stopChildren();
        }
    }

    protected function stopChildren($force=false)
    {
        if ($force) {
            $signal = SIGKILL;
        } else {
            $signal = SIGTERM;
        }

        foreach($this->childrenProc as $pid => $proc)
        {   
            if(posix_kill($pid, $signal)) {
                $this->log('send SIGTERM singal to '.$pid);
            }   
        }   

    }

    private function freshProcessInfo()
    {
        $fp = fopen($this->processFile, 'w');
        fwrite($fp, json_encode($this->childrenProc));
    }
}

