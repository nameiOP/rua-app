<?php


namespace rua\exception;

/**
 * 所有的通用异常
 *
 *
 */
class Exception extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Exception';
    }
}
