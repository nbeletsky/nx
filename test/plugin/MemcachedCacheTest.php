<?php

namespace test\plugin;

use plugin\cache\MemcachedCache;

class MemcachedCacheTest extends \PHPUnit_Framework_TestCase {    

    public function setUp() {
        $cache = new MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        $cache->flush_cache();
    }

    public function test_SetInAndGetFromCache_ReturnsOriginalValue() {
        $cache = new MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        $original_key = 'test';
        $original_value = 'value';
        $cache->set_in_cache($original_key, $original_value);
        $retrieved_value = $cache->get_from_cache($original_key);
        $this->assertEquals($original_value, $retrieved_value, 'Setting a value in the cache and then getting it did not return the original value.');
    }

    public function test_AddNonExistentKeyAndGetFromCache_ReturnsOriginalValue() {
        $cache = new MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        $original_key = 'test';
        $original_value = 'value';
        $cache->add_to_cache($original_key, $original_value);
        $retrieved_value = $cache->get_from_cache($original_key);
        $this->assertEquals($original_value, $retrieved_value, 'Adding a value to the cache using a non-existent key and then getting it did not return the original value.');
    }

    public function test_AddExistingKeyAndGetFromCache_ReturnsFalse() {
        $cache = new MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        $original_key = 'test';
        $original_value = 'value';
        // First add a non-existent key to the cache
        $cache->add_to_cache($original_key, $original_value);
        $retrieved_value = $cache->get_from_cache($original_key);
        $this->assertEquals($original_value, $retrieved_value, 'Adding a value to the cache using a non-existent key and then getting it did not return the original value.');

        // Now check that adding the same key returns false
        $retrieved_value = $cache->add_to_cache($original_key, $original_value);
        $this->assertFalse($retrieved_value, 'Adding a value to the cache using an existing key did not return false.');
    }

    public function test_DeleteKeyAndGetFromCache_ReturnsFalse()
    {
        $cache = new MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        $original_key = 'test';
        $original_value = 'value';
        $cache->add_to_cache($original_key, $original_value);
        $cache->delete_from_cache($original_key);
        $retrieved_value = $cache->get_from_cache($original_key);
        $this->assertFalse($retrieved_value, 'Adding a value to the cache, deleting it, and then getting it did not return false.');
    }

    public function test_FlushCacheAndGetFromCache_ReturnsFalse()
    {
        $cache = new MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        $original_key = 'test';
        $original_value = 'value';
        $cache->set_in_cache($original_key, $original_value);
        $cache->flush_cache();
        $retrieved_value = $cache->get_from_cache($original_key);
        $this->assertFalse($retrieved_value, 'Adding a value to the cache, flushing the whole cache, and then getting it did not return false.');
    }

    public function test_ReplaceInCache_ReturnsOriginalValue()
    {
        $cache = new MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        $original_key = 'test';
        $original_value = 'value';
        $replaced_value = 'replaced';
        $cache->set_in_cache($original_key, $original_value);
        $cache->replace_in_cache($original_key, $replaced_value);
        $retrieved_value = $cache->get_from_cache($original_key);
        $this->assertEquals($replaced_value, $retrieved_value, 'Adding a value to the cache, replacing it with another value, and then getting it did not return the original value.');
    }

}
?>
