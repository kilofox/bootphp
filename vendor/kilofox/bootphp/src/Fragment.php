<?php

namespace Bootphp;

/**
 * View fragment caching. This is primarily used to cache small parts of a view
 * that rarely change. For instance, you may want to cache the footer of your
 * template because it has very little dynamic content. Or you could cache a
 * user profile page and delete the fragment when the user updates.
 *
 * For obvious reasons, fragment caching should not be applied to any
 * content that contains forms.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 * @uses        Core::cache
 */
class Fragment
{
    /**
     * @var  integer  default number of seconds to cache for
     */
    public static $lifetime = 30;

    /**
     * @var  boolean  use multilingual fragment support?
     */
    public static $i18n = false;

    /**
     * @var  array  list of buffer => cache key
     */
    protected static $_caches = [];

    /**
     * Generate the cache key name for a fragment.
     *
     *     $key = Fragment::_cache_key('footer', true);
     *
     * @param   string  $name   fragment name
     * @param   boolean $i18n   multilingual fragment support
     * @return  string
     * @uses    I18n::lang
     */
    protected static function _cache_key($name, $i18n = null)
    {
        if ($i18n === null) {
            // Use the default setting
            $i18n = Fragment::$i18n;
        }

        // Language prefix for cache key
        $i18n = ($i18n === true) ? I18n::lang() : '';

        // Note: $i18n and $name need to be delimited to prevent naming collisions
        return 'Fragment::cache(' . $i18n . '+' . $name . ')';
    }

    /**
     * Load a fragment from cache and display it. Multiple fragments can
     * be nested with different life times.
     *
     *     if ( ! Fragment::load('footer')) {
     *         // Anything that is echo'ed here will be saved
     *         Fragment::save();
     *     }
     *
     * @param   string  $name       fragment name
     * @param   integer $lifetime   fragment cache lifetime
     * @param   boolean $i18n       multilingual fragment support
     * @return  boolean
     */
    public static function load($name, $lifetime = null, $i18n = null)
    {
        // Set the cache lifetime
        $lifetime = ($lifetime === null) ? Fragment::$lifetime : (int) $lifetime;

        // Get the cache key name
        $cache_key = Fragment::_cache_key($name, $i18n);

        if ($fragment = Core::cache($cache_key, null, $lifetime)) {
            // Display the cached fragment now
            echo $fragment;

            return true;
        } else {
            // Start the output buffer
            ob_start();

            // Store the cache key by the buffer level
            Fragment::$_caches[ob_get_level()] = $cache_key;

            return false;
        }
    }

    /**
     * Saves the currently open fragment in the cache.
     *
     *     Fragment::save();
     *
     * @return  void
     */
    public static function save()
    {
        // Get the buffer level
        $level = ob_get_level();

        if (isset(Fragment::$_caches[$level])) {
            // Get the cache key based on the level
            $cache_key = Fragment::$_caches[$level];

            // Delete the cache key, we don't need it anymore
            unset(Fragment::$_caches[$level]);

            // Get the output buffer and display it at the same time
            $fragment = ob_get_flush();

            // Cache the fragment
            Core::cache($cache_key, $fragment);
        }
    }

    /**
     * Delete a cached fragment.
     *
     *     Fragment::delete($key);
     *
     * @param   string  $name   fragment name
     * @param   boolean $i18n   multilingual fragment support
     * @return  void
     */
    public static function delete($name, $i18n = null)
    {
        // Invalid the cache
        Core::cache(Fragment::_cache_key($name, $i18n), null, -3600);
    }

}
