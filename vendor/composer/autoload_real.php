<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitcd57d8de34d4fdf9159f2c9baa4605ea
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitcd57d8de34d4fdf9159f2c9baa4605ea', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitcd57d8de34d4fdf9159f2c9baa4605ea', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitcd57d8de34d4fdf9159f2c9baa4605ea::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
