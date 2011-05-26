<?php

namespace nx\core;

use nx\lib\Data;
use nx\lib\Meta;
use nx\lib\Validator;

class Model extends Object {

    protected $_classes = array(
        'db'    => 'nx\plugin\db\PDO_MySQL',
        'cache' => 'nx\plugin\cache\MemcachedCache'
    );

    protected $_db;
    protected $_cache;

    protected $_has_one = array();
    protected $_has_many = array();
    protected $_belongs_to = array();
    protected $_has_and_belongs_to_many = array(); 
    
    protected $_sanitizers = array();
    protected $_validators = array();
    protected $_validation_errors = array();

    protected $_no_cache = false;

    public function __construct(array $config = array()) {
        $defaults = array(
            'id'      => null,
            'where'   => null,
            'classes' => $this->_classes
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();
        $db = $this->_config['classes']['db'];
        $this->_db = new $db(); 
        $cache = $this->_config['classes']['cache'];
        $this->_cache = new $cache(); 
        
        if ( isset($this->_config['where']) ) {
            $result = $this->_db->find_object($this, $this->_config['where']); 
            if ( $result ) {
                $this->_config['id'] = $result[PRIMARY_KEY];
            }
        }

        if ( is_numeric($this->_config['id']) ) {
            if ( !$this->pull_from_cache($this, $this->_config['id']) ) {
                $this->_db->load_object($this, $this->_config['id']);
                $this->cache();
            }
        }
    }

    public function __get($field) {
        if ( $this->belongs_to($field) ) {
            return $this->_get_belongs_to($field);
        } elseif ( $this->has_many($field) ) {
            return $this->_get_has_many($field);
        } elseif ( $this->has_one($field) ) {
            return $this->_get_has_one($field);
        } elseif ( $this->habtm($field) ) {
            return $this->_get_habtm($field);
        }

        return $this->$field;
    }
    
    public function __set($field, $value) {
        $this->$field = $value;
    }

   /**
    *  Checks if `$this` has a "belongs to" relationship with the
    *  object defined in `$field`.
    *
    *  @param string $field        The class name of the foreign object. 
    *  @access public
    *  @return bool
    */
    public function belongs_to($field) {
        return ( in_array($field, $this->_belongs_to) );
    }

   /**
    *  Stores an object in the cache.  Only the object's
    *  "columns" (i.e., protected properties that are not prefixed 
    *  with an underscore) are serialized and stored.
    *
    *  @see /nx/lib/Meta::get_columns()
    *  @access public
    *  @return bool
    */
    public function cache() {
        if ( $this->_no_cache ) {
            return false;
        }

        $properties = $this->get_columns();
        $data = json_encode($properties);

        $key = get_class($this) . '_' . $this->get_pk();
        return $this->_cache->store($key, $data);
    }

   /**
    *  Deletes an object from both the cache and the database.
    *       
    *  @param string|array $where        The WHERE clause to be included in the DELETE query.
    *  @access public
    *  @return bool
    */
    public function delete($where = null) {
        $key = get_class($this) . '_' . $this->get_pk();
        if ( !$this->_cache->delete($key) ) {
            // TODO: Throw exception!
        }
        if ( !$this->_db->delete($this, $where) ) {
            // TODO: Throw exception!
        }
        return true;
    }

   /**
    *  Finds and returns an array of all the objects in the 
    *  database that match the conditions provided in `$where`. 
    *       
    *  @param string|array $where        The WHERE clause of the SQL query.
    *  @access public
    *  @return array
    */
    public function find_all($where = null, $obj = null) {
        if ( is_null($obj) ) {
            $obj = $this;
        }

        $all_obj_ids = $obj->_db->find_all_objects($obj, $where);

        $collection = array();
        $obj_name = get_class($obj);
        foreach ( $all_obj_ids as $obj_id ) {
            $collection[$obj_id] = new $obj_name(array('id' => $obj_id));
        }
        return $collection;
    }

    protected function _get_belongs_to($field) {
        $lookup_id = $field . PK_SEPARATOR . PRIMARY_KEY;
        $obj_id = $this->$lookup_id;

        return new $field(array('id' => $obj_id)); 
    }

   /**
    *  Retrieves the "columns" (i.e., protected properties 
    *  that are not prefixed with an underscore) belonging
    *  to `$this`.
    *
    *  @access public
    *  @return array
    */
    public function get_columns() {
        return Meta::get_columns($this);
    }

    protected function _get_habtm($field) {
        $class_name = get_class($this);
        $table_name = ( $class_name < $field ) ? $class_name . HABTM_SEPARATOR . $field : $field . HABTM_SEPARATOR . $class_name;

        $lookup_id = $class_name . PK_SEPARATOR . PRIMARY_KEY;
        $where = array($lookup_id => $this->get_pk());

        $target_id = $field . PK_SEPARATOR . PRIMARY_KEY;
        $this->_db->find('`' . $target_id . '`', $table_name, $where);

        $rows = $this->_db->fetch_all('assoc');
        $collection = array();
        foreach ( $rows as $row ) {
            $new_id = $row[$target_id];
            $collection[$new_id] = new $field(array('id' => $new_id)); 
        }
        return $collection;
    }

    protected function _get_has_many($field) {
        $lookup_id = get_class($this) . PK_SEPARATOR . PRIMARY_KEY;
        $where = array($lookup_id => $this->get_pk());

        return $this->find_all($where, $field);
    }

    protected function _get_has_one($field) {
        $lookup_id = get_class($this) . PK_SEPARATOR . PRIMARY_KEY;
        $where = array($lookup_id => $this->get_pk());

        $result = $this->_db->find_object($field, $where);
        $obj_id = $result[PRIMARY_KEY];

        return new $field(array('id' => $obj_id)); 
    }

   /**
    *  Returns the primary key associated with `$this`.
    *       
    *  @access public
    *  @return int
    */
    public function get_pk() {
        $id = PRIMARY_KEY;
        return $this->$id;
    }

   /**
    *  Retrieves the validators associated with a given property.
    *
    *  @param string $field        The object property.
    *  @access protected
    *  @return array
    */
    protected function _get_validators($field) {
        return ( isset($this->_validators[$field]) ) ? $this->_validators[$field] : array();
    }

   /**
    *  Checks if `$this` has a "has and belongs to many" 
    *  relationship with the object defined in `$field`.
    *
    *  @param string $field        The class name of the foreign object. 
    *  @access public
    *  @return bool
    */
    public function habtm($field) {
        return ( in_array($field, $this->_has_and_belongs_to_many) );
    }
    
   /**
    *  Checks if `$this` has a "has many" relationship with the
    *  object defined in `$field`.
    *
    *  @param string $field        The class name of the foreign object. 
    *  @access public
    *  @return bool
    */
    public function has_many($field) {
        return ( in_array($field, $this->_has_many) );
    }

   /**
    *  Checks if `$this` has a "has one" relationship with the
    *  object defined in `$field`.
    *
    *  @param string $field        The class name of the foreign object. 
    *  @access public
    *  @return bool
    */
    public function has_one($field) {
        return ( in_array($field, $this->_has_one) );
    }

   /**
    *  Checks that a specific object property is valid.  If no property
    *  is supplied, then all of the object's "columns" (i.e., protected 
    *  properties that are not prefixed with an underscore) will be validated.  
    *  Corresponding errors are stored in $this->_validation_errors.
    *       
    *  @see /nx/lib/Validator
    *  @param string|null $field        The object property to be validated.
    *  @access public
    *  @return bool
    */
    public function is_valid($field = null) {
        if ( empty($this->_validators) ) {
            return true;
        }
        
        if ( !is_null($field) ) {
            $this->_validation_errors = $this->_validate($field);
        } else {
            $this->_validation_errors = array();
            foreach ( array_key_values($this->get_columns()) as $field ) {
                $this->_validation_errors += $this->_validate($field); 
            }
        }
        return ( empty($this->_validation_errors) );
    }

   /**
    *  Retrieves an object from the cache.
    *
    *  @param object $obj        The object to be populated with the retrieved values.
    *  @param int $id            The unique identifier of the object to be retrieved.
    *  @access public
    *  @return object
    */
    public function pull_from_cache($obj, $id) {
        if ( $this->_no_cache ) {
            return false;
        }

        $key = get_class($obj) . '_' . $id;
        $cached_data = $obj->_cache->retrieve($key);
        if ( !$cached_data ) {
            return false;
        }

        $cached_obj = json_decode($cached_data, true);
        foreach ( $cached_obj as $key => $val ) {
            $obj->$key = $val;
        }
        return $obj;
    }

   /**
    *  Sanitizes an object's properties in accordance with the sanitizers
    *  defined in $this->_sanitzers.
    *       
    *  @access public
    *  @return object
    */
    public function sanitize() {
        foreach ( $this->_sanitizers as $property => $type ) {
            $this->$property = Data::sanitize($this->$property, $type);
        }
        return $this;
    }

   /**
    *  Stores an object in both the database and the cache.
    *       
    *  @access public
    *  @return bool
    */
    public function store() {
        if ( !$this->is_valid() ) {
            return false;
        }
        $this->_db->upsert($this);
        // TODO: Check that caching works with UPDATEd objects!
        $id = PRIMARY_KEY;
        if ( !$this->$id ) {
            $this->$id = $this->_db->insert_id();
        }
        $this->cache();
        return true;
    }

   /**
    *  Validates a property of an object in accordance with the
    *  validators defined in $this->_validators.  Returns an array
    *  of error messages.
    *       
    *  @param string $field        The object property to be validated.
    *  @access protected
    *  @return array
    */
    protected function _validate($field) {
        $errors = array();
        $validators = $this->_get_validators($field);
        if ( empty($validators) ) {
            return $errors;
        }

        foreach ( $validators as $validator ) {
            $method = $validator[0];
            if ( isset($validator['options']) ) {
                $valid = Validator::$method($this->$field, $validator['options']);
            } else {
                $valid = Validator::$method($this->$field);
            }

            if ( !$valid ) {
                if ( !isset($errors[$field]) ) {
                    $errors[$field] = array();
                }
                $errors[$field][] = $validator['message'];
            }
        }
        return $errors;
    }

}

?>
