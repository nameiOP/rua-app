<?php


namespace rsk\io;


use Builder;
use rsk\server\server;
use rsk\server\connect;


class ioSelect extends loop {


    /**
     * 执行入口
     */
    public function run(){


        // 获取 server对象
        $this->server = Builder::$server;


        //阻塞和非阻塞模式都会在 socket_select 处阻塞
        //$this->server->socketSetBlock($this->server->getSocket());
        //$this->server->socketSetNonBlock($this->server->getSocket());

        $this->loop(null);
    }




    /**
     * 同步非阻塞
     * socket select loop
     *
     * @param int|null $timeout 超时时间
     * @author liu.bin 2017/9/26 11:43
     */
    public function loop($timeout=null)
    {



        //循环(保证多客户端连接)
        while(true){


            //重置所有链接队列
            $socketReadQueue    = $this->server->getConnSocket();
            $socketWriteQueue   = array();
            $socketExceptQueue  = array();

            /**
             * socket io复用模型
             *
             * $socketReadQueue:
             *      这个集合中应该包括文件描述符，我们是要监视这些文件描述符的读变化的，即我们关心是否可以从这些文件中读取数据了，
             *      如果这个集合中有一个文件可读，select就会返回一个大于0的值，表示有文件可读，如果没有可读的文件，则根据timeout参数再判断是否超时，
             *      若超出timeout的时间，select返回0，若发生错误返回负值。可以传入NULL值，表示不关心任何文件的读变化。
             *
             *
             * $socketWriteQueue:
             *      这个集合中应该包括文件描述符，我们是要监视这些文件描述符的写变化的，即我们关心是否可以向这些文件中写入数据了，
             *      如果这个集合中有一个文件可写，select就会返回一个大于0的值，表示有文件可写，如果没有可写的文件，则根据timeout再判断是否超时，
             *      若超出timeout的时间，select返回0，若发生错误返回负值。可以传入NULL值，表示不关心任何文件的写变化。
             *
             *
             * $socketExceptQueue:
             *      同上,用来监视文件异常。
             *
             * $timeout :
             *
             *      NULL:
             *          若将NULL以形参传入，即不传入时间结构，就是将select置于[阻塞状态]，一定等到监视文件描述符集合中某个文件描述符发生变化为止；
             *      0:
             *          若将时间值设为0秒0毫秒，就变成一个纯粹的[非阻塞函数]，不管文件描述符是否有变化，都立刻返回继续执行，文件无变化返回0，有变化返回一个正值；
             *      >0:
             *          timeout的值大于0，这就是等待的超时时间，即select在timeout时间内[阻塞]，超时时间之内有事件到来就返回了，否则在超时后不管怎样一定返回，返回值同上述。
             *
             *
             * [socketSelect] 如果有消息可读,socketSelect将一直触发,需要尽快将消息读出;如果没有消息可读,则会阻塞
             *
             */
            if( !$this->server->socketSelect($socketReadQueue, $socketWriteQueue, $socketExceptQueue, $timeout) ){
                //处理在非阻塞的模式下,不会往下执行,否则 socket_accept()会有警告
                continue;
            }


            /**
             * 可读
             */
            foreach($socketReadQueue as $socket){


                if($socket == $this->server->getSocket()){

                    //接收客户端连接
                    $this->notifyAccept();

                }else{

					//接收客户端消息

					$this->notifyRead($socket);

                }
            }

            //sleep(1);

            /**
             * todo 可写
             */
            foreach($socketWriteQueue as $socket){
                // ...
            }





            /**
             * todo 异常
             */
            foreach($socketExceptQueue as $socket){
                // ...
            }


        }


    }




    /**
     * 接收客户端连接
     * @return bool
     */
    private function notifyAccept(){

        //接受客户端链接（创建新的客户端 socket）
        if( false === ($connect = $this->server->accept()) ){
            return false;
        }


        //检测客户端是否连接成功
        if($connect->getStatus() == connect::STATUS_CLOSE){
            return false;
        }

        //$connect 绑定接收消息事件
        if( !$connect->hasEventHandlers(connect::EVENT_READ) ){
            $connect->on(connect::EVENT_READ,[$connect,'receive']);
        }


        $fd = $connect->getFd();
        $address = $connect->_from_address;
        $port = $connect->_from_port;
        console('accept success, id ['.$fd.'] ,address ['.$address.'] ,port ['.$port.']','ioloop');

        return true;

    }




    /**
     * 读取客户端消息
     *
     * @desc    在socket_select IO模式下,不需要死循环读取客户端的数据, 客户端发送消息或主动断开,都会触发
     *          客户端一次请求两个连接的时候,会通过 socket_select 触发两次 notifyRead
     *
     *
     * @param resource $socket 客户端socket
     * @return bool
     */
    private function notifyRead($socket){

        //获取 fd
        $fd = socket_to_fd($socket);
        $connect = $this->server->getConnect($fd);



        /**
         * connect 读取socket消息
         *
         *
         * 在connect中会根据读取的内容以及协议规定,设置connect的连接状态
         *
         * 消息、协议 与 连接关闭的关系:
         * TCP 短连接:读取数据,发送数据,关闭连接;
         * TCP 长连接:读取数据(非阻塞),发送数据 | 读取数据(非阻塞)[有数据],发送数据 ... | 读取数据(非阻塞)[超时]->关闭连接;
         * http短连接:读取数据,发送数据,关闭连接;
         * http长连接:读取数据(非阻塞),发送数据 | 读取数据(非阻塞)[有数据],发送数据 ... | 读取数据(非阻塞)[超时]->关闭连接;
         *
         */
        $connReadEvent = Builder::$app->get('protocol')->getReadEvent();

        //非阻塞模式读取消息,在socket_select模式下,肯定会读取到消息
        $connect->trigger(connect::EVENT_READ,$connReadEvent);


        //保持连接状态
        if(connect::STATUS_ACTIVE == $connect->getStatus()){

            console('connect fd '.$fd.' status:'.$connect->getStatus(),'select - STATUS_ACTIVE');

            $receiveEvent       = $this->server->getReceiveEvent();
            $receiveEvent->fd   = $fd;
            $receiveEvent->receive_data = $connect->getReadData();

            //执行接收消息事件
            $this->server->trigger(server::EVENT_RSK_RECEIVE,$receiveEvent);
            return true;
        }



        //已关闭
        if(connect::STATUS_CLOSE == $connect->getStatus()){

            console('connect fd '.$fd.' status:'.$connect->getStatus(),'select - STATUS_CLOSE');

            //* 接收空消息,客户端主动断开
            $stopEvent      = $this->server->getStopEvent();
            $stopEvent->fd  = $fd;

            //执行停止事件
            $this->server->trigger(server::EVENT_RSK_STOP,$stopEvent);
            $this->server->removeConnect($fd);
            return true;
        }



        //短连接状态(返回数据后关闭连接)
        if(connect::STATUS_PEND == $connect->getStatus()){

            console('connect fd '.$fd.' status:'.$connect->getStatus(),'select - STATUS_PEND');

            $receiveEvent       = $this->server->getReceiveEvent();
            $receiveEvent->fd   = $fd;
            $receiveEvent->receive_data = $connect->getReadData();;
            //执行接收消息事件
            $this->server->trigger(server::EVENT_RSK_RECEIVE,$receiveEvent);


            //* 断开连接
            $stopEvent      = $this->server->getStopEvent();
            $stopEvent->fd  = $fd;
            //执行停止事件
            $this->server->trigger(server::EVENT_RSK_STOP,$stopEvent);
            $this->server->removeConnect($fd);
            return true;
        }

        return true;

    }


}
