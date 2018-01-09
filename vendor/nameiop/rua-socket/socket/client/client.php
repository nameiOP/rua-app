<?php


namespace rsk\client;

class client extends baseClient
{


    /**
     * 客户端启动时间
     * @var int
     */
    private $start_time = 0;






    /**
     * 初始化服务器信息
     * @author liu.bin 2017/9/27 15:50
     */
    protected function init(){

        //客户端启动时间
        $this->start_time = time();

    }






    /**
     * 发送文件到服务端
     * @param int $fd
     * @param string $filename
     * @param int $offset
     * @param int $length
     * @author liu.bin 2017/9/27 15:03
     */
    public function sendFile($fd, $filename, $offset =0, $length = 0){

    }





    /**
     * 展示启动界面
     * @return void
     */
    public function displayUI(){

        return false;
        echo "----------------------- RUA SOCKET CLIENT ------------------------".PHP_EOL;
        echo "Rua client version: 0.0.1".PHP_EOL;
        echo "PHP version:" . PHP_VERSION .PHP_EOL;
        echo "socket connect ".$this->host. ":" .$this->port ." status [ok]".PHP_EOL;
        echo "-------------------------------------------------------------—---".PHP_EOL;
        echo "Press Ctrl-C to quit. Start success.".PHP_EOL;

    }





}

