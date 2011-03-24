<?php
namespace core;

interface PluginInterfaceDB
{
    /**
     *  Returns the number of rows affected by the last DELETE, INSERT, or UPDATE query.
     *
     *  @access public
     *  @return int
     */
     public function affected_rows();

    /**
     *  Closes the connection.
     *
     *  @access public
     *  @return void
     */
     public function close();

   /**
    *  Connects and selects database.
    *
    *  @access public
    *  @return void
    */
    public function connect($database, $host, $username, $password);

    /**
     *  Fetches the next row from a result set.
     *
     *  @access public
     *  @return mixed
     */
     public function fetch();

    /**
     *  Returns an array containing all of the result set rows.
     *
     *  @access public
     *  @return mixed
     */
     public function fetch_all();

    /**
     *  Inserts an object into the database. 
     *
     *  @param obj $obj              The object to be inserted. 
     *  @access public
     *  @return bool
     */
     public function insert($obj);

    /**
     *  Returns the ID of the last inserted row or sequence value.
     *
     *  @access public
     *  @return int
     */
     public function insert_id();

    /**
     *  Returns the number of rows affected by the last SELECT query.
     *
     *  @access public
     *  @return int       
     */
     public function num_rows();

    /**
     *  Executes SQL query.
     *
     *  @param string $sql           The SQL query to be executed.
     *  @access public
     *  @return bool       
     */
     public function query($sql);

    /**
     *  Executes SQL query and returns the first row of the results.
     *
     *  @param string $sql           The SQL query to be executed.
     *  @access public
     *  @return mixed       
     */
     public function query_first($sql);

    /**
     *  Updates an object in the database.
     *
     *  @param obj $obj              The object to be updated.
     *  @param string $where         The WHERE clause of the SQL query.
     *  @access public
     *  @return bool 
     */
     public function update($obj, $where = '1');

    /**
     *  Inserts or updates (if exists) an object in the database.
     *
     *  @param obj $obj                The object to be upserted.
     *  @access public
     *  @return bool 
     */
     public function upsert($obj);

}

?>
