<?php

namespace App\Doc\Controller;

use Bootphp\Controller\Template;
use Bootphp\Route;
use Bootphp\URL;
use Michelf\Markdown;
use App\Doc\Kodoc\Kodoc_Markdown;
use Bootphp\Core;
use Bootphp\View;
use Bootphp\Filesystem;
use Bootphp\File;

/**
 * Bootphp user guide and api browser.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class IndexController extends Template
{
    public $template = 'doc/template';
    // Routes
    protected $media;
    protected $api;
    protected $guide;

    public function before()
    {
        parent::before();

        if ($this->request->action() === 'media') {
            // Do not template media files
            $this->auto_render = false;
        } else {
            // Grab the necessary routes
            $this->media = Route::get('doc/media');
            $this->guide = Route::get('doc/guide');

            // Set the base URL for links and images
            Kodoc_Markdown::$base_url = URL::site($this->guide->uri()) . '/';
            Kodoc_Markdown::$image_url = URL::site($this->media->uri()) . '/';
        }

        // Default show_comments to config value
        $this->template->show_comments = Core::$config->load('userguide')->get('show_comments');
    }

    // List all modules that have userguides
    public function indexAction()
    {
        $this->template->title = 'Userguide';
        $this->template->breadcrumb = ['User Guide'];
        $this->template->content = View::factory('doc/index', ['modules' => $this->_modules()]);
        $this->template->menu = View::factory('doc/menu', ['modules' => $this->_modules()]);

        // Don't show disqus on the index page
        $this->template->show_comments = false;
    }

    // Display an error if a page isn't found
    public function error($message)
    {
        $this->response->status(404);
        $this->template->title = "Userguide - Error";
        $this->template->content = View::factory('doc/error', ['message' => $message]);

        // Don't show disqus on error pages
        $this->template->show_comments = false;

        // If we are in a module and that module has a menu, show that
        if ($module = $this->request->param('module') and $menu = $this->file($module . '/menu') and Core::$config->load('userguide.modules.' . $module . '.enabled')) {
            // Namespace the markdown parser
            Kodoc_Markdown::$base_url = URL::site($this->guide->uri()) . '/' . $module . '/';
            Kodoc_Markdown::$image_url = URL::site($this->media->uri()) . '/' . $module . '/';

            $this->template->menu = Kodoc_Markdown::markdown($this->_get_all_menu_markdown());
            $this->template->breadcrumb = [
                $this->guide->uri() => 'User Guide',
                $this->guide->uri(['module' => $module]) => Core::$config->load('userguide.modules.' . $module . '.name'),
                'Error'
            ];
        }
        // If we are in the api browser, show the menu and show the api browser in the breadcrumbs
        elseif (Route::name($this->request->route()) == 'doc/api') {
            $this->template->menu = Kodoc::menu();

            // Bind the breadcrumb
            $this->template->breadcrumb = [
                $this->guide->uri(['page' => null]) => 'User Guide',
                $this->request->route()->uri() => 'API Browser',
                'Error'
            ];
        }
        // Otherwise, show the userguide module menu on the side
        else {
            $this->template->menu = View::factory('doc/menu', ['modules' => $this->_modules()]);
            $this->template->breadcrumb = [$this->request->route()->uri() => 'User Guide', 'Error'];
        }
    }

    public function docAction()
    {
        $module = $this->request->param('module');
        $page = $this->request->param('page');

        // Trim trailing slash
        $page = rtrim($page, '/');

        // If no module provided in the url, show the user guide index page, which lists the modules.
        if (!$module) {
            return $this->index();
        }

        // If this module's userguide pages are disabled, show the error page
        if (!Core::$config->load('userguide.modules.' . $module . '.enabled')) {
            return $this->error('That module doesn\'t exist, or has userguide pages disabled.');
        }

        // Prevent "guide/module" and "guide/module/index" from having duplicate content
        if ($page == 'index') {
            return $this->error('Userguide page not found');
        }

        // If a module is set, but no page was provided in the url, show the index page
        if (!$page) {
            $page = 'index';
        }

        // Find the markdown file for this page
        $file = $this->file($module . '/' . $page);

        // If it's not found, show the error page
        if (!$file) {
            return $this->error('Userguide page not found');
        }

        // Namespace the markdown parser
        Kodoc_Markdown::$base_url = URL::site($this->guide->uri()) . '/' . $module . '/';
        Kodoc_Markdown::$image_url = URL::site($this->media->uri()) . '/' . $module . '/';

        // Set the page title
        $this->template->title = ($page == 'index') ? Core::$config->load('userguide.modules.' . $module . '.name') : $this->title($page);

        // Parse the page contents into the template
        Kodoc_Markdown::$show_toc = true;
        $this->template->content = Kodoc_Markdown::markdown(file_get_contents($file));
        Kodoc_Markdown::$show_toc = false;

        // Attach this module's menu to the template
        $this->template->menu = Kodoc_Markdown::markdown($this->_get_all_menu_markdown());

        // Bind the breadcrumb
        $this->template->bind('breadcrumb', $breadcrumb);

        // Bind the copyright
        $this->template->copyright = Core::$config->load('userguide.modules.' . $module . '.copyright');

        // Add the breadcrumb trail
        $breadcrumb = [];
        $breadcrumb[$this->guide->uri()] = 'User Guide';
        $breadcrumb[$this->guide->uri(['module' => $module])] = Core::$config->load('userguide.modules.' . $module . '.name');

        // TODO try and get parent category names (from menu).  Regex magic or javascript dom stuff perhaps?
        // Only add the current page title to breadcrumbs if it isn't the index, otherwise we get repeats.
        if ($page != 'index') {
            $breadcrumb[] = $this->template->title;
        }
    }

    public function apiAction()
    {
        // Enable the missing class autoloader.  If a class cannot be found a
        // fake class will be created that extends Kodoc_Missing
        spl_autoload_register(['Kodoc_Missing', 'create_class']);

        // Get the class from the request
        $class = $this->request->param('class');

        // If no class was passed to the url, display the API index page
        if (!$class) {
            $this->template->title = 'Table of Contents';

            $this->template->content = View::factory('doc/api/toc')
                ->set('classes', Kodoc::class_methods())
                ->set('route', $this->request->route());
        } else {
            // Create the Kodoc_Class version of this class.
            $_class = Kodoc_Class::factory($class);

            // If the class requested and the actual class name are different
            // (different case, orm vs ORM, auth vs Auth) redirect
            if ($_class->class->name != $class) {
                $this->redirect($this->request->route()->uri(['class' => $_class->class->name]));
            }

            // If this classes immediate parent is Kodoc_Missing, then it should 404
            if ($_class->class->getParentClass() and $_class->class->getParentClass()->name == 'Kodoc_Missing')
                return $this->error('That class was not found. Check your url and make sure that the module with that class is enabled.');

            // If this classes package has been disabled via the config, 404
            if (!Kodoc::show_class($_class))
                return $this->error('That class is in package that is hidden.  Check the <code>api_packages</code> config setting.');

            // Everything is fine, display the class.
            $this->template->title = $class;

            $this->template->content = View::factory('doc/api/class')
                ->set('doc', $_class)
                ->set('route', $this->request->route());
        }

        // Attach the menu to the template
        $this->template->menu = Kodoc::menu();

        // Bind the breadcrumb
        $this->template->bind('breadcrumb', $breadcrumb);

        // Add the breadcrumb
        $breadcrumb = [];
        $breadcrumb[$this->guide->uri(['page' => null])] = 'User Guide';
        $breadcrumb[$this->request->route()->uri()] = 'API Browser';
        $breadcrumb[] = $this->template->title;
    }

    public function mediaAction()
    {
        // Get the file path from the request
        $file = $this->request->param('file');

        // Find the file extension
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        // Remove the extension from the filename
        $file = substr($file, 0, -(strlen($ext) + 1));

        if ($file = Filesystem::findFile('public/doc/media', $file, $ext)) {
            // Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
            $this->check_cache(sha1($this->request->uri()) . filemtime($file));

            // Send the file content as the response
            $this->response->body(file_get_contents($file));

            // Set the proper headers to allow caching
            $this->response->headers('content-type', File::mime_by_ext($ext));
            $this->response->headers('last-modified', date('r', filemtime($file)));
        } else {
            // Return a 404 status
            $this->response->status(404);
        }
    }

    public function after()
    {
        if ($this->auto_render) {
            // Get the media route
            $media = Route::get('doc/media');

            // Add styles
            $this->template->styles = [
                $media->uri(['file' => 'css/print.css']) => 'print',
                $media->uri(['file' => 'css/screen.css']) => 'screen',
                $media->uri(['file' => 'css/kodoc.css']) => 'screen',
                $media->uri(['file' => 'css/shCore.css']) => 'screen',
                $media->uri(['file' => 'css/shThemeKodoc.css']) => 'screen',
            ];

            // Add scripts
            $this->template->scripts = [
                $media->uri(['file' => 'js/jquery.min.js']),
                $media->uri(['file' => 'js/jquery.cookie.js']),
                $media->uri(['file' => 'js/kodoc.js']),
                // Syntax Highlighter
                $media->uri(['file' => 'js/shCore.js']),
                $media->uri(['file' => 'js/shBrushPhp.js']),
            ];

            // Add languages
            $this->template->translations = Core::message('userguide', 'translations');
        }

        return parent::after();
    }

    /**
     * Locates the appropriate markdown file for a given guide page. Page URLS
     * can be specified in one of three forms:
     *
     *  * userguide/adding
     *  * userguide/adding.md
     *  * userguide/adding.markdown
     *
     * In every case, the userguide will search the cascading file system paths
     * for the file guide/userguide/adding.md.
     *
     * @param string $page The relative URL of the guide page
     * @return string
     */
    public function file($page)
    {
        // Strip optional .md or .markdown suffix from the passed filename
        $info = pathinfo($page);
        if (isset($info['extension'])
            and ( ($info['extension'] === 'md') or ( $info['extension'] === 'markdown'))) {
            $page = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'];
        }
        return Filesystem::findFile('guide', $page, 'md');
    }

    public function section($page)
    {
        $markdown = $this->_get_all_menu_markdown();

        if (preg_match('~\*{2}(.+?)\*{2}[^*]+\[[^\]]+\]\(' . preg_quote($page) . '\)~mu', $markdown, $matches)) {
            return $matches[1];
        }

        return $page;
    }

    public function title($page)
    {
        $markdown = $this->_get_all_menu_markdown();

        if (preg_match('~\[([^\]]+)\]\(' . preg_quote($page) . '\)~mu', $markdown, $matches)) {
            // Found a title for this link
            return $matches[1];
        }

        return $page;
    }

    protected function _get_all_menu_markdown()
    {
        // Only do this once per request...
        static $markdown = '';

        if (empty($markdown)) {
            // Get menu items
            $file = $this->file($this->request->param('module') . '/menu');

            if ($file and $text = file_get_contents($file)) {
                // Add spans around non-link categories. This is a terrible hack.
                $text = preg_replace('/^(\s*[\-\*\+]\s*)([^\[\]]+)$/m', '$1<span>$2</span>', $text);
                $markdown .= $text;
            }
        }

        return $markdown;
    }

    /**
     * Get the list of modules from the config, and reverses it so it displays
     * in the order the modules are added, but move Bootphp to the top.
     *
     * @return  array
     */
    protected function _modules()
    {
        $modules = array_reverse(Core::$config->load('userguide')->get('modules', []));

        if (isset($modules['bootphp'])) {
            $bootphp = $modules['bootphp'];
            unset($modules['bootphp']);
            $modules = array_merge(['bootphp' => $bootphp], $modules);
        }

        // Remove modules that have been disabled via config
        foreach ($modules as $key => $value) {
            if (!Core::$config->load('userguide.modules.' . $key . '.enabled')) {
                unset($modules[$key]);
            }
        }

        return $modules;
    }

}
