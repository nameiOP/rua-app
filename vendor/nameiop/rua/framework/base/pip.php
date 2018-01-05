<?php
namespace rua\base;



/**
 * 管道：
 * 创建定义
 * 系统功能类
 * Class pip
 * @package rua\base
 */
class pip extends house {






    /**
     * 获取命令解析对象
     * @author liu.bin 2017/10/25 10:10
     */
    public function getCommand(){
        return $this->get('command');
    }




    /**
     * 调试输出
     * @author liu.bin 2017/10/25 16:28
     */
    public function getConsole(){
       return $this->get('console');
    }


    /**
     * hook
     */
    public function getHook(){
        return $this->get('hook');
    }



    /**
     * return Logger message logger
     */
    public static function getLogger()
    {

    }



    /**
     * Sets the logger object.
     * @param
     */
    public static function setLogger($logger)
    {

    }







    /**
     * Logs a trace message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public static function trace($message, $category = 'application')
    {

    }




    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public static function error($message, $category = 'application')
    {

    }





    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public static function warning($message, $category = 'application')
    {

    }




    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public static function info($message, $category = 'application')
    {

    }









    /**
     * Returns the public member variables of an object.
     * This method is provided such that we can get the public member variables of an object.
     * It is different from "get_object_vars()" because the latter will return private
     * and protected variables if it is called within the object itself.
     * @param object $object the object to be handled
     * @return array the public member variables of the object
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }



}
