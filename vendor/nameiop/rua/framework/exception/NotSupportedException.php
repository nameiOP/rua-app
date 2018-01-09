<?php


namespace rua\exception;

/**
 * 访问不支持的功能，应该抛出此异常
 *
 */
class NotSupportedException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not Supported';
    }
}
