第一版简要功能点
1. 主进程启动后脱离shell, 实现主进程的守护。
2. 主进程监管子进程, 一旦子进程意外退出, 则重新开启进程实例。
3. 查看子进程运行状态  [ 命令 php server.php ps ]。
4. 错误重定向, 非fetal error 重定向到 log/${today}.error.log。
5. 注意, 如果子进程设置了常驻内存,但确有fetal error, 会导致 主进程不断重启子进程(时间间隔 1s)
6. 子进程定义如果定义maxloop, 则运行maxloop次循环后自动退出, 可以防止内存泄露问题。

使用方法
1. 启动 php server.php start
2. 关闭 php server.php stop
3. 查看子进程状态 php server.php ps
4. 配置进程
	1) config中加入 配置 , 
        /*  
         * 'proc name' => array(
         *  'className' => 'className',
         *  'initParam' => array(),  #  __construct param display as an array
         *  'daemon' => true/false,  # if set true, your proc run() function will loop without ended, if set false, your proc will exited when run() function run ended; 
         *  'multi'  => int(num),    # the number of your proc, please not set too big , memory will run out. 
         *  'maxLoop' => int(num)    # max loop the proc running
         */
    2) 编写 子进程类, 需要集成 ProcBasic基类, 进程类中的__counstruct()需要接受配置中initParam的配置信息,  run()函数为默认入口函数, 需要 public。