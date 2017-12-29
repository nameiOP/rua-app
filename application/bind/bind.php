<?php


/*******************************
 *
 * 系统事件触发后的操作
 * 此文件在 application构造器中引入,负责系统事件的响应处理。
 *
 * [应用类事件] 在application执行周期内触发:
 *      1:EVENT_APP_BEGIN
 *      2:EVENT_APP_INIT
 *      3:EVENT_APP_RUN
 *      4:EVENT_APP_END
 *
 * [终端类事件] 在执行终端命令时触发:
 *      1:EVENT_CMD_START
 *      2:EVENT_CMD_RESTART
 *      3:EVENT_CMD_STOP
 *
 * ----------------------------------------------------
 *  事件类型 |      事件名称      |  触发对象  |  触发条件
 * ----------------------------------------------------
 *   app    | EVENT_APP_BEGIN   |
 * ----------------------------------------------------
 *   app    | EVENT_APP_INIT    |
 * ----------------------------------------------------
 *   app    | EVENT_APP_RUN     |
 * ----------------------------------------------------
 *   app    | EVENT_APP_END     |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_START   |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_RESTART |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_STOP    |
 *
 *
 *********************************/





/**
 * 注册终端事件
 * php ./bin/start.php
 */
Builder::$app->get('command')->on(EVENT_CMD_START,function($event) {


    //1:启动服务器,阻塞
    php_exec(Builder::getAlias(Builder::$app->exec));


});






/**
 * 注册终端事件(由终端操作触发)
 * php ./bin/stop.php
 */
Builder::$app->get('command')->on(EVENT_CMD_STOP,function($event){

    //守护进程,通过pid文件关闭

});






/**
 * 注册终端事件(由终端操作触发)
 * php ./bin/restart.php
 */
Builder::$app->get('command')->on(EVENT_CMD_RESTART,function($event){

    //守护进程,通过pid文件重启
});








/**
 * 注册应用启动事件
 * app->run()
 */
Builder::$app->on(EVENT_APP_RUN,function(){
    console('hook event_app_run');
});




