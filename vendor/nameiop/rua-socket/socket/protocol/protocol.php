<?php
namespace rsk\protocol;


/**
 * Class protocol
 * @package rsk\protocol
 *
 *
 * 协议抽象类
 *
 *
 */
abstract class protocol
{





    /**
     * @var string 协议名称
     */
    public $name = '';





	/**
     * 读取一次的缓冲区数据
     */
	protected $buffer = '';





    /**
     * 一次读取缓冲区数据大小
     * @var int
     */
	public $bufferSize = 10;







    /**
     * 已读取的buffer总数据
     * @var string
     */
	protected $readBuffer = '';




    /**
     * @var string 只有消息读取完成,该值才会被重置;
     */
    protected $_readBuffer = '';





    /**
     * 已读取buffer的长度
     * @var int
     */
    protected $readLength = 0;








    /**
     * 单包接收的输入长度
     * 因为tcp是数据流，如果定义的包的概念，在没有完整接收整个包的时候，会一直接收下去，造成内存泄漏
     */
	public $maxReadLength = 100;




    public $fd = 0;




	/**
	 * 构造器
	 */
	public function __construct(){

	}



    public function __toString(){
        return (string)$this->fd;
    }



    /**
     * 获取协议名称
     */
    public function getName(){
        return $this->name;
    }




    /**
     * 获取buffer size
     * @author liu.bin 2017/9/29 13:37
     */
	public function getBufferSize(){
	    return $this->bufferSize;
    }





    /**
     * 读取buffer
     * @author liu.bin 2017/9/29 14:42
     */
    public function getBuffer(){
        return $this->buffer;
    }





    /**
     * 返回已接收的数据
     * @author liu.bin 2017/9/30 10:08
     */
    public function getReadData(){
        $this->bufferRecovery();
        return $this->_readBuffer;
    }







    /**
     * 是否读取结束
     * @param string $buffer
     * @return bool
     * true:读取结束,消息完整;
     * false:消息未完整;
     * @author liu.bin 2017/9/29 14:37
     */
    abstract public function readEOF($buffer='');








    /**
     * 数据解包
     * @param $buffer string
     * @return string
     * */
    abstract public function decode($buffer);








    /**
     * 数据打包
     * @param $buffer string
     * @return string
     * */
    abstract public function encode($buffer);








    /**
     * 读取结束
     * @return mixed
     * @author liu.bin 2017/9/30 9:57
     */
    abstract public function bufferRecovery();

}