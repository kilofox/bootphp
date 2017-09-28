<?php

namespace Bootphp\Codebench\Controller;

use Bootphp\Http\HttpException;

/**
 * Codebench — A benchmarking module.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright  (c) 2009 Bootphp Team
 * @license     http://kilofox.net/bootphp/license.html
 */
class Controller_Codebench extends Bootphp_Controller_Template
{
    // The codebench view
    public $template = 'codebench';

    public function action_index()
    {
        $class = $this->request->param('class');

        // Convert submitted class name to URI segment
        if (isset($_POST['class'])) {
            throw HttpException::factory(302)->location('codebench/' . trim($_POST['class']));
        }

        // Pass the class name on to the view
        $this->template->class = (string) $class;

        // Try to load the class, then run it
        if (Core::auto_load($class) === true) {
            $codebench = new $class;
            $this->template->codebench = $codebench->run();
        }
    }

}
