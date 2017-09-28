<?php

namespace Bootphp\Request\Client;

use Bootphp\Request\Client\External;

/**
 * [\Bootphp\Request\Client\External] Stream driver performs external requests
 * using php sockets. To use this driver, ensure the following is completed
 * before executing an external request- ideally in the application bootstrap.
 *
 * @example
 *
 *       // In application bootstrap
 *       \Bootphp\Request\Client\External::$client = '\Bootphp\Request\Client\Stream';
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 * @uses       [PHP Streams](http://php.net/manual/en/book.stream.php)
 */
class Stream extends External
{
    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param   Request   $request  Request to send
     * @param   Response  $request  Response to send
     * @return  Response
     * @uses    [PHP cURL](http://php.net/manual/en/book.curl.php)
     */
    public function _send_message(Request $request, Response $response)
    {
        // Calculate stream mode
        $mode = ($request->method() === 'GET') ? 'r' : 'r+';

        // Process cookies
        if ($cookies = $request->cookie()) {
            $request->headers('cookie', http_build_query($cookies, null, '; '));
        }

        // Get the message body
        $body = $request->body();

        if (is_resource($body)) {
            $body = stream_get_contents($body);
        }

        // Set the content length
        $request->headers('content-length', (string) strlen($body));

        list($protocol) = explode('/', $request->protocol());

        // Create the context
        $options = array(
            strtolower($protocol) => array(
                'method' => $request->method(),
                'header' => (string) $request->headers(),
                'content' => $body
            )
        );

        // Create the context stream
        $context = stream_context_create($options);

        stream_context_set_option($context, $this->_options);

        $uri = $request->uri();

        if ($query = $request->query()) {
            $uri .= '?' . http_build_query($query, null, '&');
        }

        $stream = fopen($uri, $mode, false, $context);

        $meta_data = stream_get_meta_data($stream);

        // Get the HTTP response code
        $http_response = array_shift($meta_data['wrapper_data']);

        if (preg_match_all('/(\w+\/\d\.\d) (\d{3})/', $http_response, $matches) !== false) {
            $protocol = $matches[1][0];
            $status = (int) $matches[2][0];
        } else {
            $protocol = null;
            $status = null;
        }

        // Get any exisiting response headers
        $response_header = $response->headers();

        // Process headers
        array_map(array($response_header, 'parse_header_string'), [], $meta_data['wrapper_data']);

        $response->status($status)
            ->protocol($protocol)
            ->body(stream_get_contents($stream));

        // Close the stream after use
        fclose($stream);

        return $response;
    }

}
