##使用方法


1. 启动 php server.php start procname1+proname2
2. 关闭 php server.php stop  procname1+proname2
3. 查看所有进程组状态 php server.php ps
4. 配置进程

        1) config中加入 配置 ,
	
          
         'procname' => array(
          'className' => 'className',
          'initParam' => array(),  #  __construct param display as an array
          'daemon' => true/false,  
          # if set true, your proc run() function will loop without ended, 
          # if set false, your proc will exited when run() function run ended; 
          'multi'  => int(num),    # the number of your proc, please not set too big , memory will run out. 
          'maxLoop' => int(num)    # max loop the proc running
         
	2) 编写 子进程类, 需要继承 ProcBasic基类, 进程类中的__counstruct()需要接受配置中initParam的配置信息,  run()函数为默认入口函数, 需要 public。
	
##后续开发中的功能

1. 基于这个框架写一个httpserver, 可以使用 主进程listen -> PHP消息队列 -> 子进程抢队列    模式
2. 进程组启动目前已经分离，但是错误日志还没有分开（普通日志、标准输出重定向文件已分开）。
3. 除了并发执行还可以顺序执行, 最好可以达到执行完毕某一个进程以后, 执行另一个，某一个进程exit不影响另一个执行(如果exit了另一个不执行了那和写在一个程序没啥区别了)

##20140703更新功能点
1. 启动进程可以使用 a  or  a+b  来进行单独 或者 并发执行

          比如
          启动 php server.php start One+Two
          停止 php server.php stop One+Two 
 
2. 自身实现 nohup 功能, 如果config中设置 'daemon' => true,  则会设置标准输出到配置项中的stdoutFile 参数,  比如'stdoutFile' => './std.out'  防止终端关闭以后，PHP 打印数据到终端（已关闭）自动退出的情况


3. 文件迭代器添加awkopen方法，可以像awk一样迭代文件

          比如
          foreach($this->getCmt('fileOp')->awkopen(INPUT."/vsmed_click_log.parsed.20140626") as $line_arr) {
                $line = $line_arr[0];
                $a    = trim($line_arr[10]);
                $b    = trim($line_arr[2]);
                $c    = trim($line_arr[12]);
          }

##20140627更新功能点
1. 文件迭代器功能，逐行读文件可以使用组件fileOpener，之前fopen  while 之类的打开方式缩减为一句话

          foreach($this->getCmt('fileOp')->open(LOG."/2014-06-25.error.log") as $line_num => $line) {
          	echo $line_num."=>".trim($line); echo "\n";
          }	
         

2. 添加pdo组件在config中配置相应的

     	
      	 'component' => array(
      	     'mysqlrd' => array(
      	         	'className' => 'pdo',
      	         'initParam'=> array(
      	             'dsn'    => 'mysql:dbname=test;host=127.0.0.1;port=3307',
      	             'user'   => 'root',
      	             'passwd' => '123456',
      	         )
      	     )
      	  )
      	  -----------------------使用方式-----------------------------
      	  $this->getCmt('mysqlrd')->query($sql);
      	  
##第一版简要功能点


1. 主进程启动后脱离shell, 实现主进程的守护。
2. 主进程监管子进程, 一旦子进程意外退出, 则重新开启进程实例。
3. 查看子进程运行状态  [ 命令 php server.php ps ]。
4. 错误重定向, 非fatal error 重定向到 log/${today}.error.log。
5. 注意, 如果子进程设置了常驻内存,但却有fatal error, 会导致 主进程不断重启子进程(时间间隔 1s)
6. 子进程定义如果定义maxloop, 则运行maxloop次循环后自动退出, 可以防止内存泄露问题。
