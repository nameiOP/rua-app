<?php


/****************************************************
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
 *      1:EVENT_CMD_SERVER_START
 *      2:EVENT_CMD_SERVER_RESTART
 *      3:EVENT_CMD_SERVER_STOP
 *      4:EVENT_CMD_CLIENT_START
 *      5:EVENT_CMD_CLIENT_RESTART
 *      6:EVENT_CMD_CLIENT_STOP
 *
 * ----------------------------------------------------
 *  事件类型 |      事件名称      |  触发对象  |  触发条件
 * ----------------------------------------------------
 *   app    |     EVENT_APP_BEGIN     |
 * ----------------------------------------------------
 *   app    |     EVENT_APP_INIT      |
 * ----------------------------------------------------
 *   app    |     EVENT_APP_RUN       |
 * ----------------------------------------------------
 *   app    |     EVENT_APP_END       |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_SERVER_START  |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_SERVER_RESTART |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_SERVER_STOP    |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_CLIENT_START   |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_CLIENT_RESTART |
 * ----------------------------------------------------
 *   cmd    | EVENT_CMD_CLIENT_STOP    |
 ******************************************************/






//========================server========================================
/**
 * 注册 服务端 终端事件
 */
Builder::$app->get('command')->on(EVENT_CMD_SERVER_START,function($event) {


    //1:启动服务器,阻塞
    php_exec(Builder::getAlias(Builder::$app->server_exec));


});






/**
 * 注册 服务端 终端事件(由终端操作触发)
 */
Builder::$app->get('command')->on(EVENT_CMD_SERVER_STOP,function($event){

    //守护进程,通过pid文件关闭

});






/**
 * 注册 服务端 终端事件(由终端操作触发)
 */
Builder::$app->get('command')->on(EVENT_CMD_SERVER_RESTART,function($event){

    //守护进程,通过pid文件重启
});






//=================客户端========================================================


/**
 * 注册 客户端 终端事件
 */
Builder::$app->get('command')->on(EVENT_CMD_CLIENT_START,function($event) {


    //1:启动客户端,阻塞
    php_exec(Builder::getAlias(Builder::$app->client_exec));


});






/**
 * 注册 客户端 终端事件(由终端操作触发)
 */
Builder::$app->get('command')->on(EVENT_CMD_CLIENT_STOP,function($event){

    //守护进程,通过pid文件关闭

});






/**
 * 注册 客户端 终端事件(由终端操作触发)
 */
Builder::$app->get('command')->on(EVENT_CMD_CLIENT_RESTART,function($event){

    //守护进程,通过pid文件重启
});






//=================application=======================================================

/**
 * 注册应用启动事件
 */
Builder::$app->on(EVENT_APP_RUN,function(){
    //console('[event_app_run]');
});




