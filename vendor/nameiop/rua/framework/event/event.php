<?php

/**
 * CMD 服务 开始事件
 */
defined('EVENT_CMD_SERVER_START') or define('EVENT_CMD_SERVER_START','event_cmd_server_start');


/**
 * CMD 服务 重启事件
 */
defined('EVENT_CMD_SERVER_RESTART') or define('EVENT_CMD_SERVER_RESTART','event_cmd_server_restart');



/**
 * CMD 服务 停止事件
 */
defined('EVENT_CMD_SERVER_STOP') or define('EVENT_CMD_SERVER_STOP','event_cmd_server_stop');



/**
 * CMD 客户端 开始事件
 */
defined('EVENT_CMD_CLIENT_START') or define('EVENT_CMD_CLIENT_START','event_cmd_client_start');


/**
 * CMD 客户端 重启事件
 */
defined('EVENT_CMD_CLIENT_RESTART') or define('EVENT_CMD_CLIENT_RESTART','event_cmd_client_restart');



/**
 * CMD 客户端 停止事件
 */
defined('EVENT_CMD_CLIENT_STOP') or define('EVENT_CMD_CLIENT_STOP','event_cmd_client_stop');





/**
 * APP  开始事件
 */
defined('EVENT_APP_BEGIN') or define('EVENT_APP_BEGIN','event_app_begin');



/**
 * 应用程序初始化
 */
defined('EVENT_APP_INIT') or define('EVENT_APP_INIT','event_app_init');



/**
 * 应用程序运行时
 */
defined('EVENT_APP_RUN') or define('EVENT_APP_RUN','event_app_run');



/**
 * 饮用程序结束
 */
defined('EVENT_APP_END') or define('EVENT_APP_END','event_app_end');