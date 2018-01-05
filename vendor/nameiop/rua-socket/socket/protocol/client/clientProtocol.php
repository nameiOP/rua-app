<?php
namespace rsk\protocol\client;


use rsk\protocol\protocol;



abstract class clientProtocol extends protocol
{




    /**
     * 重置数据
     * @author liu.bin 2017/9/30 10:51
     */
    public function over()
    {
        $this->buffer = '';
        $this->readBuffer = '';
        $this->readLength = 0;
    }


}