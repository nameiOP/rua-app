<?php
namespace rsk\server;





/**
 * Class server
 * @package server
 *
 *
 * 服务器消息由轮训或事件触发自动接收，采用自定义协议处理数据包，通过回调通知应用程序
 *
 */
class server extends baseServer{



	//最大连接数
	public $maxConnectLength = 5;



	/**
	 * 启动后界面
	 */
	public function displayUI(){

		echo '==================================================='.PHP_EOL;
		echo '---------   PHP VERSION:' .PHP_VERSION .'           ----------'.PHP_EOL;
		echo '---------   rua socket version :0.0.1    ----------'.PHP_EOL;
		echo '---------   io model : '.\Builder::$app->get('io').'   ----------'.PHP_EOL;
		echo '---------   listener  '.\Builder::$app->get('protocol')->name .'://'.$this->host . ':' . $this->port . ' ----------'.PHP_EOL;
		echo '==================================================='.PHP_EOL;
		echo '---------   please ctrl+c to stop server ----------'.PHP_EOL;
		echo PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

	}





    /**
     * todo
     * 初始化init
     * @author liu.bin 2017/10/31 11:54
     */
    public function init(){



		//处理 socket信号
		$this->installSignal();

	}






	/**
	 * socket 信号处理
	 */
	public function installSignal(){


		/**
		 * 忽略 SIGPIPE 信号
		 *
		 * 该连接的写半部关闭(主动发送FIN包的TCP连接)。对这样的套接字的写操作将会产生SIGPIPE信号。
		 * 所以我们的网络程序基本都要自定义处理SIGPIPE信号。因为SIGPIPE信号的默认处理方式是程序退出。
		 * 服务器端socket主动关闭客户端连接时,继续send数据,则会产生该 SIGPIPE 信号。
		 */
		pcntl_signal(SIGPIPE, SIG_IGN, false);


	}





	/**
	 * 将客户端连接 加入队列
	 * @param $id int
	 * @param $conn \rsk\server\connect | \rsk\server\server
	 * @return bool
	 */
	public function addConnect($conn,$fd=0){



		//* 第一次表明由server对象初始化,不需要加入客户端集合
		if(is_null($this->connectQueues)){
			$this->connectQueues = collect();
			//* 将socket 加入集合
			$this->_socket[$fd] = $conn->getSocket();
			return true;
		}


		//验证集合是否最大列表
		if($this->connectQueues->count() >= $this->maxConnectLength){
			return false;
		}

		//* 将客户端连接加入队列
		$this->connectQueues->put($fd,$conn);

		//* 将socket 加入集合
		$this->_socket[$fd] = $conn->getSocket();
		return true;
	}




	/**
	 * 移除客户端连接
	 * @param $fd
	 * @return bool
	 */
	public function removeConnect($fd){


		//客户端连接是否存在
		if($this->connectQueues->has($fd)){

			$this->connectQueues->forget($fd);
			if(array_key_exists($fd,$this->_socket)){
				$this->socketClose($this->_socket[$fd]);
				unset($this->_socket[$fd]);
			}

			return true;
		}

		return false;
	}



	/**
	 * 获取客户端连接
	 * @param $fd
	 * @return bool|object
	 */
	public function getConnect($fd=0){

		//返回全部连接
		if(empty($fd)){
			return $this->connectQueues;
		}

		//客户端连接是否存在
		if($this->connectQueues->has($fd)){
			return $this->connectQueues->get($fd);
		}
		return false;
	}





	/**
	 * 客户端是否存在
	 */
	public function hasConnect($fd){
		return $this->connectQueues->has($fd);
	}




	/**
	 * 获取 客户端 socket
	 * @param int $fd
	 * @return array|bool|resource
	 */
	public function getConnSocket($fd=0){
		if(0 === $fd){
			return $this->_socket;
		}else{
			return array_key_exists($fd,$this->_socket) ? $this->_socket[$fd] : false;
		}
	}









}
