<?php

namespace Bootphp\Minion;

use Bootphp\BootphpException;

/**
 * Minion exception
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class MinionException extends BootphpException
{
    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * Should this display a stack trace? It's useful.
     *
     * @uses    BootphpException::text
     * @param   Exception   $e
     * @return  boolean
     */
    public static function handler(Exception $e)
    {
        try {
            // Log the exception
            BootphpException::log($e);

            if ($e instanceof Minion_Exception) {
                echo $e->format_for_cli();
            } else {
                echo BootphpException::text($e);
            }

            $exit_code = $e->getCode();

            // Never exit "0" after an exception.
            if ($exit_code == 0) {
                $exit_code = 1;
            }

            exit($exit_code);
        } catch (\Exception $e) {
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();

            // Display the exception text
            echo BootphpException::text($e), "\n";

            // Exit with an error status
            exit(1);
        }
    }

    public function format_for_cli()
    {
        return BootphpException::text($this);
    }

}
