<?php

/**
 * UTF8::ucfirst
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _ucfirst($str)
{
    if (UTF8::is_ascii($str))
        return ucfirst($str);

    preg_match('/^(.?)(.*)$/us', $str, $matches);
    return UTF8::strtoupper($matches[1]) . $matches[2];
}
