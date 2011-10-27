<?php

namespace nx\test\plugin\cache;

use nx\plugin\cache\Memcached;

class MemcachedTest extends \PHPUnit_Framework_TestCase {

    protected $_cache;

    public function setUp() {
        $this->_cache = new Memcached();
        $this->_cache->add_server('localhost');
        $this->_cache->flush();
    }

    public function test_SetInAndGetFromCache_ReturnsOriginalValue() {
        $original_key = 'test';
        $original_value = 'value';
        $this->_cache->store($original_key, $original_value);
        $retrieved_value = $this->_cache->retrieve($original_key);
        $this->assertEquals($original_value, $retrieved_value, 'Storing a value
            in the cache and then retrieving it did not return the original value.');
    }

    public function test_AddNonExistentKeyAndGetFromCache_ReturnsOriginalValue() {
        $original_key = 'test';
        $original_value = 'value';
        $this->_cache->add($original_key, $original_value);
        $retrieved_value = $this->_cache->retrieve($original_key);
        $this->assertEquals($original_value, $retrieved_value, 'Adding a value
            to the cache using a non-existent key and then retrieving it did not
            return the original value.');
    }

    public function test_AddExistingKeyAndGetFromCache_ReturnsFalse() {
        $original_key = 'test';
        $original_value = 'value';
        // First add a non-existent key to the cache
        $this->_cache->add($original_key, $original_value);
        $retrieved_value = $this->_cache->retrieve($original_key);
        $this->assertEquals($original_value, $retrieved_value, 'Adding a value
            to the cache using a non-existent key and then retrieving it did not
            return the original value.');

        // Now check that adding the same key returns false
        $retrieved_value = $this->_cache->add($original_key, $original_value);
        $this->assertFalse($retrieved_value, 'Adding a value to the cache using
            an existing key did not return false.');
    }

    public function test_DeleteKeyAndGetFromCache_ReturnsFalse() {
        $original_key = 'test';
        $original_value = 'value';
        $this->_cache->add($original_key, $original_value);
        $this->_cache->delete($original_key);
        $retrieved_value = $this->_cache->retrieve($original_key);
        $this->assertFalse($retrieved_value, 'Adding a value to the cache,
            deleting it, and then retrieving it did not return false.');
    }

    public function test_FlushCacheAndGetFromCache_ReturnsFalse() {
        $original_key = 'test';
        $original_value = 'value';
        $this->_cache->store($original_key, $original_value);
        $this->_cache->flush();
        $retrieved_value = $this->_cache->retrieve($original_key);
        $this->assertFalse($retrieved_value, 'Adding a value to the cache,
            flushing the whole cache, and then retrieving it did not return false.');
    }

    public function test_ReplaceInCache_ReturnsOriginalValue() {
        $original_key = 'test';
        $original_value = 'value';
        $replaced_value = 'replaced';
        $this->_cache->store($original_key, $original_value);
        $this->_cache->replace($original_key, $replaced_value);
        $retrieved_value = $this->_cache->retrieve($original_key);
        $this->assertEquals($replaced_value, $retrieved_value, 'Adding a value
            to the cache, replacing it with another value, and then retrieving
            it did not return the original value.');
    }

}
?>
