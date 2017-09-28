<?php

/**
 * UTF8::strcasecmp
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strcasecmp($str1, $str2)
{
    if (UTF8::is_ascii($str1) and UTF8::is_ascii($str2))
        return strcasecmp($str1, $str2);

    $str1 = UTF8::strtolower($str1);
    $str2 = UTF8::strtolower($str2);
    return strcmp($str1, $str2);
}
