<?php

/**
 * Database query builder for WHERE statements. See [Query Builder](/database/query/builder) for usage and examples.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
abstract class Bootphp_Database_Query_Builder_Where extends Database_Query_Builder
{
    // WHERE ...
    protected $_where = [];
    // ORDER BY ...
    protected $_order_by = [];
    // LIMIT ...
    protected $_limit = null;

    /**
     * Alias of and_where()
     *
     * @param   mixed   $column  column name or array($column, $alias) or object
     * @param   string  $op      logic operator
     * @param   mixed   $value   column value
     * @return  $this
     */
    public function where($column, $op, $value)
    {
        return $this->and_where($column, $op, $value);
    }

    /**
     * Creates a new "and WHERE" condition for the query.
     *
     * @param   mixed   $column  column name or array($column, $alias) or object
     * @param   string  $op      logic operator
     * @param   mixed   $value   column value
     * @return  $this
     */
    public function and_where($column, $op, $value)
    {
        $this->_where[] = array('AND' => array($column, $op, $value));

        return $this;
    }

    /**
     * Creates a new "or WHERE" condition for the query.
     *
     * @param   mixed   $column  column name or array($column, $alias) or object
     * @param   string  $op      logic operator
     * @param   mixed   $value   column value
     * @return  $this
     */
    public function or_where($column, $op, $value)
    {
        $this->_where[] = array('OR' => array($column, $op, $value));

        return $this;
    }

    /**
     * Alias of and_where_open()
     *
     * @return  $this
     */
    public function where_open()
    {
        return $this->and_where_open();
    }

    /**
     * Opens a new "and WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function and_where_open()
    {
        $this->_where[] = array('AND' => '(');

        return $this;
    }

    /**
     * Opens a new "or WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function or_where_open()
    {
        $this->_where[] = array('OR' => '(');

        return $this;
    }

    /**
     * Closes an open "WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function where_close()
    {
        return $this->and_where_close();
    }

    /**
     * Closes an open "WHERE (...)" grouping or removes the grouping when it is
     * empty.
     *
     * @return  $this
     */
    public function where_close_empty()
    {
        $group = end($this->_where);

        if ($group and reset($group) === '(') {
            array_pop($this->_where);

            return $this;
        }

        return $this->where_close();
    }

    /**
     * Closes an open "WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function and_where_close()
    {
        $this->_where[] = array('AND' => ')');

        return $this;
    }

    /**
     * Closes an open "WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function or_where_close()
    {
        $this->_where[] = array('OR' => ')');

        return $this;
    }

    /**
     * Applies sorting with "ORDER BY ..."
     *
     * @param   mixed   $column     column name or array($column, $alias) or object
     * @param   string  $direction  direction of sorting
     * @return  $this
     */
    public function order_by($column, $direction = null)
    {
        $this->_order_by[] = array($column, $direction);

        return $this;
    }

    /**
     * Return up to "LIMIT ..." results
     *
     * @param   integer  $number  maximum results to return or null to reset
     * @return  $this
     */
    public function limit($number)
    {
        $this->_limit = ($number === null) ? null : (int) $number;

        return $this;
    }

}