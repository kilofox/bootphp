<?php

/**
 * UTF8::strrev
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrev($str)
{
    if (UTF8::is_ascii($str))
        return strrev($str);

    preg_match_all('/./us', $str, $matches);
    return implode('', array_reverse($matches[0]));
}
