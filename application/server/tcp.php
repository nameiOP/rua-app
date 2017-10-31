<?php

namespace app\server;


use rsk\server\server;

/**
* 
*/
class tcp extends server
{
	


	/**
	 * 配置
	 *
	 */
	public function setServer()
	{
		return [
			'protocol'=>'tcp',
			'ip'=>'0.0.0.0',
			'port'=>8000,
		];
	}



	/**
	 * 连接时调用
	 * 
	 */
	public function onConnect()
	{

	}



	/**
	 * 关闭时调用
	 *
	 */
	public function onClose()
	{

	}


	/**
	 * run
	 *
	 */
	public function run(){

	}
}

