<?php

namespace rua\exception;

/**
 * 设置一个无效参数的时候，抛出此异常
 *
 */
class InvalidParamException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Parameter';
    }
}
