<?php

namespace rua\exception;

/**
 * 调用一个类的不存在的方法，应该抛出此异常
 */
class UnknownMethodException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Method';
    }
}
