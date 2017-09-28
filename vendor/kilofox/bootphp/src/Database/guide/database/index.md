# Database 

Bootphp 3.0 comes with a robust module for working with databases. By default, the database module supports drivers for [MySQL](http://php.net/mysql) and [PDO](http://php.net/pdo), but new drivers can be made for other database servers.

The database module is included with the Bootphp 3.0 install, but needs to be enabled before you can use it. To enable, open your `application/bootstrap.php` file and modify the call to [Bootphp::modules] by including the database module like so:

    Bootphp::modules(array(
        ...
        'database' => MODPATH.'database',
        ...
    ));

Next, you will then need to [configure](config) the database module to connect to your database.

Once that is done then you can make [queries](query) and use the [results](results).

The database module also provides a [config driver](../api/Bootphp_Config_Database) (for storing [configuration](../kohana/files/config) in the database) and a [session driver](Session_Database).
