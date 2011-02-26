<?php
namespace core;

class Model
{
    protected $_repository;
    
    // storage area for database field values 
    protected $_fields= null;
    
    // storage area for database field types (datetime, int, etc)
    protected $_field_types= null;   
    
    protected $_has_many= null;
    protected $_has_one= null;
    protected $_belongs_to= null;
    protected $_has_and_belongs_to_many= null; 
    
    // you can override relationships by naming a method
    //  get_[relatedclass]() or set
    //  $getter_override= array('[relatedclass]'=>'[method]')
    protected $_getter_override= null;
    
    // array of classes that will be autocreated when asked for via find()
    protected $_requires= null;
    
    // array of properties to NOT cache. 
    //  these will be ignored by __get() and store().
    protected $_no_cache= array();
        
    // TODO: Fix
    // TODO: Repository needs load_row
    public function __construct($id= null, $repository= null)
    {
        if ( $repository )
        {
            $this->_repository = $repository;
        }
        
        if ( $this->$repository )
        {
            if ( $id )
            {
                $this->fields = $this->_repository->load_row(get_called_class(), $id);
                $this->set_field_types();
            }
            elseif ( count($this->fields) < 1 )
            {
                $this->set_field_types(true);
            }   
        }
    }
    
    public function requires_a($classname)
    {
        if (!$this->requires) return false;
        return (array_search($classname, $this->requires) !== false);
    }
    
    public function set_field_types($nullify= false)
    {
        if (static::$repository)
        {
            $columns= static::$repository->get_table_columns(Meta::classname_only(static::classname()));
            foreach($columns as $col_name=>$col_type)
            {
                if ($nullify)
                    $this->fields[$col_name]= null;
                
                $this->field_types[$col_name]= preg_replace("/\(([0-9])*\)/", "", $col_type);
            }
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
    
    public function get_field_types()
    {
        return $this->field_types;
    }
    
    public function get_field_type($field)
    {
        return $this->field_types[$field];
    }
    
    /**
     *  Whether or not a field has _any_ foreign key characteristics (has_many, has_one, habtm) 
     */
    function is_foreign($field_name)
    {
        return ( $this->is_has_many($field_name) || $this->is_has_one($field_name) || 
                 $this->is_habtm($field_name) || $this->is_belongs_to($field_name) );
    }
    
    /**
     * Whether or not a field is a belongs_to relationship
     */
    function is_belongs_to($field_name)
    {
        return ( count($this->belongs_to) && in_array($field_name, $this->belongs_to) );
    }
    
    /**
     *  Whether or not a field is a has_many relationship
     */
    function is_has_many($field_name)
    {
        return ( count($this->has_many) && in_array($field_name, $this->has_many) );
    }
    
    /**
     * Whether or not a field is a has_one relationship
     */
    function is_has_one($field_name)
    {
        return ( count($this->has_one) && in_array($field_name, $this->has_one) );
    }
    
    /**
     * Whether or not a field is a habtm relationship
     */
    function is_habtm($field_name)
    {
        return ( count($this->has_and_belongs_to_many) && in_array($field_name, $this->has_and_belongs_to_many) );
    }
    
    /**
     * Generate the join table name for field_name
     */
    function get_habtm_join_table($field_name)
    {
        $my_class = classname_only(static::classname());
        return ($field_name > $my_class) ? $my_class.PLOOF_SEPARATOR.$field_name : $field_name.PLOOF_SEPARATOR.$my_class;
    }
    
    /**
     * If a field_name is foreign, then refresh the data it points to.
     * Used to make sure cached data is up-to-date.
     */
    function refresh($field_name, $sort_fun=null, $order=null, $limit=null)
    {           
        $joiner= false;
        
        // Check to see if we need to override the getter by calling a different
        //  method:
        if (($this->getter_override and array_key_exists($field_name, $this->getter_override)) or method_exists($this, "get_".$field_name))
        {
            $method= ($this->getter_override and array_key_exists($field_name, $this->getter_override)) ?  $this->getter_override[$field_name] : "get_".$field_name;
            $this->debug(5, "Calling override ".$method." for refresh of ".$field_name);
            $results= $this->$method($order, $limit);
        }
        elseif($this->is_habtm($field_name))
        {   
            // joiner handles everything for habtm since it has to load
            //  and know about extra fields in the join table.
            //$joiner= new Joiner($this, static::cname(), $field_name);
        }
        else 
        {
            if ($this->is_belongs_to($field_name))
            {
                $lookup_id= $this->fields[$field_name.PK_SEPARATOR.PRIMARY_KEY];
                $lookup_field= PRIMARY_KEY;
            
                $results= $field_name::find(array($lookup_field=>$lookup_id));
            }
        
            if ($this->is_has_many($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;
                
                //$sql= $lookup_field."='".$lookup_id."' ".$order." ".$limit;
                
                //$this->debug(5, $field_name." sql=".$sql);
                
                $results= $field_name::find(array($lookup_field=>$lookup_id));
            }
            
            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;
                
                $results= $field_name::find(array($lookup_field=>$lookup_id));
            }
        }
        
        if ($sort_fun)
            usort($results, $sort_fun);
        
        //if (!$joiner)
        //{
        //    $joiner= new Joiner();
        //    $joiner->set_objects($results);
        //    $joiner->set_parent($this, classname_only(static::classname()));
        //    $joiner->set_child_class($field_name);         
        //}
        
        $this->fields[$field_name]= $joiner;
    }
    
    /**
     * Generic __get. All $obj->property calls come through here.
     */
    function __get($field_name)
    {
        // if no_cache, then get from the database directly:
        if (array_search($field_name, $this->no_cache) !== false)
        {
            // access database directly, bypass any cache access:
            $fields= static::$repository->get_database()->load(static::cname(), $this->id);
            return $fields[$field_name];
        }
        
        $results= null;
        
        if ($this->is_foreign($field_name))
        {
            $this->debug(5, "Found foreign: ".$field_name);
            
            if ($this->is_belongs_to($field_name))
            {
                $lookup_id= $this->fields[$field_name.PK_SEPARATOR.PRIMARY_KEY];
                $lookup_field= PRIMARY_KEY;

                $results= $field_name::find_object(array($lookup_field=>$lookup_id));
            }

            if ($this->is_has_many($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;
                
                // return a dummy object?
                // TODO: andrew doesn't like this. 
                $results= new $field_name();
            }

            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;

                $results= $field_name::find_object(array($lookup_field=>$lookup_id));
            }
        }

        // call the datetime handler if this is a datetime:
        if (array_key_exists($field_name, $this->field_types))
        {
            if (static::$repository->get_database()->is_date_datatype($this->field_types[$field_name]))
            {
                return Format::date(format_date($this->fields[$field_name]));
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
            // call the datetime handler if this is a datetime:
            if (array_key_exists($field_name, $this->field_types) and static::$repository->get_database()->is_date_datatype($this->field_types[$field_name]) and strlen($value) > 0)
            {
                $value = Format::date_sql($value);
            }
            
            $this->fields[$field_name]= $value;
        }
    }
    
    /**
     *  Find and overwrite, or create and store, an object of $classname
     *      with $values_array that has a relationship to $this. 
     *      Use store() on an object, save() on a relationship.
     *      TODO: This should be moved into joiner, which deals with relationships...
     */
    function save($classname, $values_array)
    {
        if ( !is_array($values_array) ) return false;

        // we'll return whatever we create / add / update:
        $return= null;
        if ($this->is_belongs_to($classname))
        {
            $this->debug(5, 'Saving '.$classname.' (belongs_to) with:');
            $this->debug(5, $values_array);
            
            // Question: Do we need a refresh check here? No because $this->$classname calls
            //  the getter (which should be an object of the type described by the belongs_to)
            if (is_object($this->$classname))
            {
                $this->debug(5, $classname.' exists, id '.$this->$classname->id);
                // this is a belongs to, so only the first is item is touched:
                $this->$classname->populate_from($values_array[$classname]);
                $this->$classname->store();
            }
            else
            {
                $object= new $classname();
                $object->fields[PRIMARY_KEY]= null;
                $object->populate_from($values_array[$classname]);
                $object->store();
                $return= $object;
                
                $lookup_field= $classname.PK_SEPARATOR.PRIMARY_KEY;
                $this->$lookup_field= $object->id;
                $this->store();
                
                $this->debug(5, $classname.' does not exist; adding id '.$this->$classname->id);
            }
            $this->refresh($classname);
        } // end belongs_to
        if ($this->is_has_one($classname))
        {
            $this->debug(5, 'Saving '.$classname.' (has_one) with:');
            $this->debug(5, $values_array);
            
            if (is_object($this->$classname))
            {
                $this->debug(5, $classname.' exists, id '.$this->$classname->id);
                $this->$classname->populate_from($values_array[$classname], 0);
                $this->$classname->store();
                $return= $this->$classname;
            }
            else
            {
                $this_class= classname_only(static::classname());
                $lookup_field= $this_class.PK_SEPARATOR.PRIMARY_KEY;
                
                $object= new $classname();
                $object->fields[PRIMARY_KEY]= null;
                $object->$lookup_field= $this->fields[PRIMARY_KEY];
                $object->populate_from($values_array[$classname], 0);
                $object->store();
                $return= $object;
                $this->debug(5, $classname.' does not exist; adding id '.$this->$classname->id);
            }
        }    // end has_one
        if ($this->is_has_many($classname))
        {
            $this->debug(5, 'Saving '.$classname.' (has_many) with:');
            $this->debug(5, $values_array);
            
            if (is_object($this->$classname))
            {
                $this->debug(5, 'Currently '.count($this->$classname->find()).' exist; repopulating with new values...');
                $this_class= classname_only(static::classname());
                $lookup_field= $this_class.PK_SEPARATOR.PRIMARY_KEY;
                
                if (!count($values_array))
                {
                    $this->debug(5, "No data; adding raw object");
                    // add a new one:
                    $obj= new $classname();
                    $obj->$lookup_field= $this->id;
                    $obj->store();
                    $return= $obj;
                    $this->refresh($classname);
                }
                else
                {
                    foreach($values_array[$classname] as $property=>$index)
                    {
                        $number_of_records= count($values_array[$classname][$property]);
                        break; 
                    }
                    
                    for($i=0; $i<$number_of_records; $i++)
                    {
                        if (array_key_exists(PRIMARY_KEY, $values_array[$classname]))
                        {
                            $obj= $this->$classname->find_object(array(PRIMARY_KEY=>$values_array[$classname][PRIMARY_KEY][$i]));
                            if ($obj) 
                            {
                                $this->debug(5, 'Found '.$obj->id);
                                $obj->populate_from($values_array[$classname], $i);
                                $obj->store();
                                $return[]= $obj;
                            }
                        }
                        else
                        {
                            $this->debug(5, 'Not found, creating');
                            $return[]= $this->$classname->add_array($values_array[$classname], $i);
                        }
                    }
                }
            }
        } // end has_many
        return $return;
    } // end save()
    
    /**
     * Auto populate the fields from an array
     */
    function populate_from($arr, $index= null)
    {
        if (is_array($arr))
        {
            foreach($arr as $key=>$value)
            {   
                if (array_key_exists($key, $this->field_types) and $this->is_foreign($key) === false)
                {
                    //$this->fields[$key]= ($index === null) ? $this->sanitize($key, $value) : $this->sanitize($key, $value[$index]);
                    // use set to ensure datetimes are handled
                    $this->__set($key, ($index === null) ? $this->sanitize($key, $value) : $this->sanitize($key, $value[$index]));
                    $this->debug(5,"Populating $key as " . $this->fields[$key]);
                }
            }
        }
    }
    
    /**
     * Find and return an object. Anything after "select * from Foo where" can be in your query.
     */
    static function find_object($query)
    {
        $classname= static::cname();
        $results= $classname::find($query);

        if ($results)
            return array_pop($results);
            
        return false;
    }

    // TODO: Repository needs a delete
    public function delete()
    {
        $this->_repository->delete_row(static::cname(), $this->id);
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
    
    public function store()
    {
        $this->_repository->upsert($this);
        $this->id = $this->_repository->insert_id;
    }
    
    
}

?>
