<?php

/**
 * Bootphp Cache Tagging Interface
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
interface Bootphp_Cache_Tagging
{
    /**
     * Set a value based on an id. Optionally add tags.
     *
     * Note : Some caching engines do not support
     * tagging
     *
     * @param   string   $id        id
     * @param   mixed    $data      data
     * @param   integer  $lifetime  lifetime [Optional]
     * @param   array    $tags      tags [Optional]
     * @return  boolean
     */
    public function set_with_tags($id, $data, $lifetime = null, array $tags = null);
    /**
     * Delete cache entries based on a tag
     *
     * @param   string  $tag  tag
     */
    public function delete_tag($tag);
    /**
     * Find cache entries based on a tag
     *
     * @param   string  $tag  tag
     * @return  array
     */
    public function find($tag);
}
