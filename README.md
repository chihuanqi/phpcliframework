#轻量级PHP多任务框架

##使用方法


1. 启动 

          单独执行  php server.php start procname
          并发执行  php server.php start procname1+proname2
          顺序执行  php server.php start procname1-proname2
	
2. 关闭 

          php server.php stop  procname
          php server.php stop  procname1+proname2
          php server.php stop  procname1-proname2

3. 查看所有进程组状态

          php server.php ps

4. 配置进程

          1) 编写 子进程类, 需要继承 ProcBasic基类, 
             进程类中的__counstruct()需要接受配置中initParam的配置信息, 没有initParam可以忽略,  
             run()函数为默认入口函数, 需要 public, 
             把该进程类放入Proc 目录中进行执行。


          2) 如果不按照默认的配置执行，那么需要在config中加入 配置 ,
          
             'procname' => array(
                'className' => 'className',
                'initParam' => array(), //  __construct param display as an array
                'daemon' => true/false, 
                //if set true, your proc run() function will loop without ended, 
                //if set false, your proc will exited when run() function run ended; 
                'multi'   => int(num), // the number copy of your proc . 
                'maxLoop' => int(num)  //max loop the proc running
             )
         
	
	
##20140714更新功能点
1. 启动进程可以用 

           php server.php start One-Two  顺序执行


2. 进程可以不再作配置, 直接在Proc文件夹中定义 任务名称= 文件名称（任务名称.php）= 类名, 则默认以单实例,非常驻内存模式启动  非默认配置则仍需要在config.php中 进行配置.

3. 非daemon 进程 stop 时 直接退出


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
