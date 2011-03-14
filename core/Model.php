<?php
namespace core;

class Model
{
    protected $_repository;
    
    protected $_has_one = array();
    protected $_has_many = array();
    protected $_belongs_to = array();
    protected $_has_and_belongs_to_many = array(); 
    
    protected $_no_cache = array();
        
    // id can either be an unique identifier 
    // or a WHERE relationship
    public function __construct($id, $repository)
    {
        $this->_repository = $repository;
        
        if ( is_numeric($id) )
        {
            // TODO: Check cache for object first!
            $pk_id = PRIMARY_KEY;
            $this->$pk_id = $id;
            $this->_repository->load_object($this, $id);
        }
        elseif ( $id != '' )
        {
            $this->find_object($id); 
        }
    }
    
    public function __get($field_name)
    {
        $id = PRIMARY_KEY;

        if ( $this->belongs_to($field_name) )
        {
            $lookup_id = $field_name.PK_SEPARATOR.PRIMARY_KEY;
            $obj_id = $this->$lookup_id;
            return new $field_name($obj_id, $this->_repository); 
        }
        elseif ( $this->has_many($field_name) )
        {
            $lookup_id = get_class($this) . PK_SEPARATOR . PRIMARY_KEY;

            $obj = new $field_name(); 
            $where = array($lookup_id => $this->$id);
            return $this->_repository->find_all_objects($obj, $where);
        }
        elseif ( $this->has_one($field_name) )
        {
            $lookup_id = get_class($this) . PK_SEPARATOR . PRIMARY_KEY;

            $obj = new $field_name(); 
            $where = array($lookup_id => $this->$id);
            return $this->_repository->find_object($obj, $where);
        }
        elseif ( $this->habtm($field_name) )
        {
            $class_name = get_class($this);
            $table_name = ( $class_name < $field_name ) ? $class_name . HABTM_SEPARATOR . $field_name : $field_name . HABTM_SEPARATOR . $class_name;

            $lookup_id = $class_name . PK_SEPARATOR . PRIMARY_KEY;
            $id = PRIMARY_KEY;
            $where = array($lookup_id => $this->$id);

            $this->_repository->find($table_name, $where);

            $rows = $this->_repository->fetch_all('assoc');
            $results = array();
            foreach ( $rows as $row )
            {
                $new_id = $row[$field_name . PK_SEPARATOR . PRIMARY_KEY];
                $results[$new_id] = new $field_name($new_id, $this->_repository); 
            }
            return $results;
        }
        else
        {
            return $this->$field_name;
        }
    }
    
    public function __set($field_name, $value)
    {
        $this->$field_name = $value;
    }

    public function belongs_to($field_name)
    {
        return ( in_array($field_name, $this->_belongs_to) );
    }

    public function delete($where=null)
    {
        $this->_repository->delete($this, $where);
    }

    public function find_object($where)
    {
        return $this->_repository->find_object($this, $where);
    }

    public function habtm($field_name)
    {
        return ( in_array($field_name, $this->_has_and_belongs_to_many) );
    }
    
    public function has_many($field_name)
    {
        return ( in_array($field_name, $this->_has_many) );
    }

    public function has_one($field_name)
    {
        return ( in_array($field_name, $this->_has_one) );
    }

    public function is_foreign($field_name)
    {
        return ( $this->has_many($field_name) || $this->has_one($field_name) || 
                 $this->habtm($field_name) || $this->belongs_to($field_name) );
    }

    public function store()
    {
        $this->_repository->upsert($this);
        $id = PRIMARY_KEY;
        $this->$id = $this->_repository->insert_id();
    }
}

?>
