<?php

namespace rsk\io;



use rua\able\loopable;
use rua\able\runnable;

abstract class loop implements loopable,runnable{


    /**
     * server对象
     * @var \rsk\server\server;
     */
    protected $server;



    /**
     * 打印类名称
     * @return string
     */
    public function __toString() {
        return get_called_class();
    }


}
