<?php


namespace rsk\traits;

trait streamsocketable
{




    /**
     * @var int 连接编号
     */
    public $fd = 0;




    /**
     * php 套接字
     * @var
     */
    public $socket;




    /**
     * @var bool socket 阻塞模式
     * true 阻塞模式
     * false 非阻塞模式
     */
    public $block = true;





    /**
     * 打印编号
     * @return string
     * @author liu.bin 2017/9/28 14:17
     */
    public function __toString()
    {
        return (string)$this->getFd();
    }






    /**
     * 创建socket套接字,保证与 socketable 接口统一
     * @return bool
     * @throws \Exception
     * @author liu.bin 2017/10/26 15:10
     */
    public function createSocket(){

        return true;
    }




    /**
     * 客户端socket连接服务器
     * @param string $host
     * @param int $port
     * @return bool
     */
    public function socketConnect($host,$port){

        $address = "tcp://".$host.":".$port;
        $err_no = 0;
        $err_str = '';
        $socket = stream_socket_client($address, $err_no, $err_str, 5);
        if($socket){
            $this->socket = $socket;
            $this->fd = socket_to_fd($socket);
            return true;
        }else{
            return false;
        }
    }





    /**
     * 服务端socket 监听端口
     * @param string $host
     * @param int $port
     * @param int $backlog backlog是增加并发的关键
     * @return bool
     *
     */
    public function socketListen($host,$port,$backlog=102400){


        //初始化socket配置
        $context_option = array();

        //backlog 是增加并发的关键
        if (!isset($context_option['socket']['backlog'])) {
            $context_option['socket']['backlog'] = $backlog;
        }


        /**
         * 开启地址重复利用
         * http://php.net/manual/en/context.socket.php
         * http://blog.csdn.net/yaokai_assultmaster/article/details/68951150
         */
        if(!isset($context_option['socket']['so_reuseport'])){
            $context_option['socket']['so_reuseport'] = 1;
        }
        $_context = stream_context_create($context_option);
        $param = 'tcp://'.$host.':'.$port;
        $socket = stream_socket_server($param, $errNo, $errStr,STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,$_context);
        if (!$socket) {
            return false;
        }

        $this->socket = $socket;
        $this->fd = socket_to_fd($this->socket);


        return true;
    }





	/**
	 * 接受socket请求
	 * 此方法可以创建客户端socket对象
     * @param resource $socket master-socket
     * @return resource|bool
	 */
	public function socketAccept($socket){

        $peer_name = '';
		$msg_socket = @stream_socket_accept($socket,5,$peer_name);
        if(is_resource($msg_socket)){
            list($address,$port) = explode(':',$peer_name);
            return [$msg_socket,$address,$port];
        }
        return false;
	}


    /**
     * 发送消息
     * @param $socket
     * @param $data
     * @return int
     */
    public function socketSend($socket,$data){
        return stream_socket_sendto($socket,$data);
    }


    /**
     * 从socket读取数据
     *
     * stream_socket_recvfrom: 不支持阻塞读,所以自己实现了阻塞读
     *
     *      1:STREAM_OOB(系统自带)       协议的实现为了提高效率，往往在应用层传来少量的数据时不马上发送，而是等到数据缓冲区里有了一定量的数据时才一起发送，
     *                                  但有些应用本身数据量并不多，而且需要马上发送，这时，就用紧急指针，这样数据就会马上发送，而不需等待有大量的数据。
     *
     *      2:STREAM_PEEK(系统自带)      从接受队列的起始位置接收数据，但不将他们从接受队列中移除。
     *                                  STREAM_PEEK标志会将套接字接收队列中的可读的数据拷贝到缓冲区，但不会使套接子接收队列中的数据减少，
     *                                  常见的是：例如调用recv或read后，导致套接字接收队列中的数据被读取后而减少，而指定了STREAM_PEEK标志，
     *                                  可通过返回值获得可读数据长度，并且不会减少套接字接收缓冲区中的数据，所以可以供程序的其他部分继续读取。
     *
     *      3:MSG_WAITALL(自实现)        [阻塞读取] 在接收到指定长度的字符之前,进程将一直阻塞,一般用作消息长度是固定的协议
     *
     *      4:MSG_DONTWAIT(自实现)       [非阻塞模式] 接收指定长度的值,如果缓冲区没有数据,则立即返回。有数据,则按最大的读,并立即返回。
     *
     *
     *
     * @param $socket
     * @param $buffer_size
     * @param $flag
     * @return bool|string
     */
    public function socketReceive($socket,$buffer_size,$flag=MSG_DONTWAIT){

        if(MSG_DONTWAIT == $flag){
            //非阻塞读(默认)
            $buffer = stream_socket_recvfrom($socket,$buffer_size);
        }elseif(MSG_WAITALL == $flag){

            //阻塞读 stream_socket_recvfrom不支持阻塞读,需要用到while
            $read_buffer_len = 0;
            $buffer = '';
            while($read_buffer_len < $buffer_size){
                $buffer .= stream_socket_recvfrom($socket,$buffer_size);
                $read_buffer_len = strlen($buffer);
            }
        }else{
            $buffer = stream_socket_recvfrom($socket,$buffer_size,$flag);
        }

        if($buffer){
            console("buffer is :".$buffer.' -- length: '.strlen($buffer),'stream_socket');
            return $buffer;
        }else{
            return false;
        }
    }




    /**
     * socket_read:
     *      1:PHP_NORMAL_READ   按最大长度读取,遇到 PHP_EOL 返回,会过滤掉 PHP_EOL 字符;
     *                          下一次消息就从下一行开始读取;
     *                          这种模式适用于没有协议的情况,用于快速读取终端发送过来的数据,比如telnet;
     *
     *      2:PHP_BINARY_READ   最大按$buffer_size读取,有数据即返回;不会过滤掉PHP_EOL;
     *                          这种模式适用于自定义协议的情况,由自定义协议判断数据中是否包涵PHP_EOL;
     *
     *
     * 从客户端socket读取消息
     * @param $socket
     * @param $buffer_size
     * @param int $read_type
     * @return bool|string
     */
    public function socketRead($socket,$buffer_size,$read_type = PHP_BINARY_READ){


        if( PHP_BINARY_READ == $read_type ){
            //按最大长度读取,有值就立马返回
            $buffer = stream_socket_recvfrom($socket,$buffer_size,0);

        }elseif(PHP_NORMAL_READ == $read_type){
            //按最大长度和PHP_EOL读取,谁先满足就立即返回

            //不删除buffer读取
            $buffer = stream_socket_recvfrom($socket,$buffer_size,STREAM_PEEK);
            //已经读满$buffer_size && 读取的数据包涵 PHP_EOL
            if( $buffer && ( false !== ($str_pos = strpos($buffer,PHP_EOL)) ) ){
                //删除buffer读取
                $buffer = stream_socket_recvfrom($socket,($str_pos + strlen(PHP_EOL)));
                $buffer = substr($buffer,0,-(strlen(PHP_EOL)));
            }else{
                $buffer = stream_socket_recvfrom($socket,$buffer_size);
            }

        }else{
            //默认 按$read_type模式读取(STREAM_PEEK,STREAM_OOB)
            $buffer = stream_socket_recvfrom($socket,$buffer_size,$read_type);
        }

        if($buffer){
            return $buffer;
        }else{
            return false;
        }
    }


    /**
     * socket_select IO复用模型
     * @param array $read
     * @param array $write
     * @param array $except
     * @param $tv_sec
     * @param null $tv_usec
     * @return int
     *
     * @author liu.bin
     */
    public function socketSelect(array &$read, array &$write, array &$except, $tv_sec, $tv_usec = null){
        return stream_select($read, $write, $except, $tv_sec, $tv_usec);
    }




    /**
     * 设置socket为非阻塞模式
     * stream家族函数,都是非阻塞
     * @param $socket
     * @return bool
     */
    public function socketSetNonBlock($socket){

        if(stream_set_blocking($socket,0)){
            $this->block = false;
            return true;
        }
        return false;
    }



    /**
     * 设置socket为阻塞模式
     * stream家族函数,都是非阻塞
     * @param $socket
     * @return bool
     */
    public function socketSetBlock($socket){
        if(stream_set_blocking($socket,1)){
            $this->block = true;
            return true;
        }
        return false;

    }


    /**
     * 关闭客户端连接
     * @param $socket
     * @return bool
     */
    public function socketClose($socket,$how=STREAM_SHUT_WR){
        stream_socket_shutdown($socket,$how);
        return fclose($socket);
    }



    /**
     * 获取socket
     * @return mixed
     * @author liu.bin 2017/9/28 15:06
     */
    public function getSocket(){
        return $this->socket;
    }





    /**
     * 获取连接编号
     * @author liu.bin 2017/9/28 14:18
     */
    public function getFd(){
        return $this->fd;
    }

}
