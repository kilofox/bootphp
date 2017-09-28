<?php

namespace Bootphp\Config\File;

/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Bootphp_Config].
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Reader implements \Bootphp\Config\Reader
{
    /**
     * The directory where config files are located
     * @var string
     */
    protected $_directory = '';

    /**
     * Creates a new file reader using the given directory as a config source
     *
     * @param string    $directory  Configuration directory to search
     */
    public function __construct($directory = 'config')
    {
        // Set the configuration directory name
        $this->_directory = trim($directory, '/');
    }

    /**
     * Load and merge all of the configuration files in this group.
     *
     *     $config->load($name);
     *
     * @param   string  $group  configuration group name
     * @return  $this   current object
     * @uses    Core::load
     */
    public function load($group)
    {
        $config = [];

        if ($files = Core::find_file($this->_directory, $group, null, true)) {
            foreach ($files as $file) {
                // Merge each file to the configuration array
                $config = Arr::merge($config, Core::load($file));
            }
        }

        return $config;
    }

}
