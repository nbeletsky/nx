<?
require_once 'config/config.default.php';

class MemcachedTest extends \core\TestCase
{    

    public function setUp()
    {
        $cache = new \plugins\cache\MemcachedCache();
        $cache->flush_cache();
    }

    public function test_set()
    {
        $cache = new \plugins\cache\MemcachedCache();
        $key = 'test';
        $value = 'value';
        $cache->set_in_cache($key, $value);
        $check = $cache->get_from_cache($key);
        $this->assertEquals($value, $check, 'set_in_cache() failed!');
    }

    public function test_add()
    {
        $cache = new \plugins\cache\MemcachedCache();
        $key = 'test';
        $value = 'value';
        // First check that adding a non-existent key to cache works
        $cache->add_to_cache($key, $value);
        $check = $cache->get_from_cache($key);
        $this->assertEquals($value, $check, 'Adding a non-existent key with add_to_cache() failed!');

        // Now check that adding the same key returns false
        $check = $cache->add_to_cache($key, $value);
        $this->assertFalse($check, 'Adding an existent key with add_to_cache() did not return false!');
    }

    public function test_delete()
    {
        $cache = new \plugins\cache\MemcachedCache();
        $key = 'test';
        $value = 'value';
        $cache->add_to_cache($key, $value);
        $cache->delete_from_cache($key);
        $check = $cache->get_from_cache($key);
        $this->assertFalse($check, 'delete_from_cache() failed!');
    }

    public function test_flush()
    {
        $cache = new \plugins\cache\MemcachedCache();
        $key = 'test';
        $value = 'value';
        $cache->set_in_cache($key, $value);
        $cache->flush_cache();
        $check = $cache->get_from_cache($key);
        $this->assertFalse($check, 'flush_cache() failed!');
    }

    public function test_replace()
    {
        $cache = new \plugins\cache\MemcachedCache();
        $key = 'test';
        $value = 'value';
        $replaced_value = 'replaced';
        $cache->set_in_cache($key, $value);
        $cache->replace_in_cache($key, $replaced_value);
        $check = $cache->get_from_cache($key);
        $this->assertEquals($replaced_value, $check, 'replace_in_cache() failed!');
    }

}
?>
