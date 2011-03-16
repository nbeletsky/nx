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
        
        if ( !is_numeric($id) && $id !== '' )
        {
            $result = $this->find_object($id); 
            $id = $result[PRIMARY_KEY];
        }

        if ( is_numeric($id) )
        {
            if ( !$this->pull_from_cache($this, $id) )
            {
                $this->_repository->load_object($this, $id);
                $this->cache();
            }
        }

    }
    
    public function __get($field_name)
    {
        if ( !$this->is_foreign($field_name) )
        {
            return $this->$field_name;
        }

        $id = PRIMARY_KEY;

        if ( $this->belongs_to($field_name) )
        {
            $lookup_id = $field_name . PK_SEPARATOR . PRIMARY_KEY;
            $obj_id = $this->$lookup_id;

            return new $field_name($obj_id, $this->_repository); 
        }
        elseif ( $this->has_many($field_name) )
        {
            $obj = new $field_name(); 
            $lookup_id = get_class($this) . PK_SEPARATOR . PRIMARY_KEY;
            $where = array($lookup_id => $this->$id);
            $all_obj_ids = $this->_repository->find_all_objects($obj, $where);

            $collection = array();
            foreach ( $all_obj_ids as $obj_id )
            {
                $cached_obj = $obj->pull_from_cache($obj, $obj_id); 
                if ( !$cached_obj )
                {
                    $collection[$obj_id] = $obj->_repository->load_object($obj, $obj_id);
                    $obj->cache();
                }
                else
                {
                    $collection[$obj_id] = $cached_obj;
                }
            }
            return $collection;
        }
        elseif ( $this->has_one($field_name) )
        {
            $obj = new $field_name(); 
            $lookup_id = get_class($this) . PK_SEPARATOR . PRIMARY_KEY;
            $where = array($lookup_id => $this->$id);
            $result = $this->_repository->find_object($obj, $where);
            $obj_id = $result[PRIMARY_KEY];

            $cached_obj = $obj->pull_from_cache($obj, $obj_id); 
            if ( !$cached_obj )
            {
                $obj->_repository->load_object($obj, $obj_id);
                $obj->cache();
                return $obj;
            }
            else
            {
                return $cached_obj;
            }
        }
        elseif ( $this->habtm($field_name) )
        {
            $class_name = get_class($this);
            $table_name = ( $class_name < $field_name ) ? $class_name . HABTM_SEPARATOR . $field_name : $field_name . HABTM_SEPARATOR . $class_name;

            $lookup_id = $class_name . PK_SEPARATOR . PRIMARY_KEY;
            $id = PRIMARY_KEY;
            $where = array($lookup_id => $this->$id);

            $target_id = $field_name . PK_SEPARATOR . PRIMARY_KEY;
            $this->_repository->find('`' . $target_id . '`', $table_name, $where);

            $rows = $this->_repository->fetch_all('assoc');
            $collection = array();
            foreach ( $rows as $row )
            {
                $new_id = $row[$target_id];
                $collection[$new_id] = new $field_name($new_id, $this->_repository); 
            }
            return $collection;
        }
    }
    
    public function __set($field_name, $value)
    {
        $this->$field_name = $value;
    }

    public function __sleep()
    {
        $meta = new \lib\Meta();
        $properties = $meta->get_protected_vars($obj);
        return array_keys($properties);
    }

    public function belongs_to($field_name)
    {
        return ( in_array($field_name, $this->_belongs_to) );
    }

    public function cache()
    {
        $data = serialize($this);
        $id = PRIMARY_KEY;
        $key = get_class($this) . '_' . $this->$id;
        $this->_repository->set_in_cache($key, $data);
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

    public function pull_from_cache($obj, $id)
    {
        $key = get_class($obj) . '_' . $id;
        $cached_data = $obj->_repository->get_from_cache($key);
        if ( !$cached_data )
        {
            return false;
        }

        // TODO: What to do with the cached data?  Is this correct?
        $cached_obj = unserialize($cached_data);
        foreach ( $cached_obj as $key=>$val )
        {
            $obj->$key = $val;
        }
        return $obj;
    }

    public function store()
    {
        $this->_repository->upsert($this);
        $id = PRIMARY_KEY;
        $this->$id = $this->_repository->insert_id();
        $this->cache();
    }

}

?>
