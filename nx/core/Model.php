<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\core;

use nx\lib\Connections;
use nx\lib\Data;
use nx\lib\Meta;
use nx\lib\Validator;

/*
 *  The `Model` class is the parent class of all
 *  application models.  Responsible for handling
 *  object relationships, it provides automatic
 *  interfacing with both databases and caches to
 *  allow for convenient object storage and retrieval.
 *  It also provides sanitization and validation mechanisms.
 *
 *  @package core
 */
class Model extends Object {

   /**
    *  The cache handler.
    *
    *  @var object
    *  @access protected
    */
    protected $_cache;

   /**
    *  The database handler.
    *
    *  @var object
    *  @access protected
    */
    protected $_db;

   /**
    *  The `belongs to` relationships pertaining to `$this`.
    *
    *  @var array
    *  @access protected
    */
    protected $_belongs_to = array();

   /**
    *  The `has one` relationships pertaining to `$this`.
    *
    *  @var array
    *  @access protected
    */
    protected $_has_one = array();

   /**
    *  The `has many` relationships pertaining to `$this`.
    *
    *  @var array
    *  @access protected
    */
    protected $_has_many = array();

   /**
    *  The `has and belongs to many` relationships pertaining to `$this`.
    *
    *  @var array
    *  @access protected
    */
    protected $_has_and_belongs_to_many = array();

   /**
    *  The meta information pertinent to objects
    *  and their relationships with other objects.
    *
    *  @var array
    *  @access protected
    */
    protected $_meta = array(
        'key'             => 'id',
        'pk_separator'    => '_',
        'habtm_separator' => '__'
    );

   /**
    *  The sanitizers to be used when parsing
    *  data.  Acceptable sanitizers are:
    *  `key` => `b` for booleans
    *  `key` => `f` for float/decimals
    *  `key` => `i` for integers
    *  `key` => `s` for strings
    *
    *  @see /nx/lib/Data::sanitize()
    *  @see /nx/core/Model->sanitize()
    *  @var array
    *  @access protected
    */
    protected $_sanitizers = array();

   /**
    *  The validators to be used when validating
    *  data.
    *
    *  @see /nx/lib/Validator
    *  @see /nx/core/Model->is_valid()
    *  @var array
    *  @access protected
    */
    protected $_validators = array();

   /**
    *  The validation error messages.
    *
    *  @see /nx/core/Model->is_valid()
    *  @var array
    *  @access protected
    */
    protected $_validation_errors = array();

   /**
    *  Initializes an object.  Takes the following configuration options:
    *  `id`       - The primary key of an existing object, used if you
    *               want to load an instance of an object with its
    *               properties from the cache/database.
    *  `where`    - A WHERE clause to be used if the primary key of the
    *               object desired is unknown.  Also used if you
    *               want to load an instance of an object with its
    *               properties from the cache/database.
    *  `no_cache` - Whether or not to use the cache to store/retrieve the object.
    *  `db`       - The name of the db connection to use as defined in app/config/bootstrap/db.php.
    *  `cache`    - The name of the cache connection to use as defined in app/config/bootstrap/cache.php.
    *
    *  @see /nx/core/Model->_init()
    *  @param array $config        The configuration options.
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'id'       => null,
            'where'    => null,
            'no_cache' => false,
            'db'       => 'default',
            'cache'    => 'default'
        );
        parent::__construct($config + $defaults);
    }

   /**
    *  Initializes an object.  Object properties are populated
    *  automatically - first by checking the cache, and then, if that
    *  fails, by retrieving the values from the database.
    *
    *  @see /nx/core/Model->__construct()
    *  @access protected
    *  @return void
    */
    protected function _init() {
        parent::_init();
        $this->_db = Connections::get_db($this->_config['db']);
        $this->_cache = Connections::get_cache($this->_config['cache']);

        if ( isset($this->_config['where']) ) {
            $this->_db->find('`' . $this->_meta['key'] . '`', $this->classname(), $this->_config['where'], 'LIMIT 1');
            $result = $this->_db->fetch('assoc');
            if ( $result ) {
                $this->_config['id'] = $result[$this->_meta['key']];
            }
        }

        if ( is_numeric($this->_config['id']) ) {
            if ( !$this->pull_from_cache($this, $this->_config['id']) ) {
                $where = array($this->_meta['key'] => $this->_config['id']);
                $this->_db->find('*', $this->classname(), $where);
                $this->_db->fetch('into', $this);

                $this->cache();
            }
        }
    }

   /**
    *  Returns an object's property.  If the property is bound to `$this`
    *  via an object relationship, the appropriate object (or array of objects)
    *  is returned.  If the property is an actual property belonging to `$this`,
    *  it will be returned.
    *
    *  @see /nx/core/Model->_get_belongs_to()
    *  @see /nx/core/Model->_get_has_many()
    *  @see /nx/core/Model->_get_has_one()
    *  @see /nx/core/Model->_get_habtm()
    *  @param string $field        The property name.
    *  @access public
    *  @return mixed
    */
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

   /**
    *  Sets an object's property.
    *
    *  @param string $field        The property name.
    *  @param mixed $value         The property value.
    *  @access public
    *  @return mixed
    */
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
        if ( $this->_config['no_cache'] ) {
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

        if ( is_null($where) ) {
            // TODO: Throw exception if id is null?
            $where = array($this->_meta['key'] => $obj->get_pk());
        }

        if ( !$this->_db->delete($this->classname(), $where) ) {
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
    public function find_all($where = null) {
        $this->_db->find('`' . $this->_meta['key'] . '`', $this->classname(), $where);
        $rows = $this->_db->fetch_all('assoc');

        $collection = array();
        $obj_name = get_class($this);
        foreach ( $rows as $row ) {
            $new_id = $row[$this->_meta['key']];
            $collection[$new_id] = new $obj_name(array('id' => $new_id));
        }
        return $collection;
    }

   /**
    *  Returns the object associated with `$this` via a
    *  "belongs to" relationship.
    *
    *  @param string $field        The object name.
    *  @access protected
    *  @return object
    */
    protected function _get_belongs_to($field) {
        $lookup_id = $field . $this->_meta['pk_separator'] . $this->_meta['key'];
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

   /**
    *  Returns an array of objects associated with `$this` via a
    *  "has and belongs to many" relationship.
    *
    *  @param string $field        The object name.
    *  @access protected
    *  @return array
    */
    protected function _get_habtm($field) {
        $class_name = get_class($this);
        if ( $class_name < $field ) {
            $table_name = $class_name . $this->_meta['habtm_separator'] . $field;
        } else {
            $table_name = $field . $this->_meta['habtm_separator'] . $class_name;
        }

        $lookup_id = $class_name . $this->_meta['pk_separator'] . $this->_meta['key'];
        $where = array($lookup_id => $this->get_pk());

        $target_id = $field . $this->_meta['pk_separator'] . $this->_meta['key'];
        $this->_db->find('`' . $target_id . '`', $table_name, $where);

        $rows = $this->_db->fetch_all('assoc');
        $collection = array();
        foreach ( $rows as $row ) {
            $new_id = $row[$target_id];
            $collection[$new_id] = new $field(array('id' => $new_id));
        }
        return $collection;
    }

   /**
    *  Returns an array of objects associated with `$this` via a
    *  "has many" relationship.
    *
    *  @param string $field        The object name.
    *  @access protected
    *  @return array
    */
    protected function _get_has_many($field) {
        $lookup_id = get_class($this) . $this->_meta['pk_separator'] . $this->_meta['key'];
        $where = array($lookup_id => $this->get_pk());

        return $this->find_all($where, $field);
    }

   /**
    *  Returns the object associated with `$this` via a
    *  "has one" relationship.
    *
    *  @param string $field        The object name.
    *  @access protected
    *  @return object
    */
    protected function _get_has_one($field) {
        $lookup_id = get_class($this) . $this->_meta['pk_separator'] . $this->_meta['key'];
        $where = array($lookup_id => $this->get_pk());


        $this->_db->find('`' . $this->_meta['key'] . '`', $field, $where, 'LIMIT 1');
        $result = $this->_db->fetch('assoc');
        $obj_id = $result[$this->_meta['key']];

        return new $field(array('id' => $obj_id));
    }

   /**
    *  Returns the primary key associated with `$this`.
    *
    *  @access public
    *  @return int
    */
    public function get_pk() {
        $id = $this->_meta['key'];
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
        if ( $this->_config['no_cache'] ) {
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
        $this->_db->upsert($this->classname(), $this->get_columns());
        // TODO: Check that caching works with UPDATEd objects!
        $id = $this->_meta['key'];
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
