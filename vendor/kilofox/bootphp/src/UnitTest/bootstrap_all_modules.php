<?php

include_once('bootstrap.php');

// Enable all modules we can find
$modules_iterator = new DirectoryIterator(MOD_PATH);

$modules = [];

foreach ($modules_iterator as $module) {
    if ($module->isDir() and ! $module->isDot()) {
        $modules[$module->getFilename()] = MOD_PATH . $module->getFilename();
    }
}

Core::modules(Core::modules() + $modules);

unset($modules_iterator, $modules, $module);
