<?php

/**
 * Invalid Task Exception
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_Minion_Exception_InvalidTask extends Minion_Exception
{
    public function format_for_cli()
    {
        return 'ERROR: ' . $this->getMessage() . PHP_EOL;
    }

}
