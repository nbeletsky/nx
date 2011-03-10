<?php
namespace core;

class Model
{
    protected $_repository;
    
    protected $_has_one= null;
    protected $_has_many= null;
    protected $_belongs_to= null;
    protected $_has_and_belongs_to_many= null; 
    
    protected $_no_cache= array();
        
    public function __construct($id, $repository)
    {
        $this->_repository = $repository;
        
        if ( $id )
        {
            // TODO: Check cache for object first!
            $pk_id = PRIMARY_KEY;
            $this->$pk_id = $id;
            return $this->_repository->load_object($this, $id);
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
            // TODO: Fix
            return null;
        }
        else
        {
            $field_name = '_' . $field_name;
            return $this->$field_name;
        }
    }
    
    public function __set($field_name, $value)
    {
        $field_name = '_' . $field_name;
        $this->$field_name = $value;
    }

    public function belongs_to($field_name)
    {
        return ( in_array($field_name, $this->_belongs_to) );
    }

    public function delete()
    {
        $this->_repository->delete_object($this);
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
        $this->$id = $this->_repository->insert_id;
    }
}

?>
