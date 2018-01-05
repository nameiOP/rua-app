<?php


namespace rua\base;


use Builder;
use rua\able\runnable;
use rua\exception\ExitException;
use rua\exception\InvalidConfigException;





class application extends pip implements runnable{






    /**
     * 项目扩展配置
     * @var array
     */
    public $extensions = [];



    /**
     * 构造器
     * @param array $config
     * application constructor.
     */
    public function __construct($config=[])
    {
        Builder::$app = $this;
        $this->preInit($config);
        static::setInstance($this);
        parent::__construct($config);
        $this->addEventBind();
        $this->trigger(EVENT_APP_BEGIN);
    }





    /**
     * application 参数 预初始化
     * This method is called at the beginning of the application constructor.
     * It initializes several important application properties.
     * If you override this method, please make sure you call the parent implementation.
     * @param array $config the application configuration
     * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
     */
    public function preInit(&$config)
    {

        //项目根目录
        if (isset($config['rootPath'])) {
            $this->setRootPath($config['rootPath']);
            unset($config['rootPath']);
        } else {
            throw new InvalidConfigException('The "rootPath" configuration for the Application is required.');
        }

    }




    /**
     * 设置项目路径
     * This method can only be invoked at the beginning of the constructor.
     * @param string $path the root directory of the application.
     * @property string the root directory of the application.
     */
    public function setRootPath($path){
        Builder::setAlias('@root', $path);
    }



    /**
     * 设置application 目录
     * @param string
     * @author liu.bin 2017/10/25 11:37
     */
    public function setAppPath($path){
        Builder::setAlias('@app',$path);
    }




    /**
     * 初始化
     */
    public function init(){


        $this->trigger(EVENT_APP_INIT);
        //扩展信息配置
        foreach ($this->extensions as $extension) {
            if (!empty($extension['alias'])) {
                foreach ($extension['alias'] as $name => $path) {
                    Builder::setAlias($name, $path);
                }
            }
        }


        //application 应用目录配置
        $this->setAppPath('@root/application');
    }


    /**
     * 加载事件侦听器
     * @author liu.bin
     */
    public function addEventBind(){
        $eventBindFile = '@app/bind/bind.php';
        php_exec(Builder::getAlias($eventBindFile));
    }



    /**
     * 运行
     * @author liu.bin 2017/10/26 10:52
     */
    public function run($type='server')
    {
		try {

            $this->trigger(EVENT_APP_RUN);
            $this->handleCommand($type);
            $this->trigger(EVENT_APP_END);

        } catch (ExitException $e) {
            echo $e->statusCode;
            exit;
        }
        // TODO: Implement run() method.
    }






    /**
     * 处理命令
     * @author liu.bin 2017/10/24 17:12
     */
    public function handleCommand($type){}






}
