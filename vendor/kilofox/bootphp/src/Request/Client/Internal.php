<?php

namespace Bootphp\Request\Client;

use Bootphp\Request\Client;
use Bootphp\Request;
use Bootphp\Response;
use Bootphp\Http\HttpException;
use Bootphp\Exception\BootphpException;

/**
 * Request Client for internal execution
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Internal extends Client
{
    /**
     * @var    array
     */
    protected $_previous_environment;

    /**
     * Processes the request, executing the controller action that handles this
     * request, determined by the [Route].
     *
     *     $request->execute();
     *
     * @param   Request $request
     * @return  Response
     * @throws  BootphpException
     * @uses    [Core::$profiling]
     * @uses    [Profiler]
     */
    public function execute_request(Request $request, Response $response)
    {
        // Create the class prefix
        $prefix = 'App\\src\\Controller\\';

        // Directory
        $directory = $request->directory();

        // Controller
        $controller = $request->controller();

        if ($directory) {
            // Add the directory name to the class prefix
            $prefix .= trim($directory, '/') . '\\';
        }

        if (\Bootphp\Core::$profiling) {
            // Set the benchmark name
            $benchmark = '"' . $request->uri() . '"';

            if ($request !== Request::$initial and Request::$current) {
                // Add the parent request uri
                $benchmark .= ' « "' . Request::$current->uri() . '"';
            }

            // Start benchmarking
            $benchmark = \Bootphp\Profiler::start('Requests', $benchmark);
        }

        // Store the currently active request
        $previous = Request::$current;

        // Change the current request to this request
        Request::$current = $request;

        // Is this the initial request
        $initial_request = ($request === Request::$initial);

        $controller = $prefix . $controller . 'Controller';

        try {
            if (!class_exists($controller)) {
                throw HttpException::factory(404, 'The requested URL :uri was not found on this server.', array(':uri' => $request->uri()))->request($request);
            }

            // Load the controller using reflection
            $class = new \ReflectionClass($controller);

            if ($class->isAbstract()) {
                throw new BootphpException('Cannot create instances of abstract :controller', array(':controller' => $prefix . $controller));
            }

            // Create a new instance of the controller
            $controller = $class->newInstance($request, $response);

            // Run the controller's execute() method
            $response = $class->getMethod('execute')->invoke($controller);

            if (!$response instanceof Response) {
                // Controller failed to return a Response.
                throw new BootphpException('Controller failed to return a Response');
            }
        } catch (HttpException $e) {
            // Store the request context in the Exception
            if ($e->request() === null) {
                $e->request($request);
            }

            // Get the response via the Exception
            $response = $e->get_response();
        } catch (\Exception $e) {
            // Generate an appropriate Response object
            $response = BootphpException::_handler($e);
        }

        // Restore the previous request
        Request::$current = $previous;

        if (isset($benchmark)) {
            // Stop the benchmark
            \Bootphp\Profiler::stop($benchmark);
        }

        // Return the response
        return $response;
    }

}
