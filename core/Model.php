<?php
namespace core;

class Model
{
    protected $_repository;
    
    // storage area for database field values 
    protected $_fields= null;
    
    protected $_has_one= null;
    protected $_has_many= null;
    protected $_belongs_to= null;
    protected $_has_and_belongs_to_many= null; 
    
    // array of properties to NOT cache. 
    //  these will be ignored by __get() and store().
    protected $_no_cache= array();
        
    // TODO: Fix
    public function __construct($id, $repository)
    {
        $this->_repository = $repository;
        
        if ( $id )
        {
            // TODO: Check cache for object first!
            $this->fields = $this->_repository->load_object($this, $id);
        }
        elseif ( count($this->fields) < 1 )
        {
            $this->set_fields();
        }   
    }
    
    public function get_fields()
    {
        return $this->fields;
    }
    
    public function has_field($var)
    {
        return array_key_exists($var, $this->fields);
    }
    
    function is_belongs_to($field_name)
    {
        return ( in_array($field_name, $this->belongs_to) );
    }

    /**
     *  Whether or not a field has _any_ foreign key characteristics (has_many, has_one, habtm) 
     */
    function is_foreign($field_name)
    {
        return ( $this->is_has_many($field_name) || $this->is_has_one($field_name) || 
                 $this->is_habtm($field_name) || $this->is_belongs_to($field_name) );
    }

    function is_habtm($field_name)
    {
        return ( in_array($field_name, $this->has_and_belongs_to_many) );
    }
    
    function is_has_one($field_name)
    {
        return ( in_array($field_name, $this->has_one) );
    }

    function is_has_many($field_name)
    {
        return ( in_array($field_name, $this->has_many) );
    }
    
    /**
     * Generic __get. All $obj->property calls come through here.
     */
    function __get($field_name)
    {
        // if no_cache, then get from the database directly:
        if ( in_array($field_name, $this->no_cache) )
        {
            $fields = $this->_repository->load_object($this, $this->id);
            return $fields[$field_name];
        }
        
        $results = null;
        
        if ( $this->is_foreign($field_name) )
        {
            if ( $this->is_belongs_to($field_name) )
            {
                $lookup_id = $this->fields[$field_name.PK_SEPARATOR.PRIMARY_KEY];

                $obj = new $field_name(); 
                $results = $this->_repository->load_object($obj, $lookup_id);
            }

            if ( $this->is_has_many($field_name) )
            {
                // TODO: Return an array of all objects

                /*
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;
                
                // return a dummy object?
                // TODO: andrew doesn't like this. 
                $results= new $field_name();
                */
            }

            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;

                $results= $field_name::find_object(array($lookup_field=>$lookup_id));
            }
        }

        if ($results)
            return $results;
        else
            return stripslashes($this->fields[$field_name]); // remove sanitized escape
    }
    
    /**
     * Generic __set. All $obj->property= $val calls come through here.
     *  Note that Controller handles the weatherstripping of user input.
     */
    function __set($field_name, $value)
    {
        // if no_cache, then get from the database directly:
        if (array_search($field_name, $this->no_cache) !== false)
        {
            $this->fields[$field_name]= $value;
            static::$repository->get_database()->store_row(static::cname(), $this->fields);
            return; 
        }
        
        if ($this->is_foreign($field_name))
        {
            /*
            if (is_object($this->fields[$field_name]) == false)
            {
                $this->debug(5, "Refreshing ".$field_name." for __set");
                $this->refresh($field_name);
            }
            else
                $this->debug(5, $field_name." already loaded for __set");

            // now add to joiner:
            $this->fields[$field_name]->add_object($value);
            */
        }
        else
        {
            $this->fields[$field_name]= $value;
        }
    }
    
    /**
     * Find and return an object. Anything after "select * from Foo where" can be in your query.
     */
    // TODO: Fix
    public function find_object($query)
    {
        // Call $repository->find_object

        $classname= static::cname();
        $results= $classname::find($query);

        if ($results)
            return array_pop($results);
            
        return false;
    }

    public function delete()
    {
        $this->_repository->delete_object($this);
    }
    
    public function find($query=null)
    {
        $returns= array();
        $classname= static::cname();   
        $results= static::$repository->find_rows(static::cname(), $query);
        if (is_array($results))
        {
            foreach($results as $key=>$object_data)
            {
                $returns[]= new $classname($object_data[PRIMARY_KEY]);
            }
        }
        return $returns;
    }

    public function set_fields()
    {
        // TODO: Fix this
        $columns = $this->repository->get_table_columns(get_called_class());
        foreach( $columns as $col_name=>$col_type )
        {
            $this->fields[$col_name] = null;
        }
    }

    public function store()
    {
        $this->_repository->upsert($this);
        $id = PRIMARY_KEY;
        $this->$id = $this->_repository->insert_id;
    }
    
    
}

?>
