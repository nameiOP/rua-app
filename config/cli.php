<?php

	$config = [
		
	
	
		//项目跟目录
		'rootPath'=>dirname(__DIR__),



		/**
		 * dists
		 * 在 application(dist) 初始化的时候,通过魔术方法 __set(),自动调用 setDists(),将组件注册进 application->_definitions中
		 * 通过 application->get(id) 方式获取组件对象
		 */
		'dists'=>[



			/**
			 * 调试输出
			 */
			'console'=>[
				'class'=>'rua\dists\console',
			],




			/**
			 * io模型
			 */
			'io'=>[
				'class'			=> 'rsk\io\adapter\ioSelectAdapter',
				'time_out'		=> null,
				'buffer_size'	=> 65535
			],



			/**
			 * 服务器配置信息
			 */
			'server'=>[
				'class'				=> 'rsk\server\server',
                'server'			=> ['tcp://127.0.0.1:8000'],
			],


		],


	];
	
	
	return $config;
