<?php

namespace rsk\base;

use SplQueue;
use Generator;
use rua\able\runnable;


/**
 * 任务调度器
 * Class scheduler
 */
class scheduler implements runnable {


    /**
     * 任务编号
     * @var int
     */
    protected $maxTaskId = 0;


    /**
     * 任务集合
     * @var array
     */
    protected $taskMap = []; // taskId => task


    /**
     * 任务队列
     * @var SplQueue
     */
    protected $taskQueue;


    /**
     * 构造器
     * scheduler constructor.
     */
    public function __construct() {
        $this->taskQueue = new SplQueue();
    }


    /**
     * 生成任务
     * @param Generator $coroutine
     * @return int
     * @author liu.bin 2017/10/26 15:39
     */
    public function buildTask(Generator $coroutine) {

        $tid = ++$this->maxTaskId;
        $task = new task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }


    /**
     * 将任务添加到队列
     * @param Task $task
     * @author liu.bin 2017/10/26 15:46
     */
    public function schedule(Task $task) {
        $this->taskQueue->enqueue($task);
    }


    /**
     * 调度器 run
     * @author liu.bin 2017/10/26 15:46
     */
    public function run() {



        while (!$this->taskQueue->isEmpty()) {

            //任务出列
            $task = $this->taskQueue->dequeue();
            $task->run();


            if ($task->isFinished()) {

                //任务执行结束，出列
                unset($this->taskMap[$task->getTaskId()]);

            } else {

                //没有执行结束，继续入列
                $this->schedule($task);
            }

        }


    }
}