<?php

/**
 * UTF8::str_split
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
function _str_split($str, $split_length = 1)
{
    $split_length = (int) $split_length;

    if (UTF8::is_ascii($str))
        return str_split($str, $split_length);

    if ($split_length < 1)
        return false;

    if (UTF8::strlen($str) <= $split_length)
        return array($str);

    preg_match_all('/.{' . $split_length . '}|[^\x00]{1,' . $split_length . '}$/us', $str, $matches);

    return $matches[0];
}
