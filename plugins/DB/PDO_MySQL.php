<?php
namespace plugins\DB;

class PDO_MySQL implements \core\PluginInterfaceDB 
{
   /**
    *  The db handle. 
    *
    *  @var object
    *  @access private
    */
    private $_dbh;

   /**
    *  Number of rows affected by MySQL query.
    *
    *  @var int
    *  @access private
    */
    private $_affected_rows = 0;

   /**
    *  The result set associated with a prepared statement.
    *
    *  @var PDOStatement
    *  @access private
    */
    private $_statement;

   /**
    *  Connects and selects database.
    *
    *  @access public
    *  @return void
    */
    public function __construct($host, $database, $username, $password) 
    {
        $this->connect($host, $database, $username, $password);
    }

    public function connect($host, $database, $username, $password) 
    {
        $dsn = 'mysql:host=' . $host . ';dbname=' . $database; 
        try 
        {
            $this->_dbh = new \PDO($dsn, $username, $password);
            $this->_dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        catch ( PDOException $e ) 
        {
            // TODO: How to handle error reporting?
        }
    }

   /**
    *  Returns the number of rows affected by the last DELETE, INSERT, or UPDATE query.
    *
    *  @access public
    *  @return int
    */
    public function affected_rows() 
    {
    	return $this->_affected_rows;
    }

   /**
    *  Closes the connection.
    *
    *  @access public
    *  @return void
    */
    public function close()
    {
        $this->_dbh = null;
    }
    
    public function delete($obj, $where=null)
    {
        $sql = 'DELETE FROM `' . get_class($obj) . '`';
        if ( is_null($where) )
        {
            $id = PRIMARY_KEY;
            $where = array(PRIMARY_KEY => $obj->$id);
        }

        $sql .= $this->_format_where($where);

        if ( is_array($where) )
        {
            return $this->query($sql, $where);
        }
        // $where is a string
        else
        {
            return $this->query($sql);
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
    public function fetch($fetch_style=null, $obj=null) 
    {
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
    public function fetch_all($fetch_style=null) 
    {
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
    public function fetch_column($column_number=0) 
    {
        $column = $this->_statement->fetchColumn($column_number);
        $this->_statement->closeCursor();
        return $column; 
    }

    private function _find($obj, $where=null)
    {
        $sql = 'SELECT * FROM `' . get_class($obj) . '`';
        $sql .= $this->_format_where($where);
        return $sql;
    }

    public function find_all_objects($obj, $where=null)
    {
        $results = array();
        $id = PRIMARY_KEY;

        $sql = $this->_find($obj, $where);
        $this->query($sql, $where);
        $this->_set_fetch_mode('into', $obj);
        while ( $row = $this->_statement->fetch() )
        {
            $results[$row->$id] = clone $row;
        }
        $this->_statement->closeCursor();
        return $results;
    }

    public function find_habtm($from_obj, $to_find_obj)
    {
        $from_name = get_class($from_obj);
        $to_find_name = get_class($to_find_obj);
        $table_name = ( $from_name < $to_find_name ) ? $from_name . HABTM_SEPARATOR . $to_find_name : $to_find_name . HABTM_SEPARATOR . $from_name;

        $sql = 'SELECT * FROM `' . $table_name . '`';

        $lookup_id = $from_name . PK_SEPARATOR . PRIMARY_KEY;
        $id = PRIMARY_KEY;
        $where = array($lookup_id => $from_obj->$id);

        $sql .= $this->_format_where($where);
        $this->query($sql, $where);

        $rows = $this->fetch_all('assoc');
        $this->_statement->closeCursor();
        $results = array();
        foreach ( $rows as $row )
        {
            $new_id = $row[$to_find_name . PK_SEPARATOR . PRIMARY_KEY];
            $results[] = clone $this->load_object($to_find_obj, $new_id);
        }
        return $results;
    }

    public function find_object($obj, $where=null)
    {
        $sql = $this->_find($obj, $where);
        $sql .= ' LIMIT 1';

        if ( !$this->query($sql, $where) )
        {
            return false;
        }
        return $this->fetch('into', $obj);
    }

    private function _format_where($where=null)
    {
        $sql = '';
        if ( !is_null($where) )
        {
            $sql = ' WHERE ';
            if ( is_array($where) )
            {
                $field_names = array_keys($where);
                foreach ( $field_names as $name ) 
                {
                    $sql .= '`' . $name . '`=:' . $name . ', ';
                }
                $sql = rtrim($sql, ', ');
            }
            // $where is a string
            else
            {
                $sql .= $where;
            }
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
    public function insert($obj) 
    {
        $table = get_class($obj);
        $meta = new \lib\Meta();
        $properties = $meta->get_private_vars($obj);

    	$sql = 'INSERT INTO `' . $table . '` ';
        
        $property_names = array_keys($properties);
    	$fields = '`' . implode('`, `', $property_names) . '`';
        $values = ':' . implode(', :', $property_names);
    
    	$sql .= '(' . $fields . ') VALUES (' . $values . ')';

    	$statement = $this->_dbh->prepare($sql);

        try 
        {
            $statement->execute($properties);
    	}
        catch ( \PDOException $e ) 
        {
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
    public function insert_id()
    {
        return $this->_dbh->lastInsertId();
    }

    public function load_object($obj, $id)
    {
        $sql = 'SELECT * FROM `' . get_class($obj) . '` WHERE `' . PRIMARY_KEY . '`=:' . PRIMARY_KEY;
        $params = array(PRIMARY_KEY => $id);
        $query = $this->query($sql, $params);
        return $this->fetch('into', $obj);
    }

   /**
    *  Returns the number of rows affected by the last SELECT query.
    *
    *  @access public
    *  @return int       
    */
    public function num_rows()
    {
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
    public function query($sql, $parameters=null) 
    {
        $statement = $this->_dbh->prepare($sql);

        if ( is_array($parameters) ) 
        {
            foreach ( $parameters as $field => &$value ) 
            {
                $statement->bindParam(':' . $field, $value);
            }
        }
        try 
        {
            $statement->execute();
    	}
        catch ( \PDOException $e ) 
        {
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
    public function query_first($sql, $parameters=null) 
    {
        $this->query($sql . ' LIMIT 1', $parameters);
    }

   /**
    *  Sets the fetch mode.
    *
    *  @param string $fetch_style        Controls how the rows will be returned.
    *  @param obj $obj                   The object to be fetched into for use with FETCH_INTO.
    *  @access private
    *  @return int 
    */
    private function _set_fetch_mode($fetch_style, $obj=null) 
    {
        switch ( $fetch_style ) 
        {
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
    public function update($obj, $where=null) 
    {
        $table = get_class($obj);
        $meta = new lib\Meta();
        $properties = $meta->get_private_vars($obj);

    	$sql = 'UPDATE `' . $table . '` SET ';
    
        $property_names = array_keys($properties);
    	foreach ( $property_names as $name ) 
        {
            $sql .= '`' . $name . '`=:' . $name . ', ';
    	}

        $sql = rtrim($sql, ', ');

        if ( !is_null($where) )
        {
    	    $sql .= ' WHERE ';
            foreach ( $where as $name => $val ) 
            {
                $sql .= '`' . $name . '`=:' . $name . '_where, ';
                $properties[$name . '_where'] = $val;
            }
        }
    	$statement = $this->_dbh->prepare($sql);

        try 
        {
            $statement->execute($properties);
    	}
        catch ( \PDOException $e ) 
        {
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
    public function upsert($obj) 
    {
        $table = get_class($obj);
        $meta = new \lib\Meta();
        $properties = $meta->get_private_vars($obj);

    	$sql = 'INSERT INTO `' . $table . '` ';
        
        $property_names = array_keys($properties);
    	$fields = '`' . implode('`, `', $property_names) . '`';
        $values = ':' . implode(', :', $property_names);

    
        $sql .= '(' . $fields . ') VALUES (' . $values . ') ON DUPLICATE KEY UPDATE `' . PRIMARY_KEY . '`=LAST_INSERT_ID(`' . PRIMARY_KEY . '`), ';

    	foreach ( $property_names as $name ) 
        {
            if ( $name !== PRIMARY_KEY )
            {
                $sql .= '`' . $name . '`=:' . $name . ', ';
            }
    	}

        $sql = rtrim($sql, ', ');
    	$statement = $this->_dbh->prepare($sql);

        try 
        {
            $statement->execute($properties);
    	}
        catch ( \PDOException $e ) 
        {
            die($e->getMessage() . $sql . var_dump($properties));
            // TODO: How to handle error reporting?
            return false;
        }

    	$this->_affected_rows = $statement->rowCount();
        return true; 
    }
}

?>
