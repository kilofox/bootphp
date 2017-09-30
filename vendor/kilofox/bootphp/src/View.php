<?php

namespace Bootphp;

use Bootphp\Exception\BootphpException;

/**
 * Acts as an object wrapper for HTML pages with embedded PHP, called "views".
 * Variables can be assigned with the view object and referenced locally within
 * the view.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class View
{
    /**
     * Template map.
     *
     * @var array
     */
    protected $map = [];
    // Array of global variables
    protected static $_global_data = [];

    /**
     * Returns a new View object. If you do not define the "file" parameter,
     * you must call [View::set_filename].
     *
     *     $view = View::factory($file);
     *
     * @param   string  $file   View filename
     * @param   array   $data   Array of values
     * @return  View
     */
    public static function factory($file = null, array $data = null)
    {
        return new self($file, $data);
    }

    /**
     * Captures the output that is generated when a view is included.
     * The view data will be extracted to make local variables. This method
     * is static to prevent object scope resolution.
     *
     *     $output = View::capture($file, $data);
     *
     * @param   string  $bootphp_view_filename   filename
     * @param   array   $bootphp_view_data       variables
     * @return  string
     * @throws  Exception
     */
    protected static function capture($bootphp_view_filename, array $bootphp_view_data)
    {
        // Import the view variables to local namespace
        extract($bootphp_view_data, EXTR_SKIP);

        if (self::$_global_data) {
            // Import the global view variables to local namespace
            extract(self::$_global_data, EXTR_SKIP | EXTR_REFS);
        }

        // Capture the view output
        ob_start();

        try {
            // Load the view within the current scope
            include $bootphp_view_filename;
        } catch (\Exception $e) {
            // Delete the output buffer
            ob_end_clean();

            // Re-throw the exception
            throw $e;
        }

        // Get the captured output and close the buffer
        return ob_get_clean();
    }

    /**
     * Sets a global variable, similar to [View::set], except that the
     * variable will be accessible to all views.
     *
     *     View::set_global($name, $value);
     *
     * You can also use an array or Traversable object to set several values at once:
     *
     *     // Create the values $food and $beverage in the view
     *     View::set_global(array('food' => 'bread', 'beverage' => 'water'));
     *
     * [!!] Note: When setting with using Traversable object we're not attaching the whole object to the view,
     * i.e. the object's standard properties will not be available in the view context.
     *
     * @param   string|array|Traversable  $key    variable name or an array of variables
     * @param   mixed                     $value  value
     * @return  void
     */
    public static function set_global($key, $value = null)
    {
        if (is_array($key) or $key instanceof Traversable) {
            foreach ($key as $name => $value) {
                self::$_global_data[$name] = $value;
            }
        } else {
            self::$_global_data[$key] = $value;
        }
    }

    /**
     * Assigns a global variable by reference, similar to [View::bind], except
     * that the variable will be accessible to all views.
     *
     *     View::bind_global($key, $value);
     *
     * @param   string  $key    variable name
     * @param   mixed   $value  referenced variable
     * @return  void
     */
    public static function bind_global($key, & $value)
    {
        self::$_global_data[$key] = & $value;
    }

    // View filename
    protected $_file;
    // Array of local variables
    protected $_data = [];

    /**
     * Sets the initial view filename and local data. Views should almost
     * always only be created using [View::factory].
     *
     *     $view = new View($file);
     *
     * @param   string  $file   view filename
     * @param   array   $data   array of values
     * @uses    View::set_filename
     */
    public function __construct($file = null, array $data = null)
    {
        if ($file !== null) {
            $this->set_filename($file);
        }

        if ($data !== null) {
            // Add the values to the current data
            $this->_data = $data + $this->_data;
        }
    }

    /**
     * Magic method, searches for the given variable and returns its value.
     * Local variables will be returned before global variables.
     *
     *     $value = $view->foo;
     *
     * [!!] If the variable has not yet been set, an exception will be thrown.
     *
     * @param   string  $key    variable name
     * @return  mixed
     * @throws  BootphpException
     */
    public function & __get($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        } elseif (array_key_exists($key, self::$_global_data)) {
            return self::$_global_data[$key];
        } else {
            throw new BootphpException('View variable is not set: :var', array(':var' => $key));
        }
    }

    /**
     * Magic method, calls [View::set] with the same parameters.
     *
     *     $view->foo = 'something';
     *
     * @param   string  $key    variable name
     * @param   mixed   $value  value
     * @return  void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Magic method, determines if a variable is set.
     *
     *     isset($view->foo);
     *
     * [!!] `null` variables are not considered to be set by [isset](http://php.net/isset).
     *
     * @param   string  $key    variable name
     * @return  boolean
     */
    public function __isset($key)
    {
        return (isset($this->_data[$key]) or isset(self::$_global_data[$key]));
    }

    /**
     * Magic method, unsets a given variable.
     *
     *     unset($view->foo);
     *
     * @param   string  $key    variable name
     * @return  void
     */
    public function __unset($key)
    {
        unset($this->_data[$key], self::$_global_data[$key]);
    }

    /**
     * Magic method, returns the output of [View::render].
     *
     * @return  string
     * @uses    View::render
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            /**
             * Display the exception message.
             *
             * We use this method here because it's impossible to throw an
             * exception from __toString().
             */
            $error_response = BootphpException::_handler($e);

            return $error_response->body();
        }
    }

    /**
     * Sets the view filename.
     *
     *     $view->set_filename($file);
     *
     * @param   string  $file   View filename
     * @return  View
     * @throws  BootphpException
     */
    public function set_filename($file)
    {
        $path = $file;
        if ($path === false) {
            throw new BootphpException('The requested view :file could not be found', array(
            ':file' => $file,
            ));
        }

        // Store the file path locally
        $this->_file = $path;

        return $this;
    }

    /**
     * Assigns a variable by name. Assigned values will be available as a
     * variable within the view file:
     *
     *     // This value can be accessed as $foo within the view
     *     $view->set('foo', 'my value');
     *
     * You can also use an array or Traversable object to set several values at once:
     *
     *     // Create the values $food and $beverage in the view
     *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
     *
     * [!!] Note: When setting with using Traversable object we're not attaching the whole object to the view,
     * i.e. the object's standard properties will not be available in the view context.
     *
     * @param   string|array|Traversable  $key    variable name or an array of variables
     * @param   mixed                     $value  value
     * @return  $this
     */
    public function set($key, $value = null)
    {
        if (is_array($key) or $key instanceof Traversable) {
            foreach ($key as $name => $value) {
                $this->_data[$name] = $value;
            }
        } else {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Renders the view object to a string. Global and local data are merged
     * and extracted to create local variables within the view file.
     *
     *     $output = $view->render();
     *
     * [!!] Global variables with the same key name as local variables will be
     * overwritten by the local variable.
     *
     * @param   string  $file   view filename
     * @return  string
     * @throws  BootphpException
     * @uses    View::capture
     */
    public function render($file = null)
    {
        if ($file !== null) {
            $this->set_filename($file);
        }

        if (empty($this->_file)) {
            throw new BootphpException('You must set the file to use within your view before rendering');
        }

        // Combine local and global data and capture the output
        return self::capture($this->_file, $this->_data);
    }

}
