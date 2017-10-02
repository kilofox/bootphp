<?php

namespace Bootphp\Database\Database\Query\Builder;

use Bootphp\BootphpException;

/**
 * Database query builder for INSERT statements. See [Query Builder](/database/query/builder) for usage and examples.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_Database_Query_Builder_Insert extends Database_Query_Builder
{
    // INSERT INTO ...
    protected $_table;
    // (...)
    protected $_columns = [];
    // VALUES (...)
    protected $_values = [];

    /**
     * Set the table and columns for an insert.
     *
     * @param   mixed  $table    table name or array($table, $alias) or object
     * @param   array  $columns  column names
     * @return  void
     */
    public function __construct($table = null, array $columns = null)
    {
        if ($table) {
            // Set the inital table name
            $this->table($table);
        }

        if ($columns) {
            // Set the column names
            $this->_columns = $columns;
        }

        // Start the query with no SQL
        return parent::__construct(Database::INSERT, '');
    }

    /**
     * Sets the table to insert into.
     *
     * @param   string  $table  table name
     * @return  $this
     */
    public function table($table)
    {
        if (!is_string($table))
            throw new BootphpException('INSERT INTO syntax does not allow table aliasing');

        $this->_table = $table;

        return $this;
    }

    /**
     * Set the columns that will be inserted.
     *
     * @param   array  $columns  column names
     * @return  $this
     */
    public function columns(array $columns)
    {
        $this->_columns = $columns;

        return $this;
    }

    /**
     * Adds or overwrites values. Multiple value sets can be added.
     *
     * @param   array   $values  values list
     * @param   ...
     * @return  $this
     */
    public function values(array $values)
    {
        if (!is_array($this->_values)) {
            throw new BootphpException('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
        }

        // Get all of the passed values
        $values = func_get_args();

        foreach ($values as $value) {
            $this->_values[] = $value;
        }

        return $this;
    }

    /**
     * Use a sub-query to for the inserted values.
     *
     * @param   object  $query  Database_Query of SELECT type
     * @return  $this
     */
    public function select(Database_Query $query)
    {
        if ($query->type() !== Database::SELECT) {
            throw new BootphpException('Only SELECT queries can be combined with INSERT queries');
        }

        $this->_values = $query;

        return $this;
    }

    /**
     * Compile the SQL query and return it.
     *
     * @param   mixed  $db  Database instance or name of instance
     * @return  string
     */
    public function compile($db = null)
    {
        if (!is_object($db)) {
            // Get the database instance
            $db = Database::instance($db);
        }

        // Start an insertion query
        $query = 'INSERT INTO ' . $db->quote_table($this->_table);

        // Add the column names
        $query .= ' (' . implode(', ', array_map(array($db, 'quote_column'), $this->_columns)) . ') ';

        if (is_array($this->_values)) {
            // Callback for quoting values
            $quote = array($db, 'quote');

            $groups = [];
            foreach ($this->_values as $group) {
                foreach ($group as $offset => $value) {
                    if ((is_string($value) and array_key_exists($value, $this->_parameters)) === false) {
                        // Quote the value, it is not a parameter
                        $group[$offset] = $db->quote($value);
                    }
                }

                $groups[] = '(' . implode(', ', $group) . ')';
            }

            // Add the values
            $query .= 'VALUES ' . implode(', ', $groups);
        } else {
            // Add the sub-query
            $query .= (string) $this->_values;
        }

        $this->_sql = $query;

        return parent::compile($db);
        ;
    }

    public function reset()
    {
        $this->_table = null;

        $this->_columns = $this->_values = [];

        $this->_parameters = [];

        $this->_sql = null;

        return $this;
    }

}
