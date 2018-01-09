<?php

namespace rsk\base;


/**
 * Class systemCall
 * @package rsk\base
 */
class systemCall {




    protected $callback;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }






    public function __invoke(Task $task, Scheduler $scheduler) {
        $callback = $this->callback;
        return $callback($task, $scheduler);
    }
}