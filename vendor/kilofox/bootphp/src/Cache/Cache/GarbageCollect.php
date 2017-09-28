<?php

/**
 * Garbage Collection interface for caches that have no GC methods
 * of their own, such as [Cache_File] and [Cache_Sqlite]. Memory based
 * cache systems clean their own caches periodically.
 *
 * @version    2.0
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
interface Bootphp_Cache_GarbageCollect
{
    /**
     * Garbage collection method that cleans any expired
     * cache entries from the cache.
     *
     * @return void
     */
    public function garbage_collect();
}
