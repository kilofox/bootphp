<?php

namespace Bootphp\Http;

/**
 * A HTTP Request specific interface that adds the methods required
 * by HTTP requests. Over and above [Bootphp_HTTP_Interaction], this
 * interface provides method, uri, get and post methods.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
interface Request extends Message
{
    // HTTP Methods
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const HEAD = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE = 'TRACE';
    const CONNECT = 'CONNECT';

    /**
     * Gets or sets the HTTP method. Usually GET, POST, PUT or DELETE in
     * traditional CRUD applications.
     *
     * @param   string   $method  Method to use for this request
     * @return  mixed
     */
    public function method($method = null);
    /**
     * Gets the URI of this request, optionally allows setting
     * of [Route] specific parameters during the URI generation.
     * If no parameters are passed, the request will use the
     * default values defined in the Route.
     *
     * @param   array    $params  Optional parameters to include in uri generation
     * @return  string
     */
    public function uri();
    /**
     * Gets or sets HTTP query string.
     *
     * @param   mixed   $key    Key or key value pairs to set
     * @param   string  $value  Value to set to a key
     * @return  mixed
     */
    public function query($key = null, $value = null);
    /**
     * Gets or sets HTTP POST parameters to the request.
     *
     * @param   mixed   $key   Key or key value pairs to set
     * @param   string  $value Value to set to a key
     * @return  mixed
     */
    public function post($key = null, $value = null);
}
