<?php

/**
 * UTF8::ltrim
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _ltrim($str, $charlist = null)
{
    if ($charlist === null)
        return ltrim($str);

    if (UTF8::is_ascii($charlist))
        return ltrim($str, $charlist);

    $charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);

    return preg_replace('/^[' . $charlist . ']+/u', '', $str);
}
