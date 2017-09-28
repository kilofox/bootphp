<?php

namespace Bootphp\Http;

/**
 * The HTTP Interaction interface providing the core HTTP methods that
 * should be implemented by any HTTP request or response class.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
interface Message
{
    /**
     * Gets or sets the HTTP protocol. The standard protocol to use
     * is `HTTP/1.1`.
     *
     * @param   string   $protocol  Protocol to set to the request/response
     * @return  mixed
     */
    public function protocol($protocol = null);
    /**
     * Gets or sets HTTP headers to the request or response. All headers
     * are included immediately after the HTTP protocol definition during
     * transmission. This method provides a simple array or key/value
     * interface to the headers.
     *
     * @param   mixed   $key    Key or array of key/value pairs to set
     * @param   string  $value  Value to set to the supplied key
     * @return  mixed
     */
    public function headers($key = null, $value = null);
    /**
     * Gets or sets the HTTP body to the request or response. The body is
     * included after the header, separated by a single empty new line.
     *
     * @param   string    $content  Content to set to the object
     * @return  string
     * @return  void
     */
    public function body($content = null);
    /**
     * Renders the HTTP_Interaction to a string, producing
     *
     *  - Protocol
     *  - Headers
     *  - Body
     *
     * @return  string
     */
    public function render();
}
