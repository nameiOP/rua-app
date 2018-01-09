<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf9817e0a2e1fe983cbeb4f5fa736eb91
{
    public static $files = array (
        '3cde2558e3d03f3a6eeb599234da4ac5' => __DIR__ . '/..' . '/nameiop/rua/framework/helpers/helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'r' => 
        array (
            'rua\\' => 4,
            'rsk\\' => 4,
        ),
        'p' => 
        array (
            'pfork\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'rua\\' => 
        array (
            0 => __DIR__ . '/..' . '/nameiop/rua/framework',
        ),
        'rsk\\' => 
        array (
            0 => __DIR__ . '/..' . '/nameiop/rua-socket/socket',
        ),
        'pfork\\' => 
        array (
            0 => __DIR__ . '/..' . '/nameiop/rua-fork/fork',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf9817e0a2e1fe983cbeb4f5fa736eb91::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf9817e0a2e1fe983cbeb4f5fa736eb91::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}