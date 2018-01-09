<?php

namespace rua\base;

use Builder;
use Closure;
use rua\exception\InvalidConfigException;


/**
 * 高楼大厦类
 *
 * 功能类比作砖头，所有的功能类组成一个大厦，大厦通过id，找到砖头
 *
 * Class house
 * @package rua\base
 */
class house extends brick
{
    /**
     * @var array 砖头，可以通过id访问
     */
    private $_brick = [];



    /**
     * @var array 砖头定义，可以通过id访问
     */
    private $_definitions = [];




    /**
     * Getter 魔术方法，获取一个不存在的属性的时候调用
     * 当属性不存在的时候，可以通过 $name 获取同名的 brick
     * @param string $name brick or property name
     * @return mixed the named property value
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        } else {
            return parent::__get($name);
        }
    }




    /**
     * 检查属性是否存在，当调用isset()方法判断对象属性是否存在时调用
     * @param string $name the property name or the event name
     * @return bool whether the property value is null
     */
    public function __isset($name)
    {
        if ($this->has($name)) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }



    /**
     * 检查砖头是否已经定义，$checkInstance如果是真，则验证砖头是否已被实例化
     * @param string $id brick ID (e.g. `db`).
     * @param bool $checkInstance whether the method should check if the brick is shared and instantiated.
     * @return bool whether the locator has the specified brick definition or has instantiated the brick.
     * @see set()
     */
    public function has($id, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_brick[$id]) : isset($this->_definitions[$id]);
    }



    /**
     * 通过 id 获取砖头
     * @param string $id brick ID (e.g. `db`).
     * @param bool $throwException whether to throw an exception if `$id` is not registered with the locator before.
     * @return object|null the brick of the specified ID. If `$throwException` is false and `$id`
     * is not registered before, null will be returned.
     * @throws InvalidConfigException if `$id` refers to a nonexistent brick ID
     * @see has()
     * @see set()
     */
    public function get($id, $throwException = true)
    {
        if (isset($this->_brick[$id])) {
            return $this->_brick[$id];
        }

        if (isset($this->_definitions[$id])) {
            $definition = $this->_definitions[$id];
            if (is_object($definition) && !$definition instanceof Closure) {
                return $this->_brick[$id] = $definition;
            } else {
                return $this->_brick[$id] = Builder::createObject($definition);
            }
        } elseif ($throwException) {
            throw new InvalidConfigException("Unknown brick ID: $id");
        } else {
            return null;
        }
    }





    /**
     * 定义一个砖头
     *
     * For example,
     *
     * ```php
     * // a class name
     * $app->set('cache', 'rua\caching\FileCache');
     *
     * // a configuration array
     * $locator->set('db', [
     *     'class' => 'rua\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // an anonymous function
     * $app->set('cache', function ($params) {
     *     return new \rua\caching\FileCache;
     * });
     *
     * // an instance
     * $app->set('cache', new \rua\caching\FileCache);
     * ```
     *
     * If a brick definition with the same ID already exists, it will be overwritten.
     *
     * @param string $id brick ID (e.g. `db`).
     * @param mixed $definition the brick definition to be registered with this locator.
     * It can be one of the following:
     *
     * - a class name
     * - a configuration array: the array contains name-value pairs that will be used to
     *   initialize the property values of the newly created object when [[get()]] is called.
     *   The `class` element is required and stands for the the class of the object to be created.
     * - a PHP callable: either an anonymous function or an array representing a class method (e.g. `['Foo', 'bar']`).
     *   The callable will be called by [[get()]] to return an object associated with the specified brick ID.
     * - an object: When [[get()]] is called, this object will be returned.
     *
     * @throws InvalidConfigException if the definition is an invalid configuration array
     */
    public function set($id, $definition)
    {
        unset($this->_brick[$id]);

        if ($definition === null) {
            unset($this->_definitions[$id]);
            return;
        }

        if (is_object($definition) || is_callable($definition, true)) {
            // an object, a class name, or a PHP callable
            $this->_definitions[$id] = $definition;
        } elseif (is_array($definition)) {
            // a configuration array
            if (isset($definition['class'])) {
                $this->_definitions[$id] = $definition;
            } else {
                throw new InvalidConfigException("The configuration for the \"$id\" brick must contain a \"class\" element.");
            }
        } else {
            throw new InvalidConfigException("Unexpected configuration type for the \"$id\" brick: " . gettype($definition));
        }
    }

    /**
     * Removes the brick from the locator.
     * @param string $id the brick ID
     */
    public function clear($id)
    {
        unset($this->_definitions[$id], $this->_brick[$id]);
    }

    /**
     * Returns the list of the brick definitions or the loaded brick instances.
     * @param bool $returnDefinitions whether to return brick definitions instead of the loaded brick instances.
     * @return array the list of the brick definitions or the loaded brick instances (ID => definition or instance).
     */
    public function getBricks($returnDefinitions = true)
    {
        return $returnDefinitions ? $this->_definitions : $this->_brick;
    }

    /**
     * Registers a set of brick definitions in this locator.
     *
     * This is the bulk version of [[set()]]. The parameter should be an array
     * whose keys are brick IDs and values the corresponding brick definitions.
     *
     * For more details on how to specify brick IDs and definitions, please refer to [[set()]].
     *
     * If a brick definition with the same ID already exists, it will be overwritten.
     *
     * The following is an example for registering two brick definitions:
     *
     * ```php
     * [
     *     'db' => [
     *         'class' => 'rua\db\Connection',
     *         'dsn' => 'sqlite:path/to/file.db',
     *     ],
     *     'cache' => [
     *         'class' => 'rua\caching\DbCache',
     *         'db' => 'db',
     *     ],
     * ]
     * ```
     *
     * @param array $bricks brick definitions or instances
     */
    public function setBricks($bricks)
    {
        foreach ($bricks as $id => $brick) {
            $this->set($id, $brick);
        }
    }
}
