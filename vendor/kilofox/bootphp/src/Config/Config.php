<?php

namespace Bootphp\Config;

use Bootphp\Config\Source;
use Bootphp\BootphpException;
use Bootphp\Arr;

/**
 * Wrapper for configuration arrays. Multiple configuration readers can be
 * attached to allow loading configuration from files, database, etc.
 *
 * Configuration directives cascade across config sources in the same way that
 * files cascade across the filesystem.
 *
 * Directives from sources high in the sources list will override ones from those
 * below them.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Config
{
    // Configuration readers
    protected $_sources = [];
    // Array of config groups
    protected $_groups = [];

    /**
     * Attach a configuration reader. By default, the reader will be added as
     * the first used reader. However, if the reader should be used only when
     * all other readers fail, use `false` for the second parameter.
     *
     *     $config->attach($reader);        // Try first
     *     $config->attach($reader, false); // Try last
     *
     * @param   \Bootphp\Config\Source    $source instance
     * @param   boolean                 $first  add the reader as the first used object
     * @return  $this
     */
    public function attach(Source $source, $first = true)
    {
        if ($first === true) {
            // Place the log reader at the top of the stack
            array_unshift($this->_sources, $source);
        } else {
            // Place the reader at the bottom of the stack
            $this->_sources[] = $source;
        }

        // Clear any cached _groups
        $this->_groups = [];

        return $this;
    }

    /**
     * Detach a configuration reader.
     *
     *     $config->detach($reader);
     *
     * @param   \Bootphp\Config\Source    $source instance
     * @return  $this
     */
    public function detach(Source $source)
    {
        if (($key = array_search($source, $this->_sources)) !== false) {
            // Remove the writer
            unset($this->_sources[$key]);
        }

        return $this;
    }

    /**
     * Load a configuration group. Searches all the config sources, merging all the
     * directives found into a single config group.  Any changes made to the config
     * in this group will be mirrored across all writable sources.
     *
     *     $array = $config->load($name);
     *
     * See [Bootphp\Config\Group] for more info.
     *
     * @param   string  $group  Configuration group name
     * @return  Bootphp\Config\Group
     * @throws  BootphpException
     */
    public function load($group)
    {
        if (!count($this->_sources)) {
            //throw new BootphpException('No configuration sources attached');
        }

        if (empty($group)) {
            throw new BootphpException('Need to specify a config group');
        }

        if (!is_string($group)) {
            throw new BootphpException('Config group must be a string');
        }

        if (isset($this->_groups[$group])) {
            return $this->_groups[$group];
        }

        $config = [];

        // We search from the "lowest" source and work our way up
        $sources = array_reverse($this->_sources);

        foreach ($sources as $source) {
            if ($source instanceof \Bootphp\Config\Reader) {
                if ($source_config = $source->load($group)) {
                    $config = Arr::merge($config, $source_config);
                }
            }
        }

        $this->_groups[$group] = new Group($this, $group, $config);

        return $this->_groups[$group];
    }

    /**
     * Copy one configuration group to all of the other writers.
     *
     *     $config->copy($name);
     *
     * @param   string  $group  configuration group name
     * @return  $this
     */
    public function copy($group)
    {
        // Load the configuration group
        $config = $this->load($group);

        foreach ($config->asArray() as $key => $value) {
            $this->_write_config($group, $key, $value);
        }

        return $this;
    }

    /**
     * Callback used by the config group to store changes made to configuration
     *
     * @param string    $group  Group name
     * @param string    $key    Variable name
     * @param mixed     $value  The new value
     * @return Bootphp_Config Chainable instance
     */
    public function _write_config($group, $key, $value)
    {
        foreach ($this->_sources as $source) {
            if (!($source instanceof \Bootphp\Config\Writer)) {
                continue;
            }

            // Copy each value in the config
            $source->write($group, $key, $value);
        }

        return $this;
    }

}
