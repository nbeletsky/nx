<?php

namespace plugin\db;

use lib\Meta;

class PDO_MySQL extends \core\Object {
   /**
    *  The db handle. 
    *
    *  @var object
    *  @access protected
    */
    protected $_dbh;

   /**
    *  Number of rows affected by MySQL query.
    *
    *  @var int
    *  @access protected
    */
    protected $_affected_rows = 0;

   /**
    *  The result set associated with a prepared statement.
    *
    *  @var PDOStatement
    *  @access protected
    */
    protected $_statement;

   /**
    *  Connects and selects database.
    *
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'database' => DATABASE_NAME,
            'host'     => DATABASE_HOST, 
            'username' => DATABASE_USER,
            'password' => DATABASE_PASS
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        $this->connect($this->_config['database'], $this->_config['host'], $this->_config['username'], $this->_config['password']);
    }

    public function connect($database, $host, $username, $password) {
        $dsn = 'mysql:host=' . $host . ';dbname=' . $database; 
        try {
            $this->_dbh = new \PDO($dsn, $username, $password);
            $this->_dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $e ) {
            // TODO: How to handle error reporting?
        }
    }

   /**
    *  Returns the number of rows affected by the last DELETE, INSERT, or UPDATE query.
    *
    *  @access public
    *  @return int
    */
    public function affected_rows() {
    	return $this->_affected_rows;
    }

   /**
    *  Closes the connection.
    *
    *  @access public
    *  @return void
    */
    public function close() {
        $this->_dbh = null;
    }
    
    public function delete($obj, $where = null) {
        $sql = 'DELETE FROM `' . Meta::classname_only($obj) . '`';
        if ( is_null($where) ) {
            $id = PRIMARY_KEY;
            // TODO: Throw exception if id is null?
            $where = array(PRIMARY_KEY => $obj->$id);
        }

        $sql .= $this->_format_where($where);

        if ( is_string($where) ) {
            return $this->query($sql);
        } elseif ( is_array($where) ) {
            return $this->query($sql, $where);
        }
    }
    
   /**
    *  Fetches the next row from the result set in memory (i.e., the one
    *  that was created after running query()).
    *
    *  @param string $fetch_style        Controls how the rows will be returned.
    *  @param obj $obj                   The object to be fetched into for use with FETCH_INTO.
    *  @access public
    *  @return mixed
    */
    public function fetch($fetch_style = null, $obj = null) {
        $this->_set_fetch_mode($fetch_style, $obj);
        $row = $this->_statement->fetch();
        $this->_statement->closeCursor();
        return $row; 
    }

   /**
    *  Returns an array containing all of the result set rows.
    *
    *  @param string $fetch_style        Controls how the rows will be returned.
    *  @access public
    *  @return mixed
    */
    public function fetch_all($fetch_style = null) {
        $this->_set_fetch_mode($fetch_style);
        $rows = $this->_statement->fetchAll();
        $this->_statement->closeCursor();
        return $rows; 
    }

   /**
    *  Returns a single column from the next row of a result set or false if there are no more rows.
    *
    *  @param int $column_number         Zero-index number of the column to retrieve from the row.
    *  @access public
    *  @return mixed
    */
    public function fetch_column($column_number = 0) {
        $column = $this->_statement->fetchColumn($column_number);
        $this->_statement->closeCursor();
        return $column; 
    }

    public function find($fields, $table, $where = null, $additional = null) {
        $sql = 'SELECT ';
        if ( is_array($fields) ) {
            $sql .= '`' . implode('`, `', $fields) . '`';
        } else {
            $sql .= $fields;
        }

        if ( is_object($table) ) {
            $table = Meta::classname_only($table);
        }

        $sql .= ' FROM `' . $table . '`';
        $sql .= $this->_format_where($where);
        if ( !is_null($additional) ) {
            $sql .= ' ' . $additional;
        }
        $this->query($sql, $where); 
    }

    public function find_all_objects($obj, $where = null) {
        $results = array();
        $id = PRIMARY_KEY;

        $this->find('`' . $id . '`', $obj, $where);
        $this->_set_fetch_mode('assoc');
        while ( $row = $this->_statement->fetch() ) {
            $results[] = $row[$id];
        }
        $this->_statement->closeCursor();
        return $results;
    }

    public function find_object($obj, $where = null) {
        $id = PRIMARY_KEY;
        $this->find('`' . $id . '`', $obj, $where, 'LIMIT 1');
        return $this->fetch('assoc');
    }

    protected function _format_where($where = null) {
        $sql = '';

        if ( is_null($where) ) {
            return $sql;
        }

        $sql = ' WHERE ';
        if ( is_string($where) ) {
            $sql .= $where;
        } elseif ( is_array($where) ) {
            foreach ( $where as $name => $val ) {
                // $EXAMPLE = array( "i" => array( "\$gt" => 20, "\$lte" => 30 ) );
                if ( is_string($val) ) {
                    $sql .= '`' . $name . '`=:' . $name . ' and ';
                }
                elseif ( is_array($val) ) {
                    foreach ( $val as $sign => $constraint ) {
                        $new_name = $name .  '__' . $constraint;
                        $sql .=  '`' . $new_name . '` ';
                        switch ( $sign ) {
                            case 'gt':
                                $sql .= '>';
                                break;
                            case 'gte':
                                $sql .= '>=';
                                break;
                            case 'lt':
                                $sql .= '<';
                                break;
                            case 'lte':
                                $sql .= '<=';
                                break;
                            case 'e':
                            default:
                                $sql .= '=';
                                break;
                        }
                        $sql .= ':' . $new_name . ' and ';
                        $where[$new_name] = $constraint;
                        unset($where[$name]);
                    }
                }
            }
            $sql = substr($sql, 0, strlen($sql) - strlen(' and '));
        }

        return $sql;
    }

   /**
    *  Inserts an object into the database. 
    *
    *  @param obj $obj        The object to be inserted. 
    *  @access public
    *  @return bool
    */
    public function insert($obj) {
        $table = Meta::classname_only($obj);
        $properties = Meta::get_protected_vars($obj);

    	$sql = 'INSERT INTO `' . $table . '` ';
        
        $property_names = array_keys($properties);
    	$fields = '`' . implode('`, `', $property_names) . '`';
        $values = ':' . implode(', :', $property_names);
    
    	$sql .= '(' . $fields . ') VALUES (' . $values . ')';

    	$statement = $this->_dbh->prepare($sql);

        try {
            $statement->execute($properties);
    	} catch ( \PDOException $e ) {
            die($e->getMessage());
            // TODO: How to handle error reporting?
            return false;
        }

    	$this->_affected_rows = $statement->rowCount();
        return true;
    }

   /**
    *  Returns the ID of the last inserted row or sequence value.
    *
    *  @access public
    *  @return int
    */
    public function insert_id() {
        return $this->_dbh->lastInsertId();
    }

    public function load_object($obj, $id) {
        $where = array(PRIMARY_KEY => $id);
        $this->find('*', $obj, $where);
        return $this->fetch('into', $obj);
    }

   /**
    *  Returns the number of rows affected by the last SELECT query.
    *
    *  @access public
    *  @return int       
    */
    public function num_rows() {
        $this->query('SELECT FOUND_ROWS()');
        $rows = $this->fetch_column();
        return $rows;
    }

   /**
    *  Executes SQL query.
    *
    *  @param string $sql           The SQL query to be executed.
    *  @param array $parameters     An array containing the parameters to be bound.
    *  @access public
    *  @return bool 
    */
    public function query($sql, $parameters = null) {
        $statement = $this->_dbh->prepare($sql);

        if ( is_array($parameters) ) {
            foreach ( $parameters as $field => &$value ) {
                $statement->bindParam(':' . $field, $value);
            }
        }
        try {
            $statement->execute();
    	} catch ( \PDOException $e ) {
            die($e->getMessage());
            // TODO: How to handle error reporting?
            $this->_affected_rows = 0;
            return false;
        }
    
    	$this->_affected_rows = $statement->rowCount();
        $this->_statement = $statement;
    	return true;
    }

   /**
    *  Executes SQL query and returns the first row of the results.
    *
    *  @param string $sql                The SQL query to be executed.
    *  @param array $parameters          An array containing the parameters to be bound.
    *  @access public
    *  @return mixed       
    */
    public function query_first($sql, $parameters = null) {
        $this->query($sql . ' LIMIT 1', $parameters);
    }

   /**
    *  Sets the fetch mode.
    *
    *  @param string $fetch_style        Controls how the rows will be returned.
    *  @param obj $obj                   The object to be fetched into for use with FETCH_INTO.
    *  @access protected
    *  @return int 
    */
    protected function _set_fetch_mode($fetch_style, $obj = null) {
        switch ( $fetch_style ) {
            case 'assoc':
                $this->_statement->setFetchMode(\PDO::FETCH_ASSOC);
                break;
            case 'both':
                $this->_statement->setFetchMode(\PDO::FETCH_BOTH);
                break;
            case 'into':
                $this->_statement->setFetchMode(\PDO::FETCH_INTO, $obj);
                break;
            case 'lazy':
                $this->_statement->setFetchMode(\PDO::FETCH_LAZY);
                break;
            case 'num':
                $this->_statement->setFetchMode(\PDO::FETCH_NUM);
                break;
            case 'obj':
                $this->_statement->setFetchMode(\PDO::FETCH_OBJ);
                break;
            default:
                $this->_statement->setFetchMode(\PDO::FETCH_ASSOC);
                break;
        }
    }

   /**
    *  Updates an object in the database.
    *
    *  @param obj $obj              The object to be updated.
    *  @param string $where         The WHERE clause of the SQL query.
    *  @access public
    *  @return bool 
    */
    public function update($obj, $where = null) {
        $table = Meta::classname_only($obj);
        $properties = Meta::get_protected_vars($obj);

    	$sql = 'UPDATE `' . $table . '` SET ';
    
        $property_names = array_keys($properties);
    	foreach ( $property_names as $name ) {
            $sql .= '`' . $name . '`=:' . $name . ', ';
    	}

        $sql = rtrim($sql, ', ');

        if ( !is_null($where) ) {
    	    $sql .= ' WHERE ';
            foreach ( $where as $name => $val ) {
                $sql .= '`' . $name . '`=:' . $name . '_where, ';
                $properties[$name . '_where'] = $val;
            }
        }
    	$statement = $this->_dbh->prepare($sql);

        try {
            $statement->execute($properties);
    	} catch ( \PDOException $e ) {
            die($e->getMessage());
            // TODO: How to handle error reporting?
            return false;
        }
    
    	$this->_affected_rows = $statement->rowCount();
    	return true;
    }

   /**
    *  Inserts or updates (if exists) an object in the database.
    *
    *  @param obj $obj                The object to be upserted.
    *  @access public
    *  @return bool 
    */
    public function upsert($obj) {
        $table = Meta::classname_only($obj);
        $properties = Meta::get_protected_vars($obj);

    	$sql = 'INSERT INTO `' . $table . '` ';
        
        $property_names = array_keys($properties);
    	$fields = '`' . implode('`, `', $property_names) . '`';
        $values = ':' . implode(', :', $property_names);

    
        $sql .= '(' . $fields . ') VALUES (' . $values . ') ON DUPLICATE KEY UPDATE ';

    	foreach ( $property_names as $name ) {
            $sql .= '`' . $name . '`=:' . $name . ', ';
    	}

        $sql = rtrim($sql, ', ');
    	$statement = $this->_dbh->prepare($sql);

        try {
            $statement->execute($properties);
    	} catch ( \PDOException $e ) {
            die($e->getMessage() . $sql . var_dump($properties));
            // TODO: How to handle error reporting?
            return false;
        }

    	$this->_affected_rows = $statement->rowCount();
        return true; 
    }
}

?>
