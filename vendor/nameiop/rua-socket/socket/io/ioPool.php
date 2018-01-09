<?php

namespace rsk\loop;



use rsk\base\scheduler;

class ioPool extends loop {


    /**
     * 读集合
     * @var array
     */
    protected $waitingForRead = [];


    /**
     * 写集合
     * @var array
     */
    protected $waitingForWrite = [];




    /**
     * 轮询
     * @param $timeout
     * @author liu.bin 2017/10/26 15:17
     */
    public function loop($timeout=null)
    {














        $rSocks = [];
        foreach ($this->waitingForRead as list($socket)) {
            $rSocks[] = $socket;
        }



        $wSocks = [];
        foreach ($this->waitingForWrite as list($socket)) {
            $wSocks[] = $socket;
        }




        $eSocks = []; // dummy
        if (!stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
            return;
        }




        /**
         * 激活 接收客户端连接
         */
        foreach ($rSocks as $socket) {
            list(, $tasks) = $this->waitingForRead[(int) $socket];
            unset($this->waitingForRead[(int) $socket]);

            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }


        /**
         * 激活 发送客户端信息
         */
        foreach ($wSocks as $socket) {
            list(, $tasks) = $this->waitingForWrite[(int) $socket];
            unset($this->waitingForWrite[(int) $socket]);

            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }



    }


    /**
     * 读
     * @param $socket
     * @param Task $task
     * @author liu.bin 2017/10/26 15:19
     */
    private function waitForRead($socket, Task $task) {
        if (isset($this->waitingForRead[(int) $socket])) {
            $this->waitingForRead[(int) $socket][1][] = $task;
        } else {
            $this->waitingForRead[(int) $socket] = [$socket, [$task]];
        }
    }


    /**
     * 写
     * @param $socket
     * @param Task $task
     * @author liu.bin 2017/10/26 15:19
     */
    public function waitForWrite($socket, Task $task) {
        if (isset($this->waitingForWrite[(int) $socket])) {
            $this->waitingForWrite[(int) $socket][1][] = $task;
        } else {
            $this->waitingForWrite[(int) $socket] = [$socket, [$task]];
        }
    }


    /**
     * @author liu.bin 2017/10/26 15:54
     */
    public function test(){


        $scheduler = new scheduler();

        $scheduler->buildTask(task1());
        $scheduler->buildTask(task2());

        $scheduler->run();


    }





}