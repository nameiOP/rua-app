<?php

namespace rua\base;

use Builder;
use rua\traits\eventable;
use rua\traits\macroable;


/**
 * 砖头类
 * Class brick
 * @package rua\base
 */
class brick extends object
{

    use macroable,eventable;




    /**
     * 已加载的类
     * @var array
     */
    public $loadedClass = [];


    /**
     * 通过 getInstance 静态方法，可以从任何地方访问当前类的实例
     * 前提：application曾经实例化过，并且把application实例 通过setInstance($app)注入
     *
     * 使用方法：
     * application::getInstance()
     *
     * 返回：application对象
     *
     * @see setInstance()
     * @return static|null the currently requested instance of this module class, or `null` if the module class is not requested.
     */
    public static function getInstance()
    {
        $class = get_called_class();
        return isset(Builder::$app->loadedClass[$class]) ? Builder::$app->loadedClass[$class] : null;
    }





    /**
     * 设置实例到一个静态方法中，可以直接通过application::getInstance()方式获取
     *
     * get_class(): 获取当前调用方法的类名；在父类中定义，返回父类（如果实例化的是子类，仍然返回父类）
     * get_called_class():获取静态绑定后的类名；在父类中定义，返回实例化的类（如果实例化的是子类，返回子类）
     *
     * @see getInstance()
     * @param Application|null $instance 实例
     */
    public static function setInstance($instance)
    {
        if ($instance === null) {
            unset(Builder::$app->loadedClass[get_called_class()]);
        } else {
            Builder::$app->loadedClass[get_class($instance)] = $instance;
        }
    }


}
