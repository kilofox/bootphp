<?php

return array
    (
    /* 	'memcache' => array(
      'driver'             => 'memcache',
      'default_expire'     => 3600,
      'compression'        => false,              // Use Zlib compression (can cause issues with integers)
      'servers'            => array(
      'local' => array(
      'host'             => 'localhost',  // Memcache Server
      'port'             => 11211,        // Memcache port number
      'persistent'       => false,        // Persistent connection
      'weight'           => 1,
      'timeout'          => 1,
      'retry_interval'   => 15,
      'status'           => true,
      ),
      ),
      'instant_death'      => true,               // Take server offline immediately on first fail (no retry)
      ),
      'memcachetag' => array(
      'driver'             => 'memcachetag',
      'default_expire'     => 3600,
      'compression'        => false,              // Use Zlib compression (can cause issues with integers)
      'servers'            => array(
      'local' => array(
      'host'             => 'localhost',  // Memcache Server
      'port'             => 11211,        // Memcache port number
      'persistent'       => false,        // Persistent connection
      'weight'           => 1,
      'timeout'          => 1,
      'retry_interval'   => 15,
      'status'           => true,
      ),
      ),
      'instant_death'      => true,
      ),
      'apc'      => array(
      'driver'             => 'apc',
      'default_expire'     => 3600,
      ),
      'wincache' => array(
      'driver'             => 'wincache',
      'default_expire'     => 3600,
      ),
      'sqlite'   => array(
      'driver'             => 'sqlite',
      'default_expire'     => 3600,
      'database'           => APP_PATH.'cache/bootphp-cache.sql3',
      'schema'             => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
      ),
      'eaccelerator'           => array(
      'driver'             => 'eaccelerator',
      ),
      'xcache'   => array(
      'driver'             => 'xcache',
      'default_expire'     => 3600,
      ),
      'file'    => array(
      'driver'             => 'file',
      'cache_dir'          => APP_PATH.'cache',
      'default_expire'     => 3600,
      'ignore_on_delete'   => array(
      '.gitignore',
      '.git',
      '.svn'
      )
      )
     */
);
