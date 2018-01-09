<?php

namespace rsk\base;


use Generator;
use rua\able\runnable;


/**
 * 任务类
 * Class task
 * @package rsk\base
 */

class task implements runnable {


    /**
     * 任务编号
     * @var
     */
    protected $taskId;


    /**
     * 生成器
     * @var Generator
     */
    protected $coroutine;


    /**
     * sendValue
     * @var null
     */
    protected $sendValue = null;


    /**
     * 第一次current()
     * @var bool
     */
    protected $beforeFirstYield = true;



    /**
     * 构造器
     * task constructor.
     * @param $taskId
     * @param Generator $coroutine
     */
    public function __construct($taskId, Generator $coroutine) {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }


    /**
     * 获取任务编号
     * @return mixed
     * @author liu.bin 2017/10/26 15:44
     */
    public function getTaskId() {
        return $this->taskId;
    }


    /**
     * 设置 sendValue
     * @param $sendValue
     * @author liu.bin 2017/10/26 15:44
     */
    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }


    /**
     * 任务运行
     * @return mixed
     * @author liu.bin 2017/10/26 15:44
     */
    public function run() {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = null;
            return $retval;
        }
    }


    /**
     * 任务结束
     * @return bool
     * @author liu.bin 2017/10/26 15:44
     */
    public function isFinished() {
        return !$this->coroutine->valid();
    }


}