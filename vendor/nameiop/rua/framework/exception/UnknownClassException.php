<?php


namespace rua\exception;

/**
 * 未知类 抛出此异常
 *
 */
class UnknownClassException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Class';
    }
}
