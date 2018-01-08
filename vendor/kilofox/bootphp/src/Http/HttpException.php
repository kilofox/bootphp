<?php

namespace Bootphp\Http;

use Bootphp\BootphpException;

class HttpException extends BootphpException
{
    /**
     * Creates an HttpException of the specified type.
     *
     * @param   integer $code       The http status code
     * @param   string  $message    Status message, custom content to display with error
     * @param   array   $variables  Translation variables
     * @return  HttpException
     */
    public static function factory($code, $message = null, array $variables = null)
    {
        return new self($message, $variables, $code);
    }

    /**
     * @var  integer    HTTP status code
     */
    protected $_code = 0;

    /**
     * @var  Request    Request instance that triggered this exception.
     */
    protected $_request;

    /**
     * @var  Response   Response Object
     */
    protected $_response;

    /**
     * Creates a new translated exception.
     *
     *     throw new BootphpException('Something went wrong, :user', array(':user' => $user));
     *
     * @param   string  $message    Status message, custom content to display with error
     * @param   array   $variables  Translation variables
     * @return  void
     */
    public function __construct($message = null, array $variables = null, $code = 0)
    {
        $this->_code = $code;

        parent::__construct($message, $variables, $this->_code);

        // Prepare our response object and set the correct status code.
        $this->_response = \Bootphp\Response::factory()->status($this->_code);
    }

    /**
     * Store the Request that triggered this exception.
     *
     * @param   Request   $request  Request object that triggered this exception.
     * @return  HttpException
     */
    public function request(Request $request = null)
    {
        if ($request === null)
            return $this->_request;

        $this->_request = $request;

        return $this;
    }

    /**
     * Generate a Response for the current Exception
     *
     * @uses    BootphpException::response()
     * @return Response
     */
    public function get_response()
    {
        return BootphpException::response($this);
    }

    /**
     * Gets and sets headers to the [Response].
     *
     * @see     [Response::headers]
     * @param   mixed   $key
     * @param   string  $value
     * @return  mixed
     */
    public function headers($key = null, $value = null)
    {
        $result = $this->_response->headers($key, $value);

        if (!$result instanceof Response)
            return $result;

        return $this;
    }

}
