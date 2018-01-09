<?php
namespace rsk\client;




use rua\traits\eventable;
use rua\traits\macroable;
use rsk\traits\socketable;
use rsk\traits\streamsocketable;
use rsk\event\client\startEvent;
use rsk\event\client\connectEvent;


/**
 * Class server
 * @package server
 *
 */
abstract class baseClient {



	//use streamsocketable,macroable,eventable;
	use socketable,macroable,eventable;




	//客户端启动事件
	const EVENT_RSK_START = 'event_rsk_start';

	//客户端连接成功事件
	const EVENT_RSK_CONNECT = 'event_rsk_connect';

	//接收服务端消息事件
	const EVENT_RSK_RECEIVE = 'event_rsk_receive';

	//客户端停止事件
	const EVENT_RSK_STOP = 'event_rsk_stop';


	/**
	 * 接连服务器
	 * @var string
	 */
	public $host = '';


	/**
	 * 连接端口
	 * @var int
	 */
	public $port = 0;





    /**
     * 启动客户端
     * @author liu.bin 2017/9/27 14:56
     */
    public function start()
	{

		/**
		 * 创建 socket
		 */
		$result = $this->createSocket();
		if(false === $result){
			throw new \Exception('createSocket error');
		}


		//触发事件
		$startEvent = new startEvent();
		$startEvent->client = $this;
		$this->trigger(self::EVENT_RSK_START,$startEvent);



		//连接服务器
		$result = $this->socketConnect($this->host,$this->port);
		if(false === $result){
			throw new \Exception('socketConnect error');
		}



		//初始化服务器信息
		$this->init();




		//展示ui
		$this->displayUI();


		//触发连接事件
		$connEvent = new connectEvent();
		$connEvent->client = $this;
		$this->trigger(self::EVENT_RSK_CONNECT,$connEvent);


	}









	/**
	 * 发送消息到服务端
	 * @param string $data
	 * @author liu.bin 2017/9/27 15:02
	 */
	public function send($data){
		$this->socketSend($this->socket,$data);
	}







	/**
	 * 客户端接收服务端消息: socket_read
	 * @param resource|null $socket 客户端socket
	 * @return string
	 * @author liu.bin 2017/9/29 16:59
	 */
	public function receive($socket=null){

		$socket = is_null($socket) ? $this->socket : $socket;
		return $this->socketRead($socket,1000);
	}



	/**
	 * 重启客户端
	 * @author liu.bin 2017/9/27 14:57
	 */
	public function reload(){

	}


	/**
	 * 关闭客户端
	 * @author liu.bin 2017/9/27 14:57
	 */
	public function stop(){

	}


	/**
	 * 关闭客户端
	 * @author liu.bin 2017/9/27 14:58
	 */
	public function shutdown(){

	}




	/**
	 * todo
	 * 初始化init
	 * @author liu.bin 2017/10/31 11:54
	 */
	abstract protected function init();


	/**
	 * 启动后界面
	 */
	abstract public function displayUI();


}
