<?php

/**
 * UTF8::strrpos
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrpos($str, $search, $offset = 0)
{
    $offset = (int) $offset;

    if (UTF8::is_ascii($str) and UTF8::is_ascii($search))
        return strrpos($str, $search, $offset);

    if ($offset == 0) {
        $array = explode($search, $str, -1);
        return isset($array[0]) ? UTF8::strlen(implode($search, $array)) : false;
    }

    $str = UTF8::substr($str, $offset);
    $pos = UTF8::strrpos($str, $search);
    return ($pos === false) ? false : ($pos + $offset);
}
