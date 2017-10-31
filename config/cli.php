<?php

	$config = [
		
	
	
		//项目跟目录
		'rootPath'=>dirname(__DIR__),	
		
		
		'bricks'=>[


			'command'=>[
				'class'=>'rua\bricks\command',
			],



			'console'=>[
				'class'=>'rua\bricks\command',
			],

		],


	];
	
	
	return $config;
