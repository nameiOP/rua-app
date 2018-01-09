<?php


defined('RUA_DEBUG') or define('RUA_DEBUG',true);
defined('RUA_ENV') or define('RUA_ENV','dev');
defined('RUA_ENV_DEV') or define('RUA_ENV_DEV', true);

//引入composer
require(__DIR__ . '/../vendor/autoload.php');

//引入框架文件
require(__DIR__ . '/../vendor/nameiop/rua/framework/Builder.php');
require(__DIR__ . '/../vendor/nameiop/rua/framework/event/event.php');


date_default_timezone_set('Asia/Shanghai');

//运行
$config = require(__DIR__ . '/../config/cli.php');
(new rua\cli\app($config))->run('client');
