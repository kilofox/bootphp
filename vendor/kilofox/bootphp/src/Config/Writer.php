<?php

/**
 * Interface for config writers
 *
 * Specifies the methods that a config writer must implement
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
interface Bootphp_Config_Writer extends Bootphp_Config_Source
{
    /**
     * Writes the passed config for $group
     *
     * Returns chainable instance on success or throws
     * Bootphp_Config_Exception on failure
     *
     * @param string      $group  The config group
     * @param string      $key    The config key to write to
     * @param array       $config The configuration to write
     * @return boolean
     */
    public function write($group, $key, $config);
}
