<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\plugin\db;

/*
 *  The `SimpleDB` class is used to connect to Amazon's SimpleDB.
 *
 *  @package plugin
 */
class SimpleDB extends \nx\core\Object {

   /**
    *  The data required for authentication.
    *
    *  @var array
    *  @access protected
    */
    protected $_authentication_details = array();

   /**
    *  The id of the last inserted record.
    *
    *  @var string
    *  @access protected
    */
    protected $_last_insert_id = null;

   /**
    *  The token used to retrieve the next page of results.
    *
    *  @var string
    *  @access protected
    */
    protected $_next_token = null;

   /**
    *  The results retrieved from a find() call.
    *
    *  @var array
    *  @access protected
    */
    protected $_select_results = array();

   /**
    *  Loads the configuration settings for a SimpleDB connection.
    *
    *  @param array $config         The configuration settings,
    *                               which can take the following options:
    *                               `region`            - The region.
    *                               `version`           - The version of the API.
    *                               `access_key`        - The access key id.
    *                               `primary_key`       - The primary key (used
    *                                                     to identify records).
    *                               `secret_key`        - The secret access key.
    *                               `signature_method`  - The signature method.
    *                               `signature_version` - The AWS signature
    *                                                     version (currently 2).
    *                               `scheme`            - The protocol (http or
    *                                                     https) to use when
    *                                                     making REST requests.
    *                               `classes`           - Dependency classes.
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'region'            => 'sdb.amazonaws.com',
            'version'           => '2009-04-15',
            'access_key'        => '',
            'primary_key'       => 'id',
            'secret_key'        => '',
            'signature_method'  => 'HmacSHA256',
            'signature_version' => 2,
            'scheme'            => 'http',
            'classes'           => array(
                'rest' => 'nx\lib\Rest'
            )
        );
        parent::__construct($config + $defaults);
    }

   /**
    *  Sets the common authentication details.
    *
    *  @access protected
    *  @return void
    */
    protected function _init() {
        $this->_authentication_details = array(
            'AWSAccessKeyId'   => $this->_config['access_key'],
            'SignatureMethod'  => $this->_config['signature_method'],
            'SignatureVersion' => $this->_config['signature_version'],
            'Version'          => $this->_config['version']
        );
    }

   /**
    *  Properly urlencodes request parameters in accordance with Amazon's
    *  guidelines.
    *
    *  @see http://docs.amazonwebservices.com/AmazonSimpleDB/latest/DeveloperGuide/
    *  @param array $parameters     The parameters to be urlencoded.
    *  @access protected
    *  @return string
    */
    protected function _build_query($parameters = array()) {
        $components = array();
        foreach ( $parameters as $key => $value ) {
            if ( is_string($key) && !is_array($value) ) {
                $components[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }
        return implode('&', $components);
    }

   /**
    *  Builds a REST request and sends it.
    *
    *  @param array $parameters     The parameters to be encoded.
    *  @access protected
    *  @return string
    */
    protected function _craft_request($parameters) {
        $parameters += array(
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        ) + $this->_authentication_details;

        $parameters['Signature'] = $this->_create_signature($parameters);

        $query_string = $this->_build_query($parameters);
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'
        );

        $url = $this->_config['scheme'] . '://' . $this->_config['region'] . '/';
        $rest = $this->_config['classes']['rest'];
        $response = $rest::post($url, $query_string, $headers);

        return $response;
    }

   /**
    *  Properly urlencodes a signable string in accordance with Amazon's
    *  guidelines.
    *
    *  @see http://docs.amazonwebservices.com/AmazonSimpleDB/latest/DeveloperGuide/
    *  @param array $parameters     The parameters to be encoded.
    *  @access protected
    *  @return string
    */
    protected function _create_signable_string($parameters = array()) {
        return str_replace('%7E', '~', $this->_build_query($parameters));
    }

   /**
    *  Creates a request signature.
    *
    *  @see http://docs.amazonwebservices.com/AmazonSimpleDB/latest/DeveloperGuide/index.html?HMACAuth.html
    *  @param array $parameters     The signature parameters.
    *  @access protected
    *  @return string
    */
    protected function _create_signature($parameters) {
        // Amazon requires that query string components be alphabetized
        uksort($parameters, 'strcmp');
        $canonical_query_string = $this->_create_signable_string($parameters);

        $protocols = array('http://', 'https://');
        $domain = str_replace($protocols, '', $this->_config['region']);
        $parsed_url = parse_url('http://' . $domain);
        $host_header = strtolower($parsed_url['host']);
        $request_uri = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '/';

        $to_sign = "POST\n" . $host_header . "\n" . $request_uri
            . "\n" . $canonical_query_string;
        $hash = hash_hmac('sha256', $to_sign, $this->_config['secret_key'], true);
        return base64_encode($hash);
    }

   /**
    *  Deletes attributes from the database.
    *
    *  @param string $domain        The domain from which the data should
    *                               be deleted.
    *  @param array $attributes     An array containing the data to be deleted.
    *                               Format should be as follows:
    *                               array('column_name' => 'column_value');
    *                               If 'id' is the only attribute supplied,
    *                               all attributes will be deleted.
    *  @access public
    *  @return bool
    */
    public function delete($domain, $attributes) {
        $attributes = $this->_remap_attributes($attributes);
        $parameters = array(
            'Action'     => 'DeleteAttributes',
            'DomainName' => $domain,
        ) + $attributes;

        $response = $this->_craft_request($parameters);
        $response = simplexml_load_string($response);
        if ( isset($response->Errors) ) {
            // TODO: How to handle errors?
            return false;
        } elseif ( isset($response->ResponseMetadata) ) {
            return true;
        }
    }

   /**
    *  Escapes character sequences for use in a REST request.
    *
    *  @param string $val           The string to be escaped.
    *  @access protected
    *  @return string
    */
    protected function _escape($val) {
        $replace = array(
            '"' => '""',
            "'" => "''"
        );
        return str_replace(array_keys($replace), array_values($replace), $val);
    }

   /**
    *  Fetches the next row from the result set in memory (i.e., the one
    *  that was created after running find()).
    *
    *  @param string $fetch_style   Controls how the rows will be returned.
    *                               Available options are 'assoc' and 'into'.
    *  @param obj $obj              The object to be fetched into if
    *                               $fetch_style is set to 'into'.
    *  @access public
    *  @return mixed
    */
    public function fetch($fetch_style = 'assoc', $obj = null) {
        if ( empty($this->_select_results) ) {
            return false;
        }

        $result = array_shift($this->_select_results);
        if ( !property_exists($result, 'Name') ) {
            return false;
        }

        switch ( $fetch_style ) {
            case 'into':
                // TODO: Throw exception if no object is provided
                $id = $this->_config['primary_key'];
                $obj->$id = (string) $result->Name;
                foreach ( $result->Attribute as $attribute ) {
                    $name = (string) $attribute->Name;
                    $obj->$name = (string) $attribute->Value;
                }
                return $obj;
            case 'assoc':
            default:
                $final = array(
                    $this->_config['primary_key'] => (string) $result->Name
                );
                foreach ( $result->Attribute as $attribute ) {
                    $final += array(
                        (string) $attribute->Name => (string) $attribute->Value
                    );
                }
                return $final;
        }
    }

   /**
    *  Returns an array containing all of the result set rows.
    *
    *  @param string $fetch_style   Controls how the rows will be returned.
    *                               Available options are 'assoc'.
    *  @access public
    *  @return mixed
    */
    public function fetch_all($fetch_style = 'assoc') {
        if ( empty($this->_select_results) ) {
            return false;
        }

        $collection = array();
        switch ( $fetch_style ) {
            case 'assoc':
            default:
                foreach ( $this->_select_results as $result ) {
                    if ( !property_exists($result, 'Name') ) {
                        continue;
                    }

                    $final = array(
                        $this->_config['primary_key'] => (string) $result->Name
                    );
                    foreach ( $result->Attribute as $attribute ) {
                        $final += array(
                            (string) $attribute->Name => (string) $attribute->Value
                        );
                    }
                    $collection[] = $final;
                }
                if ( empty($collection) ) {
                    return false;
                }
                return $collection;
        }
    }

   /**
    *  Returns a single column from the next row of a result set or false
    *  if there are no more rows.
    *
    *  @param int $column_number    Zero-index number of the column to
    *                               retrieve from the row.
    *  @access public
    *  @return mixed
    */
    public function fetch_column($column_number = 0) {
        if ( empty($this->_select_results) ) {
            return false;
        }

        $result = array_shift($this->_select_results);
        $attributes = array_reverse($result->Attribute);
        $column = $attributes[$column_number];
        $final = array(
            (string) $column->Name => (string) $column->Value
        );

        return $final;
    }

   /**
    *  Performs a `SELECT FROM` query.
    *
    *  @param string|array $attributes   The attributes to be retrieved.
    *  @param string $domain             The domain to SELECT from.
    *  @param string|array $where        The WHERE clause of the SQL query.
    *  @param string $additional         Any additional SQL to be added at
    *                                    the end of the query.
    *  @access public
    *  @return bool
    */
    public function find($attributes, $domain, $where = null, $additional = null) {
        $sql = 'select ';
        if ( is_array($attributes) ) {
            $sql .= '`' . implode('`, `', $attributes) . '`';
        } else {
            $sql .= $attributes;
        }

        $sql .= ' from `' . $domain . '`';
        $sql .= $this->_format_where($where);

        $limit = null;
        if ( !is_null($additional) ) {
            $additional = strtolower($additional);
            $sql .= ' ' . $additional;
            if ( preg_match('/limit ([\d,]+)/', $additional, $matches) ) {
                $limit = (int) $matches[1];
            }
        }

        $parameters = array(
            'Action'           => 'Select',
            'ConsistentRead'   => 'true',
            'SelectExpression' => $sql
        );

        // Are there paged results from a previous find() call?
        if ( !is_null($this->_next_token) ) {
            $parameters += array('NextToken' => $this->_next_token);
        } else {
            // No paged results, meaning we're not recursing
            $this->_select_results = array();
        }

        $response = $this->_craft_request($parameters);
        $response = simplexml_load_string($response);
        if ( isset($response->Errors) ) {
            $this->_select_results = array();
            // TODO: How to handle errors?
            return false;
        } elseif ( !isset($response->SelectResult) ) {
            return false;
        }

        $results = array();
        foreach ( $response->SelectResult->Item as $item ) {
            $results[] = $item;
        }

        $this->_select_results = array_merge($this->_select_results, $results);

        if ( !is_null($limit) && count($this->_select_results) > $limit ) {
            $this->_select_results = array_slice($this->_select_results, 0, $limit);
        }

        if ( (is_null($limit) || count($this->_select_results) < $limit)
            && isset($response->SelectResult->NextToken) ) {
            $this->_next_token = $response->SelectResult->NextToken;
            // Recurse
            $func = array($this, __FUNCTION__);
            call_user_func($func, $attributes, $domain, $where, $additional);
            $this->_next_token = null;
        }
        return true;
    }

   /**
    *  Parses a WHERE clause, which can be of any of the following formats:
    *
    *  $where = 'id = 3';
    *  (produces ` WHERE id = 3`)
    *
    *  $where = array(
    *      'id'       => 3,
    *      'username' => 'test'
    *  );
    *  (produces ` WHERE id = 3 and username = 'test'`)
    *
    *  $where = array(
    *      'id' => array(
    *          'gte' => 20,
    *          'lt'  => 30
    *      ),
    *      'username' => 'test',
    *      'template' => array(
    *          'in' => array('default', 'mobile')
    *      )
    *  );
    *  (produces ` WHERE id >= 20 and id < 30 and username = 'test'
    *              and template in('default','mobile') `)
    *
    *  @param string|array $where        The clause to be parsed.
    *  @access protected
    *  @return string
    */
    protected function _format_where($where = null) {
        $sql = '';

        if ( is_null($where) ) {
            return $sql;
        }

        $sql = ' where ';
        if ( is_string($where) ) {
            $sql .= $where;
        } elseif ( is_array($where) ) {
            foreach ( $where as $name => $val ) {
                if ( $name !== $this->_config['primary_key'] ) {
                    $name = '`' . $name . "`";
                } else {
                    $name = 'itemName()';
                }

                if ( is_string($val) || is_numeric($val) ) {
                    $val = $this->_escape($val);
                    $sql .= $name . " = '" . $val . "' and ";
                } elseif ( is_array($val) ) {
                    foreach ( $val as $sign => $constraint ) {
                        $constraint = $this->_escape($constraint);
                        $sql .=  $name;
                        switch ( $sign ) {
                            case 'gt':
                                $sql .= " > '" . $constraint . "'";
                                break;
                            case 'gte':
                                $sql .= " >= '" . $constraint . "'";
                                break;
                            case 'lt':
                                $sql .= " < '" . $constraint . "'";
                                break;
                            case 'lte':
                                $sql .= " <= '" . $constraint . "'";
                                break;
                            case 'ne':
                                $sql .= " != '" . $constraint . "'";
                                break;
                            case 'in':
                                $list = '';
                                foreach ( $constraint as $item ) {
                                    $list .= "'" . $this->_escape($item) . "',";
                                }
                                $list = rtrim($list, ',');
                                $sql .= ' in (' . $list . ')';
                                break;
                            case 'e':
                            default:
                                $sql .= " = '" . $constraint . "'";
                                break;
                        }
                        $sql .= ' and ';
                    }
                }
            }
            $sql = substr($sql, 0, strlen($sql) - strlen(' and '));
        }

        return $sql;
    }

   /**
    *  Inserts a record into the database.
    *
    *  @param string $domain        The domain into which the data should
    *                               be inserted.
    *  @param array $attributes     An array containing the data to be inserted.
    *                               Format should be as follows:
    *                               array('column_name' => 'column_value');
    *  @access public
    *  @return bool
    */
    public function insert($domain, $attributes) {
        $insert_id = str_replace('.', '', uniqid('', true));
        $attributes += array($this->_config['primary_key'] => $insert_id);

        $attributes = $this->_remap_attributes($attributes, false);
        $parameters = array(
            'Action'     => 'PutAttributes',
            'DomainName' => $domain
        ) + $attributes;

        $response = $this->_craft_request($parameters);
        $response = simplexml_load_string($response);
        if ( isset($response->Errors) ) {
            // TODO: How to handle errors?
            return false;
        } elseif ( isset($response->ResponseMetadata) ) {
            $this->_last_insert_id = $insert_id;
            return true;
        }
    }

   /**
    *  Returns the ID of the last inserted row or sequence value.
    *
    *  @access public
    *  @return int
    */
    public function insert_id() {
        return $this->_last_insert_id;
    }

   /**
    *  Formats attributes for use in a REST request.
    *
    *  @param array $attributes     The attributes.
    *  @param bool $exists          Whether or not the attributes supplied
    *                               should already exist.
    *  @access protected
    *  @return array
    */
    protected function _remap_attributes($attributes, $exists = null) {
        // TODO: Throw exception if no id is specified
        $item_name = $attributes[$this->_config['primary_key']];
        unset($attributes[$this->_config['primary_key']]);

        $map = array('ItemName' => $item_name);
        $index = 1;
        foreach ( $attributes as $key => $value ) {
            $map += array(
                'Attribute.' . $index . '.Name'  => $key,
                'Attribute.' . $index . '.Value' => $value,
            );

            if ( is_null($exists) ) {
                continue;
            }

            if ( $exists ) {
                $map += array(
                    'Attribute.' . $index . '.Replace' => 'true'
                );
            } else {
                $map += array(
                    'Attribute.' . $index . '.Replace' => 'false'
                );
            }
            $index++;
        }
        return $map;
    }

   /**
    *  Updates attributes in the database.
    *
    *  @param string $domain        The domain into which the data should
    *                               be inserted.
    *  @param array $attributes     An array containing the data to be updated.
    *                               Format should be as follows:
    *                               array('column_name' => 'column_value');
    *  @access public
    *  @return bool
    */
    public function update($domain, $attributes) {
        $attributes = $this->_remap_attributes($attributes, true);
        $parameters = array(
            'Action'     => 'PutAttributes',
            'DomainName' => $domain
        ) + $attributes;

        $response = $this->_craft_request($parameters);
        $response = simplexml_load_string($response);
        if ( isset($response->Errors) ) {
            // TODO: How to handle errors?
            return false;
        } elseif ( isset($response->ResponseMetadata) ) {
            return true;
        }
    }

   /**
    *  Updates attributes in the database if they exist, inserts them
    *  otherwise.
    *
    *  @param string $domain        The domain into which the data should
    *                               be inserted.
    *  @param array $attributes     An array containing the data to be inserted.
    *                               Format should be as follows:
    *                               array('column_name' => 'column_value');
    *  @access public
    *  @return bool
    */
    public function upsert($domain, $attributes) {
        if ( isset($attributes[$this->_config['primary_key']])
            && !is_null($attributes[$this->_config['primary_key']]) ) {
            $result = $this->update($domain, $attributes);
        } else {
            $result = $this->insert($domain, $attributes);
        }
        return $result;
    }
}

?>
