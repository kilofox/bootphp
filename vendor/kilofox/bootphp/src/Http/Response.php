<?php

namespace Bootphp\Http;

/**
 * A HTTP Response specific interface that adds the methods required
 * by HTTP responses. Over and above [Bootphp_HTTP_Interaction], this
 * interface provides status.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
interface Response extends Message
{
    /**
     * Sets or gets the HTTP status from this response.
     *
     *      // Set the HTTP status to 404 Not Found
     *      $response = Response::factory()
     *              ->status(404);
     *
     *      // Get the current status
     *      $status = $response->status();
     *
     * @param   integer  $code  Status to set to this response
     * @return  mixed
     */
    public function status($code = null);
}
