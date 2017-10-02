<?php

/**
 * UTF8::stristr
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _stristr($str, $search)
{
    if (UTF8::is_ascii($str) and UTF8::is_ascii($search))
        return stristr($str, $search);

    if ($search == '')
        return $str;

    $str_lower = UTF8::strtolower($str);
    $search_lower = UTF8::strtolower($search);

    preg_match('/^(.*?)' . preg_quote($search_lower, '/') . '/s', $str_lower, $matches);

    if (isset($matches[1]))
        return substr($str, strlen($matches[1]));

    return false;
}
