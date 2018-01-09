<?php
namespace rua\exception;

/**
 * 方法返回的值类型错误，应该抛出 InvalidValueException
 *
 */
class InvalidValueException extends \UnexpectedValueException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Return Value';
    }
}
