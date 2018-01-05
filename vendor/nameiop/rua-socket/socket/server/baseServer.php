<?php
namespace rsk\server;

use rsk\event\acceptEvent;
use rsk\event\startEvent;
use rua\traits\eventable;
use rua\traits\macroable;
use rsk\traits\socketable;
use rsk\event\stopEvent;
use rsk\event\receiveEvent;
use rsk\traits\streamsocketable;



/**
 * Class server
 * @package server
 *
 *
 * 服务器消息由轮训或事件触发自动接收，采用自定义协议处理数据包，通过回调通知应用程序
 *
 */
abstract class baseServer {



	//use streamsocketable,macroable,eventable;
	use socketable,macroable,eventable;


	/**
	 * 服务器启动
	 * @var string
	 */
	const EVENT_RSK_START = 'rua_server_start';


	/**
	 * 服务器重启
	 * @var string
	 */
	const EVENT_RKS_RESTART = 'rua_server_restart';


	/**
	 * 服务器连接
	 * @var string
	 */
	const EVENT_RSK_ACCEPT = 'rua_server_accept';


	/**
	 * 接收消息
	 * @var string
	 */
	const EVENT_RSK_RECEIVE = 'rua_server_receive';

	/**
	 * 服务器关闭
	 * @var string
	 */
	const EVENT_RSK_STOP = 'rua_server_stop';


	//接受连接事件
	private $acceptEvent = null;

	//读取消息事件
	private $receiveEvent = null;

	//关闭socket事件
	private $stopEvent = null;





	/**
	 * 客户端连接集合
	 * @var \rua\helpers\collection|null
	 */
	protected $connectQueues = null;





	/**
	 * socket集合
	 * @var array
	 */
	protected $_socket = [];




	/**
	 * 主机
	 * @var string
	 */
	public $host = '';



	/**
	 * 端口
	 * @var int
	 */
	public $port = 0;



    /**
     * 启动服务器
     * @author liu.bin 2017/9/27 14:56
     */
    public function start(){


		/**
		 * 创建 socket
		 */
        $result = $this->createSocket($this->host,$this->port);
		if(false === $result){
            throw new \Exception('socket error');
		}




        //初始化服务器信息
        $this->init();




        //展示ui
        $this->displayUI();




		//添加队列,用于循环侦听
		$this->addConnect($this,$this->fd);




		//触发事件
		$startEvent = new startEvent();
		$startEvent->server = $this;
		$this->trigger(self::EVENT_RSK_START,$startEvent);



        //io模型
		\Builder::$app->get('io')->run();

	}



	/**
	 * 接收客户端连接
	 * @param resource $socket master-socket
	 * @return \rsk\server\connect|bool
	 */
	public function accept($socket=null){

		$mSocket = is_null($socket) ? $this->socket : $socket;

		$socket = $this->socketAccept($mSocket);
		if(!$socket){
			return false;
		}

		//创建连接
		$conn = new connect($socket);
		if($conn->getStatus()){
			if($this->addConnect($conn,$conn->getFd())){


				//server 执行触发事件 [连接事件]
				$acceptEvent = $this->getAcceptEvent();
				$acceptEvent->fd = $conn->getFd();
				$this->trigger(self::EVENT_RSK_ACCEPT,$acceptEvent);

				return $conn;
			}
		}

		//注销socket
		$this->socketClose($socket);
		unset($conn);
		return false;
	}


	/**
	 * 发送消息
	 * @param int $fd socket id
	 * @param string $data
	 * @return int
	 */
	public function send($fd,$data){
		$socket = $this->getConnSocket($fd);
		return $this->socketSend($socket,$data);
	}


	/**
	 * 关闭连接
	 * @param int $fd socket id
	 * @return bool
	 */
	public function close($fd){
		return $this->removeConnect($fd);
	}






	/**
	 * 接受连接事件
	 */
	public function getAcceptEvent(){
		if(is_null($this->acceptEvent)){
			$this->acceptEvent = new acceptEvent();
			$this->acceptEvent->server = $this;
		}
		$this->acceptEvent->fd = 0;
		return $this->acceptEvent;
	}


	/**
	 * 接收消息事件
	 * @return receiveEvent
	 */
	public function getReceiveEvent(){

		if(is_null($this->receiveEvent)){
			$this->receiveEvent = new receiveEvent();
			$this->receiveEvent->server = $this;
		}
		$this->receiveEvent->fd = 0;
		return $this->receiveEvent;
	}


	/**
	 * socket停止事件
	 */
	public function getStopEvent(){

		if(is_null($this->stopEvent)){
			$this->stopEvent = new stopEvent();
			$this->stopEvent->server = $this;
		}
		$this->stopEvent->fd = 0;
		return $this->stopEvent;
	}





	/**
	 * todo
	 * 初始化init
	 * @author liu.bin 2017/10/31 11:54
	 */
	abstract public function init();


	/**
	 * 启动后界面
	 */
	abstract public function displayUI();


	/**
	 * 添加客户端连接
	 * @param $conn
	 * @param $fd
	 * @return mixed
	 */
	abstract public function addConnect($conn,$fd);



	/**
	 * 移除客户端连接
	 * @param $fd
	 * @return bool
	 */
	abstract public function removeConnect($fd);




	/**
	 * 获取客户端连接
	 * @param $fd
	 * @return bool|object
	 */
	abstract public function getConnect($fd);





	/**
	 * 客户端是否存在
	 * @param $fd
	 * @return bool|object
	 */
	abstract public function hasConnect($fd);




	/**
	 * 获取 客户端 socket
	 * @param int $fd
	 * @return array|bool|resource
	 */
	abstract public function getConnSocket($fd=0);

}
