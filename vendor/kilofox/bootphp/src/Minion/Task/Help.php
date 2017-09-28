<?php

/**
 * Help task to display general instructons and list all tasks
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Task_Help extends Minion_Task
{
    /**
     * Generates a help list for all tasks
     *
     * @return null
     */
    protected function _execute(array $params)
    {
        $tasks = $this->_compile_task_list(Core::list_files('classes/Task'));

        $view = new View('minion/help/list');

        $view->tasks = $tasks;

        echo $view;
    }

}
