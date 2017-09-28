<?php

/**
 * Default auth user toke
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Model_Auth_User_Token extends ORM
{
    // Relationships
    protected $_belongs_to = array(
        'user' => array('model' => 'User'),
    );
    protected $_created_column = array(
        'column' => 'created',
        'format' => true,
    );

    /**
     * Handles garbage collection and deleting of expired objects.
     *
     * @return  void
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        if (mt_rand(1, 100) === 1) {
            // Do garbage collection
            $this->delete_expired();
        }

        if ($this->expires < time() and $this->_loaded) {
            // This object has expired
            $this->delete();
        }
    }

    /**
     * Deletes all expired tokens.
     *
     * @return  ORM
     */
    public function delete_expired()
    {
        // Delete all expired tokens
        DB::delete($this->_table_name)
            ->where('expires', '<', time())
            ->execute($this->_db);

        return $this;
    }

    public function create(Validation $validation = null)
    {
        $this->token = $this->create_token();

        return parent::create($validation);
    }

    protected function create_token()
    {
        do {
            $token = sha1(uniqid(Text::random('alnum', 32), true));
        } while (ORM::factory('User_Token', array('token' => $token))->loaded());

        return $token;
    }

}
