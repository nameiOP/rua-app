<?php


namespace rua\exception;

/**
 * 访问一个不存在的属性，应该抛出此异常
 */
class UnknownPropertyException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Property';
    }
}
