<?php

namespace rua\base\di;

class instance{




    /**
     * 类唯一标示
     */
    public $id;

    /**
     * 构造函数
     * @param string $id 类唯一ID
     */
    public function __construct($id)
    {
        $this->id = $id;
    }





    /**
     * 获取类的实例
     * @param string $id 类唯一ID
     * @return instance
     */
    public static function getInstance($id)
    {
        return new self($id);
    }
}