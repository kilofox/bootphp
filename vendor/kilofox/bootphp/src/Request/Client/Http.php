<?php

namespace Bootphp\Request\Client;

use Bootphp\Request\Client\External;
use Bootphp\BootphpException;
use Bootphp\Http\Request;

/**
 * [\Bootphp\Request\Client\External] HTTP driver performs external requests using the
 * php-http extension. To use this driver, ensure the following is completed
 * before executing an external request- ideally in the application bootstrap.
 *
 * @example
 *
 *       // In application bootstrap
 *       \Bootphp\Request\Client\External::$client = '\Bootphp\Request\Client\Http';
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 * @uses       [PECL HTTP](http://php.net/manual/en/book.http.php)
 */
class Http extends External
{
    /**
     * Creates a new `Request_Client` object,
     * allows for dependency injection.
     *
     * @param   array    $params Params
     * @throws  BootphpException
     */
    public function __construct(array $params = [])
    {
        // Check that PECL HTTP supports requests
        if (!http_support(HTTP_SUPPORT_REQUESTS)) {
            throw new BootphpException('Need HTTP request support!');
        }

        // Carry on
        parent::__construct($params);
    }

    /**
     * @var     array     cUrl options
     * @link    http://www.php.net/manual/function.curl-setopt
     */
    protected $_options = [];

    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param   Request   $request  request to send
     * @param   Response  $request  response to send
     * @return  Response
     */
    public function _send_message(Request $request, Response $response)
    {
        $http_method_mapping = [
            'GET' => 'GET',
            'HEAD' => 'HEAD',
            'POST' => 'POST',
            'PUT' => 'PUT',
            'DELETE' => 'DELETE',
            'OPTIONS' => 'OPTIONS',
            'TRACE' => 'TRACE',
            'CONNECT' => 'CONNECT',
        ];

        // Create an http request object
        $http_request = new \Request($request->uri(), $http_method_mapping[$request->method()]);

        if ($this->_options) {
            // Set custom options
            $http_request->setOptions($this->_options);
        }

        // Set headers
        $http_request->setHeaders($request->headers()->getArrayCopy());

        // Set cookies
        $http_request->setCookies($request->cookie());

        // Set query data (?foo=bar&bar=foo)
        $http_request->setQueryData($request->query());

        // Set the body
        if ($request->method() == 'PUT') {
            $http_request->addPutData($request->body());
        } else {
            $http_request->setBody($request->body());
        }

        try {
            $http_request->send();
        } catch (\Exception $e) {
            throw new BootphpException($e->getMessage());
        }

        // Build the response
        $response->status($http_request->getResponseCode())
            ->headers($http_request->getResponseHeader())
            ->cookie($http_request->getResponseCookies())
            ->body($http_request->getResponseBody());

        return $response;
    }

}
