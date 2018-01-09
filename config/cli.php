<?php

	$config = [
		
	
	
		//项目跟目录
		'rootPath'=>dirname(__DIR__),


		//服务器启动脚本
		'server_exec'=>'@app/exec/server.php',


		//客户端启动脚本
		'client_exec'=>'@app/exec/client.php',




		/**
		 * bricks
		 * 在 application(house) 初始化的时候,通过魔术方法 __set(),自动调用 setBricks(),将组件注册进 application->_definitions中
		 * 通过 application->get(id) 方式获取组件对象
		 */
		'bricks'=>[


			/**
			 * 终端事件触发器
			 */
			'command'=>[
				'class'=>'rua\bricks\command',
			],


			/**
			 * 调试输出
			 */
			'console'=>[
				'class'=>'rua\bricks\console',
			],




			/**
			 * io模型
			 */
			'io'=>[
				'class'	=>'rsk\io\ioSelect',
				//'class'		=>'rsk\io\ioLoop',
				//'time_out'	=> null,
			],


			/**
			 * 协议类型
			 */
			'protocol'=>[
				'name'=>'eof',
				'class'=>'rsk\protocol\server\eof',
				'bufferSize'=>10,//http:65535
				'maxReadLength'=>50,//http:10485760
			],


			/**
			 * 服务器配置信息
			 */

			'server'=>[
				'class'=>'rsk\server\server',
				'host'=>'127.0.0.1',
				'port'=>8000,
				'maxConnectLength'=> 5,
			],


			/**
			 *客户端 配置信息
			 */
			'client'=>[
				'class'=>'rsk\client\client',
				'host'=>'127.0.0.1',
				'port'=>8000
			],


		],


	];
	
	
	return $config;
