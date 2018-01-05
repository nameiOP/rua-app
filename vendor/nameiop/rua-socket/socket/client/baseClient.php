<?php
namespace rsk\server;


use rsk\io\ioFork;
use rsk\io\ioLoop;
use rsk\io\ioSelect;
use rsk\event\startEvent;
use rua\traits\eventable;
use rua\traits\macroable;
use rsk\traits\socketable;
use rsk\event\stopEvent;
use rsk\event\connectEvent;
use rsk\event\receiveEvent;

use rsk\traits\streamsocketable;



/**
 * Class server
 * @package server
 *
 */
abstract class baseClient {



	//use streamsocketable,macroable,eventable;
	use socketable,macroable,eventable;










    /**
     * 启动服务器
     * @author liu.bin 2017/9/27 14:56
     */
    public function start(){




	}










	/**
	 * 连接事件
	 */
	public function getConnectEvent(){
		if(is_null($this->connectEvent)){
			$this->connectEvent = new connectEvent();
			$this->connectEvent->server = $this;
		}
		$this->connectEvent->fd = 0;
		return $this->connectEvent;
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


}
