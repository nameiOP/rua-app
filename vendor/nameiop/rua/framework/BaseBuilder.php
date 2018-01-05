<?php




/**
 * This constant defines the framework installation directory.
 */
defined('RUA_PATH') or define('RUA_PATH', __DIR__);
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('RUA_DEBUG') or define('RUA_DEBUG', false);



/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('RUA_ENABLE_ERROR_HANDLER') or define('RUA_ENABLE_ERROR_HANDLER', true);




class BaseBuilder{







    /**
     * 建筑工人的图纸
     * class 映射关系
     * @var array
     */
    public static $classDrawing = [];






    /**
     * 砖头厂商的地址
     * 路径别名
     * @var array registered path aliases
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = ['@rua' => __DIR__];




    /**
     * 建筑工人的瓦刀
     * 容器ioc
     * @var rua\base\di\container the dependency injection (DI) container used by [[createObject()]].
     * You may use [[container::set()]] to set up the needed dependencies of classes and
     * their initial property values.
     * @see createObject()
     * @see Container
     */
    public static $container;




    /**
     * 管道
     * @var \rua\base\application
     */
    public static $app;





    /**
     * Returns a string representing the current version of the Yii framework.
     * @return string the version of Yii framework
     */
    public static function getVersion()
    {
        return '0.0.1';
    }





    /**
     * 通过@别名，获取真实目录
     * @param string $alias the alias to be translated.
     * @param bool $throwException whether to throw an exception if the given alias is invalid.
     * If this is false and an invalid alias is given, false will be returned by this method.
     * @return string|bool the path corresponding to the alias, false if the root alias is not previously registered.
     * @throws \rua\exception\InvalidParamException if the alias is invalid while $throwException is true.
     * @see setAlias()
     */
    public static function getAlias($alias, $throwException = true)
    {


        //比较$alias的第一个字符，如果没有@，直接返回
        if (strncmp($alias, '@', 1)) {
            return $alias;
        }


        //获取别名的下标root,以第一个/前的字符串作为下标root，如果没有/，则整个别名作为下标
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);


        if (isset(static::$aliases[$root])) {


            //只定义了一个下标root：@foo
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            }


            //如果定义了@foo 和 @foo/bar ，都以@foo为下标存储
            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return $path . substr($alias, strlen($name));
                }
            }
        }


        //没有定义别名的话，直接抛出异常
        if ($throwException) {
            throw new \rua\exception\InvalidParamException("Invalid path alias: $alias");
        }

        return false;
    }




    /**
     * 设置一个路径别名
     *
     *
     * @param string $alias the alias name (e.g. "@yii"). It must start with a '@' character.
     * It may contain the forward slash '/' which serves as boundary character when performing
     * alias translation by [[getAlias()]].
     * @param string $path the path corresponding to the alias. If this is null, the alias will
     * be removed. Trailing '/' and '\' characters will be trimmed. This can be
     *
     * @throws \rua\exception\InvalidParamException if $path is an invalid alias.
     * @see getAlias()
     */
    public static function setAlias($alias, $path)
    {


        //别名第一个字符是否有@,如果没有，自动添加
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }




        //设置别名下标
        //以第一个/前的字符串作为下标root，如果没有/，则整个别名作为下标
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);




        if ($path !== null) {

            //判断$path是否为别名，支持别名叠别名
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);


            if (!isset(static::$aliases[$root])) {



                if ($pos === false) {

                    //@foo : foo

                    //[alias]
                    //foo->foo

                    static::$aliases[$root] = $path;
                } else {


                    //@foo : tem/foo
                    //@foo/bar : foo/bar

                    //[alias]
                    // foo->[
                    //    foo=>tem/foo
                    //    foo/bar=>foo/var
                    //]

                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {

            //删除别名
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }


        }
    }





    /**
     * 自动加载
     * @param string $className the fully qualified class name without a leading backslash "\"
     * @throws \rua\exception\UnknownClassException if the class does not exist in the class file
     */
    public static function autoload($className)
    {
        if (isset(static::$classDrawing[$className])) {
            $classFile = static::$classDrawing[$className];
            if ($classFile[0] === '@') {
                $classFile = static::getAlias($classFile);
            }
        } elseif (strpos($className, '\\') !== false) {
            $classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include($classFile);

        if (RUA_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new \rua\exception\UnknownClassException("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }








    /**
     * 建工工人的技能：盖房子
     *
     * Below are some usage examples:
     *
     * ```php
     * // create an object using a class name
     * $object = Yii::createObject('yii\db\Connection');
     *
     * // create an object using a configuration array
     * $object = Yii::createObject([
     *     'class' => 'yii\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // create an object with two constructor parameters
     * $object = \Yii::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * Using [[\yii\di\Container|dependency injection container]], this method can also identify
     * dependent objects, instantiate them and inject them into the newly created object.
     *
     * @param string|array|callable $type the object type. This can be specified in one of the following forms:
     *
     * - a string: representing the class name of the object to be created
     * - a configuration array: the array must contain a `class` element which is treated as the object class,
     *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
     * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
     *   The callable should return a new instance of the object being created.
     *
     * @param array $params the constructor parameters
     * @return object the created object
     * @throws \rua\exception\InvalidConfigException if the configuration is invalid.
     * @see \rua\base\di\container
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return static::$container->invoke($type, $params);
        } elseif (is_array($type)) {
            throw new \rua\exception\InvalidConfigException('Object configuration must be an array containing a "class" element.');
        }

        throw new \rua\exception\InvalidConfigException('Unsupported configuration type: ' . gettype($type));
    }




    /**
     * 初始化 对象 的属性
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }




    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\yii\i18n\I18N::translate()]].
     *
     * The translation will be conducted according to the message category and the target language will be used.
     *
     * You can add parameters to a translation message that will be substituted with the corresponding value after
     * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
     *
     * ```php
     * $username = 'Alexander';
     * echo \Yii::t('app', 'Hello, {username}!', ['username' => $username]);
     * ```
     *
     * Further formatting of message parameters is supported using the [PHP intl extensions](http://www.php.net/manual/en/intro.intl.php)
     * message formatter. See [[\yii\i18n\I18N::translate()]] for more details.
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null)
    {

    }



}
