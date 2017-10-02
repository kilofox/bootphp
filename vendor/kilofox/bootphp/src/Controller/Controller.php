<?php

namespace Bootphp\Controller;

use Bootphp\Http\Http;
use Bootphp\Http\HttpException;
use Bootphp\Request\Request;
use Bootphp\Response;

/**
 * Abstract controller class. Controllers should only be created using a [Request].
 *
 * Controllers methods will be automatically called in the following order by
 * the request:
 *
 *     $controller = new Controller_Foo($request);
 *     $controller->before();
 *     $controller->action_bar();
 *     $controller->after();
 *
 * The controller action should add the output it creates to
 * `$this->response->body($output)`, typically in the form of a [View], during the
 * "action" part of execution.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
abstract class Controller
{
    /**
     * @var  Request  Request that created the controller
     */
    public $request;

    /**
     * @var  Response The response that will be returned from controller
     */
    public $response;

    /**
     * Creates a new controller instance. Each controller must be constructed
     * with the request object that created it.
     *
     * @param   Request   $request  Request that created the controller
     * @param   Response  $response The request's response
     * @return  void
     */
    public function __construct(Request $request, Response $response)
    {
        // Assign the request to the controller
        $this->request = $request;

        // Assign a response to the controller
        $this->response = $response;
    }

    /**
     * Executes the given action and calls the [Controller::before] and [Controller::after] methods.
     *
     * Can also be used to catch exceptions from actions in a single place.
     *
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     *
     * @throws  HttpException
     * @return  Response
     */
    public function execute()
    {
        // Execute the "before action" method
        $this->before();

        // Determine the action to use
        $action = $this->request->action() . 'Action';

        // If the action doesn't exist, it's a 404
        if (!method_exists($this, $action)) {
            throw HttpException::factory(404, 'The requested URL :uri was not found on this server.', array(':uri' => $this->request->uri()))
                ->request($this->request);
        }

        // Execute the action itself
        $this->{$action}();

        // Execute the "after action" method
        $this->after();

        // Return the response
        return $this->response;
    }

    /**
     * Automatically executed before the controller action. Can be used to set
     * class properties, do authorization checks, and execute other custom code.
     *
     * @return  void
     */
    public function before()
    {
        // Nothing by default
    }

    /**
     * Automatically executed after the controller action. Can be used to apply
     * transformation to the response, add extra output, and execute
     * other custom code.
     *
     * @return  void
     */
    public function after()
    {
        // Nothing by default
    }

    /**
     * Issues a HTTP redirect.
     *
     * Proxies to the [Http::redirect] method.
     *
     * @param  string  $uri   URI to redirect to
     * @param  int     $code  HTTP Status code to use for the redirect
     * @throws HttpException
     */
    public static function redirect($uri = '', $code = 302)
    {
        return Http::redirect((string) $uri, $code);
    }

    /**
     * Checks the browser cache to see the response needs to be returned,
     * execution will halt and a 304 Not Modified will be sent if the
     * browser cache is up to date.
     *
     *     $this->check_cache(sha1($content));
     *
     * @param  string  $etag  Resource Etag
     * @return Response
     */
    protected function check_cache($etag = null)
    {
        return Http::check_cache($this->request, $this->response, $etag);
    }

}
