<?php

namespace rua\exception;

/**
 * 配置文件错误，应该抛出 InvalidConfigException
 *
 */
class InvalidConfigException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
