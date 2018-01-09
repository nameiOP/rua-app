<?php
namespace pfork;


/**
 * PHP 多进程
 * Class pfork
 * @package pfork
 */
class phpfork{


    /**
     * @var object 用户接口
     */
    private $_runnable = null;


    /**
     * @var int 进程id
     */
    private $pid = 0;


    /**
     * @var int 父进程pid
     */
    private $parent_pid = 0;







    private $_server = null;


    private $_connect = null;


    /**
     * 构造函数
     */
    public function __construct($run=null)
    {

        if(is_object($run)){
            $reflection = new \ReflectionClass($run);
            if($reflection->implementsInterface('rua\able\runnable')){
                $this->_runnable = $run;
            }
        }
    }


    /**
     * 绑定服务器
     * @param \rsk\server\server $server
     */
    public function bindServer($server){
        $this->_server = $server;
    }


    /**
     * 绑定客户端通信连接
     * @param  \rsk\server\connect $connect
     */
    public function bindConnect($connect){
        $this->_connect = $connect;
    }



    /**
     * 进程开启
     */
    public function start(){

        //父进程id
        $pid = posix_getpid();
        if($pid > 0){
            $this->parent_pid = $pid;
        }


        // fork 子进程
        $pid = pcntl_fork();


        // fork 失败
        if ($pid == -1) {
            throw new \Exception('fork子进程失败!');
        }



        //* 子进程
        if ($pid == 0) {


            $pid = posix_getpid();
            if($pid > 0 ){
                $this->pid = $pid;
            }

            if(is_null($this->_runnable)){
                $this->run();
            }else{
                $this->_runnable->run();
            }


            //不再执行从父进程拷贝过来的代码,直接中断
            exit();
        }


    }




    /**
     * run 入口
     */
    protected function run(){

    }


    /**
     * 获取服务器对象
     * @return \rsk\server\server;
     */
    public function getServer(){
        return $this->_server;
    }


    /**
     * 获取客户端连接
     * @return \rsk\server\connect;
     */
    public function getConnect(){
        return $this->_connect;
    }


    /**
     * 获取父进程id
     * @return int
     */
    public function getParentPid(){
        return $this->parent_pid;
    }


    /**
     * 获取子进程id
     * @return int
     */
    public function getPid(){
        return $this->pid;
    }


}
