<?php

namespace rua\exception;

/**
 * 调用错误的方法或方法不存在的时候，应抛出此异常
 *
 */
class InvalidCallException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Call';
    }
}
