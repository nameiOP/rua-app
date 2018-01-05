<?php

namespace rua\exception;




/**
 * ExitException 应用正常停止
 *
 * 如果不抛出此异常，将导致应用程序无法优雅的处理一些数据
 *
 * 在rua框架中需要退出的话，直接抛出 ExitException 即可，在捕获到ExitException后，rua框架将处理一些剩余操作后退出
 * 如果在业务逻辑中，直接使用exit或die，将导致rua框架无法完善后续操作
 *
 */
class ExitException extends \Exception
{
    /**
     * @var int the exit status code
     */
    public $statusCode;


    /**
     * Constructor.
     * @param int $status the exit status code
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}
