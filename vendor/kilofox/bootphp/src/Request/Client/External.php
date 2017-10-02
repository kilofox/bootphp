<?php

namespace Bootphp\Request\Client;

use Bootphp\Request\Client;
use Bootphp\BootphpException;

/**
 * [\Bootphp\Request\Client\External] provides a wrapper for all external request
 * processing. This class should be extended by all drivers handling external
 * requests.
 *
 * Supported out of the box:
 *  - Curl (default)
 *  - PECL HTTP
 *  - Streams
 *
 * To select a specific external driver to use as the default driver, set the
 * following property within the Application bootstrap. Alternatively, the
 * client can be injected into the request object.
 *
 * @example
 *
 *       // In application bootstrap
 *       \Bootphp\Request\Client\External::$client = '\Bootphp\Request\Client\Stream';
 *
 *       // Add client to request
 *       $request = Request::factory('http://some.host.tld/foo/bar')
 *           ->client(\Bootphp\Request\Client\External::factory('\Request\Client\Http));
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 * @uses       [PECL HTTP](http://php.net/manual/en/book.http.php)
 */
abstract class External extends Client
{
    /**
     * Use:
     *  - \Bootphp\Request\Client\Curl (default)
     *  - \Bootphp\Request\Client\HTTP
     *  - \Bootphp\Request\Client\Stream
     *
     * @var     string    defines the external client to use by default
     */
    public static $client = '\Bootphp\Request\Client\Curl';

    /**
     * Factory method to create a new \Bootphp\Request\Client\External object based on
     * the client name passed, or defaulting to \Bootphp\Request\Client\External::$client
     * by default.
     *
     * \Bootphp\Request\Client\External::$client can be set in the application bootstrap.
     *
     * @param   array   $params parameters to pass to the client
     * @param   string  $client external client to use
     * @return  \Bootphp\Request\Client\External
     * @throws  BootphpException
     */
    public static function factory(array $params = [], $client = null)
    {
        if ($client === null) {
            $client = \Bootphp\Request\Client\External::$client;
        }

        $client = new $client($params);

        if (!$client instanceof \Bootphp\Request\Client\External) {
            throw new BootphpException('Selected client is not a \Bootphp\Request\Client\External object.');
        }

        return $client;
    }

    /**
     * @var     array     cUrl options
     * @link    http://www.php.net/manual/function.curl-setopt
     * @link    http://www.php.net/manual/http.request.options
     */
    protected $_options = [];

    /**
     * Processes the request, executing the controller action that handles this
     * request, determined by the [Route].
     *
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     *
     * By default, the output from the controller is captured and returned, and
     * no headers are sent.
     *
     *     $request->execute();
     *
     * @param   Request   $request   A request object
     * @param   Response  $response  A response object
     * @return  Response
     * @throws  BootphpException
     * @uses    [Core::$profiling]
     * @uses    [Profiler]
     */
    public function execute_request(Request $request, Response $response)
    {
        if (Core::$profiling) {
            // Set the benchmark name
            $benchmark = '"' . $request->uri() . '"';

            if ($request !== Request::$initial and Request::$current) {
                // Add the parent request uri
                $benchmark .= ' « "' . Request::$current->uri() . '"';
            }

            // Start benchmarking
            $benchmark = Profiler::start('Requests', $benchmark);
        }

        // Store the current active request and replace current with new request
        $previous = Request::$current;
        Request::$current = $request;

        // Resolve the POST fields
        if ($post = $request->post()) {
            $request->body(http_build_query($post, null, '&'))
                ->headers('content-type', 'application/x-www-form-urlencoded; charset=utf-8');
        }

        $request->headers('content-length', (string) $request->content_length());

        // If Bootphp expose, set the user-agent
        if (Core::$expose) {
            $request->headers('user-agent', Core::version());
        }

        try {
            $response = $this->_send_message($request, $response);
        } catch (\Exception $e) {
            // Restore the previous request
            Request::$current = $previous;

            if (isset($benchmark)) {
                // Delete the benchmark, it is invalid
                Profiler::delete($benchmark);
            }

            // Re-throw the exception
            throw $e;
        }

        // Restore the previous request
        Request::$current = $previous;

        if (isset($benchmark)) {
            // Stop the benchmark
            Profiler::stop($benchmark);
        }

        // Return the response
        return $response;
    }

    /**
     * Set and get options for this request.
     *
     * @param   mixed    $key    Option name, or array of options
     * @param   mixed    $value  Option value
     * @return  mixed
     * @return  \Bootphp\Request\Client\External
     */
    public function options($key = null, $value = null)
    {
        if ($key === null)
            return $this->_options;

        if (is_array($key)) {
            $this->_options = $key;
        } elseif ($value === null) {
            return Arr::get($this->_options, $key);
        } else {
            $this->_options[$key] = $value;
        }

        return $this;
    }

    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param   Request   $request    Request to send
     * @param   Response  $response   Response to send
     * @return  Response
     */
    abstract protected function _send_message(Request $request, Response $response);
}
