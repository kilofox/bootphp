<?php

/**
 * Default auth role
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license.html
 */
class Model_Auth_Role extends ORM
{
    // Relationships
    protected $_has_many = array(
        'users' => array('model' => 'User', 'through' => 'roles_users'),
    );

    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
                array('min_length', array(':value', 4)),
                array('max_length', array(':value', 32)),
            ),
            'description' => array(
                array('max_length', array(':value', 255)),
            )
        );
    }

}
