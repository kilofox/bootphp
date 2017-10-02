<?php

/**
 * Auto-loader initialization.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class AutoloaderInit
{
    private static $loader;
    private static $map = [
        'Bootphp\\' => array(__DIR__ . '/kilofox/bootphp/src'),
        'App\\' => array(__DIR__ . '/../src'),
    ];

    public static function loadClassLoader($class)
    {
        if ('Bootphp\ClassLoader\ClassLoader' === $class) {
            require __DIR__ . '/kilofox/bootphp/src/ClassLoader/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(['AutoloaderInit', 'loadClassLoader'], true, true);
        self::$loader = $loader = new \Bootphp\ClassLoader\ClassLoader();
        spl_autoload_unregister(['AutoloaderInit', 'loadClassLoader']);

        foreach (self::$map as $namespace => $path) {
            $loader->setPsr4($namespace, $path);
        }

        $loader->register(true);

        return $loader;
    }

}

function composerRequire($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        require $file;

        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
    }
}

return AutoloaderInit::getLoader();
