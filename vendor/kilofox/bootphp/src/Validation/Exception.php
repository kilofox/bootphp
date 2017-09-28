<?php

/**
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_Validation_Exception extends BootphpException
{
    /**
     * @var  object  Validation instance
     */
    public $array;

    /**
     * @param  Validation   $array      Validation object
     * @param  string       $message    error message
     * @param  array        $values     translation variables
     * @param  int          $code       the exception code
     */
    public function __construct(Validation $array, $message = 'Failed to validate array', array $values = null, $code = 0, Exception $previous = null)
    {
        $this->array = $array;

        parent::__construct($message, $values, $code, $previous);
    }

}
