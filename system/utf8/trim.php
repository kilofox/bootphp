<?php

/**
 * UTF8::trim
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _trim($str, $charlist = null)
{
    if ($charlist === null)
        return trim($str);

    return UTF8::ltrim(UTF8::rtrim($str, $charlist), $charlist);
}
