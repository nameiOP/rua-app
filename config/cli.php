<?php

	$config = [
		
	
	
		//项目跟目录
		'rootPath'=>dirname(__DIR__),


		//服务器启动脚本
		'exec'=>'@app/exec/server.php',



		/**
		 * bricks
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
			 * 服务器配置信息
			 */
			'server'=>[
				'class'=>'rsk\server\server',
				'host'=>'0.0.0.0',
				'protocol'=>'http',
				'protocolClass'=>'app\protocol\http',
				'port'=>8000
			],


			/**
			 *
			 */
			'client'=>[
				'class'=>'',
				'bin'=>'',
			],


		],


	];
	
	
	return $config;
