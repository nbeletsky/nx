<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\plugin\cache;

/*
 *  The `Memcached` class is used to facilitate storage
 *  and retrieval of data from a Memcached server.
 *
 *  @package plugin
 */
class Memcached extends \nx\core\Object {

   /**
    *  The Memcached object.
    *
    *  @var object
    *  @access protected
    */
    protected $_cache;

   /**
    *  Loads the configuration settings for Memcached.
    *
    *  @param array $config                     The configuration settings, which
    *                                           can take two options:
    *                                           `host`          - The hostname of
    *                                                             the memcached server.
    *                                           `persistent_id` - A unique ID used
    *                                                             to allow persistence
    *                                                             between requests.
    *                                           (By default, instances are
    *                                           destroyed at the end of the request.)
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'host'          => 'localhost',
            'persistent_id' => ''
        );
        parent::__construct($config + $defaults);
    }

   /**
    *  Creates a new instance of Memcached and adds a server if one does
    *  not exist using the provided host.
    *
    *  @access protected
    *  @return void
    */
    protected function _init() {
        if ( $this->_config['persistent_id'] ) {
            $this->_cache = new \Memcached($this->_config['persistent_id']);
        } else {
            $this->_cache = new \Memcached();
        }

        $server_list = $this->server_list();
        $found_server = false;
        foreach ( $server_list as $server ) {
            if ( $server['host'] == $this->_config['host'] ) {
                $found_server = true;
                break;
            }
        }

        if ( !$found_server ) {
            $this->add_server($this->_config['host']);
        }
    }

   /**
    *  Adds an item under a new key.  Functionally equivalent to store(),
    *  though this operation will fail if $key already exists on the server.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored.
    *  @param string $server_key                The key identifying the
    *                                           server to store the value on.
    *  @param int $expiration                   The expiration time.
    *                                           Can be number of seconds from now.
    *                                           If this value exceeds 60*60*24*30
    *                                           (number of seconds in 30 days),
    *                                           the value will be interpreted as
    *                                           a UNIX timestamp.
    *  @access public
    *  @return bool
    */
    public function add($key, $value, $server_key = '', $expiration = 0) {
        return $this->_cache->addByKey($server_key, $key, $value, $expiration);
    }

   /**
    *  Adds a server to the server pool.
    *
    *  @param string $host                      The hostname of the memcached server.
    *  @param int $weight                       The weight of the server relative
    *                                           to the total weight of all the
    *                                           servers in the pool.
    *                                           This controls the probability of
    *                                           the server being selected for
    *                                           operations, and usually corresponds
    *                                           to the amount of memory available
    *                                           to memcached on that server.
    *  @param int $port                         The port on which memcache is running.
    *                                           (Typically 11211.)
    *  @access public
    *  @return bool
    */
    public function add_server($host, $weight = 0, $port = 11211) {
        return $this->_cache->addServer($host, $port, $weight);
    }

   /**
    *  Adds multiple servers to the server pool.
    *
    *  @param array $servers                    Array of the servers to add to the pool.
    *                                           Expected format:
    *                                           array(
    *                                               array(
    *                                                   'host'   => $host,
    *                                                   'weight' => $weight [ = 0],
    *                                                   'port'   => $port [ = 11211]
    *                                               )
    *                                           )
    *  @access public
    *  @return bool
    */
    public function add_servers($servers) {
        $reassembled = array();
        foreach ( $servers as $server ) {
            $reassembled[] = array(
                $server['host'],
                (isset($server['port'])) ? $server['port'] : 11211,
                (isset($server['weight'])) ? $server['weight'] : 0
            );
        }

        return $this->_cache->addServers($reassembled);
    }

   /**
    *  Appends data to an existing item.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param string $value                     The string to append.
    *  @param mixed $server_key                 The key identifying the server
    *                                           to store the value on.
    *  @access public
    *  @return bool
    */
    public function append($key, $value, $server_key = '') {
        return $this->_cache->appendByKey($server_key, $key, $value);
    }

   /**
    *  Compares and swaps an item.  This means that the item will be stored only
    *  if no other client has updated it since it was last fetched by this client.
    *
    *  @param float $token                      Unique value associated with the
    *                                           existing item. Generated by memcached.
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored.
    *  @param string $server_key                The key identifying the server
    *                                           to store the value on.
    *  @param int $expiration                   The expiration time.  Can be
    *                                           number of seconds from now.
    *                                           If this value exceeds 60*60*24*30
    *                                           (number of seconds in 30 days),
    *                                           the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @access public
    *  @return bool
    *
    *  @see /nx/plugin/cache/Memcached->retrieve() for how to obtain the CAS token.
    */
    public function cas($token, $key, $value, $server_key = '', $expiration = 0) {
        return $this->_cache->casByKey($token, $server_key, $key, $value, $expiration);
    }

   /**
    *  Decrements a numeric item's value.
    *
    *  @param string $key                       The key of the item to decrement.
    *  @param int $offset                       The amount by which to decrement
    *                                           the item's value.
    *  @access public
    *  @return int                              If the item's value is not
    *                                           numeric, it is treated as if the
    *                                           value were 0.  If the operation
    *                                           would decrease the value below
    *                                           0, the new value will be 0.
    */
    public function decrement($key, $offset = 1) {
        return $this->_cache->decrement($key, $offset);
    }

   /**
    *  Deletes an item.
    *
    *  @param string $key                       The key to be deleted.
    *  @param string $server_key                The key identifying the server
    *                                           to delete the value from.
    *  @param int $time                         The amount of time the server
    *                                           will wait to delete the item.
    *  @access public
    *  @return bool
    */
    public function delete($key, $server_key = '', $time = 0) {
        return $this->_cache->deleteByKey($server_key, $key, $time);
    }

   /**
    *  Invalidates all items in the cache.
    *
    *  @param int $delay                        Number of seconds to wait before
    *                                           invalidating the items.
    *  @access public
    *  @return bool
    */
    public function flush($delay = 0) {
        return $this->_cache->flush($delay);
    }

   /**
    *  Increments a numeric item's value.
    *
    *  @param string $key                       The key of the item to increment.
    *  @param int $offset                       The amount by which to increment
    *                                           the item's value.
    *  @access public
    *  @return int                              If the item's value is not
    *                                           numeric, it is treated as if the
    *                                           value were 0.
    */
    public function increment($key, $offset = 1) {
        return $this->_cache->increment($key, $offset);
    }

   /**
    *  Prepends data to an existing item.
    *
    *  @param string $key                       The key of the item to prepend the data to.
    *  @param string $value                     The string to prepend.
    *  @param mixed $server_key                 The key identifying the server
    *                                           to store the value on.
    *  @access public
    *  @return bool
    */
    public function prepend($key, $value, $server_key = '') {
        return $this->_cache->prependByKey($server_key, $key, $value);
    }

   /**
    *  Replaces the item under an existing key.  Functionally
    *  equivalent to store(), though this operation will fail
    *  if $key does not exist.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored.
    *  @param string $server_key                The key identifying the server
    *                                           to store the value on.
    *  @param int $expiration                   The expiration time.
    *                                           Can be number of seconds from now.
    *                                           If this value exceeds 60*60*24*30
    *                                           (number of seconds in 30 days),
    *                                           the value will be interpreted as
    *                                           a UNIX timestamp.
    *  @access public
    *  @return bool
    */
    public function replace($key, $value, $server_key = '', $expiration = 0) {
        return $this->_cache->replaceByKey($server_key, $key, $value, $expiration);
    }

   /**
    *  Retrieves an item.  Returns false if the key is not found.
    *
    *  @param string $key                       The key of the item to retrieve.
    *  @param string $server_key                The key identifying the server
    *                                           to retrieve the value from.
    *  @param callback $cache_cb                Read-through caching callback.
    *  @param float &$cas_token                 The variable to store the CAS token in.
    *  @access public
    *  @return mixed
    *
    *  @see /nx/plugin/cache/Memcached->cas() for how to use CAS tokens.
    */
    public function retrieve($key, $server_key = '', $cache_cb = null, &$cas_token = null) {
        return $this->_cache->getByKey($server_key, $key, $cache_cb, $cas_token);
    }

   /**
    *  Gets the list of the servers in the pool.
    *
    *  @access public
    *  @return array
    */
    public function server_list() {
        return $this->_cache->getServerList();
    }

   /**
    *  Gets server pool statistics.
    *
    *  @access public
    *  @return array
    */
    public function stats() {
        return $this->_cache->getStats();
    }

   /**
    *  Stores an item.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored.
    *  @param string $server_key                The key identifying the server
    *                                           to store the value on.
    *  @param int $expiration                   The expiration time.
    *                                           Can be number of seconds from now.
    *                                           If this value exceeds 60*60*24*30
    *                                           (number of seconds in 30 days),
    *                                           the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @access public
    *  @return bool
    */
    public function store($key, $value, $server_key = '', $expiration = 0) {
        $result = $this->_cache->setByKey($server_key, $key, $value, $expiration);
    }

}
?>
