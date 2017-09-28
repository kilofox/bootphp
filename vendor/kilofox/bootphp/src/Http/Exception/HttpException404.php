<?php

namespace Bootphp\Http\Exception;

use Bootphp\Http\HttpException;

class HttpException404 extends HttpException
{
    /**
     * @var   integer    HTTP 404 Not Found
     */
    protected $_code = 404;

}
