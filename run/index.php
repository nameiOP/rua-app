<?php

defined('RUA_DEBUG') or define('RUA_DEBUG',true);
defined('RUA_ENV') or define('RUA_ENV','dev');
defined('RUA_ENV_DEV') or define('RUA_ENV_DEV', true);


//引入框架文件
require(__DIR__ . '/../vendor/rua/framework/Builder.php');


//run
$config = require(__DIR__ . '/../config/cli.php');
(new rua\cli\app($config))->run();
