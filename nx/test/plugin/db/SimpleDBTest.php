<?php

namespace nx\test\plugin\db;

use nx\plugin\db\SimpleDB;

class ModelMock extends \nx\core\Model {
    protected $id;
    protected $price;
    protected $location;

    public function set_id($val) {
        $this->id = $val;
    }

    public function set_price($val) {
        $this->price = $val;
    }

    public function set_location($val) {
        $this->location = $val;
    }
}

// This is here because we don't
// want to expose the create_ and delete_domain
// methods within the plugin itself
class SimpleDBAdmin extends SimpleDB {

    public function create_domain($domain) {
        $parameters = array(
            'Action'     => 'CreateDomain',
            'DomainName' => $domain,
        );

        $response = $this->_craft_request($parameters);
        $response = simplexml_load_string($response);
        if ( isset($response->Errors) ) {
            var_dump($response->Errors);
            return false;
        } elseif ( isset($response->ResponseMetadata) ) {
            return true;
        }
    }

    public function delete_domain($domain) {
        $parameters = array(
            'Action'     => 'DeleteDomain',
            'DomainName' => $domain,
        );

        $response = $this->_craft_request($parameters);
        $response = simplexml_load_string($response);
        if ( isset($response->Errors) ) {
            var_dump($response->Errors);
            return false;
        } elseif ( isset($response->ResponseMetadata) ) {
            return true;
        }
    }
}

class SimpleDBTest extends \PHPUnit_Framework_TestCase {

    protected $_db;
    protected $_dbadmin;
    protected $_domain;

    public function setUp() {
        $options = array(
            'access_key' => '',
            'secret_key' => ''
        );
        $this->_domain = 'NXTest';
        $this->_db = new SimpleDB($options);

        $this->_dbadmin = new SimpleDBAdmin($options);

        if ( !$this->_dbadmin->create_domain($this->_domain) ) {
            $this->fail('Could not create the domain needed to run the test.');
        }
    }

    public function tearDown() {
        if ( !$this->_dbadmin->delete_domain($this->_domain) ) {
            $this->fail('Could not delete the domain created during the test run.');
        }
    }

    public function test_DeleteExistingRecord_ReturnsTrue() {
        $attributes = array(
            'price'    => '1700',
            'location' => 'Harold Bloom'
        );

        $this->_db->insert($this->_domain, $attributes);
        $insert_id = $this->_db->insert_id();

        $attributes = array('id' => $insert_id);
        $result = $this->_db->delete($this->_domain, $attributes);
        $this->assertTrue($result);

        $attributes = '*';
        $where = array('id' => $insert_id);
        $this->_db->find($attributes, $this->_domain, $where);
        $result = $this->_db->fetch();
        $this->assertFalse($result);
    }

    public function test_DeleteNonExistingRecord_ReturnsTrue() {
        $attributes = array(
            'price'    => '34',
            'location' => 'Herald Square'
        );

        $this->_db->insert($this->_domain, $attributes);
        $insert_id = $this->_db->insert_id();

        $attributes = array('id' => $insert_id);
        $this->_db->delete($this->_domain, $attributes);

        // Record doesn't exist, try to delete again
        $result = $this->_db->delete($this->_domain, $attributes);
        $this->assertTrue($result);

        $attributes = '*';
        $where = array('id' => $insert_id);
        $this->_db->find($attributes, $this->_domain, $where);
        $result = $this->_db->fetch_all();
        $this->assertFalse($result);
    }

    public function test_FindExistingRecord_ReturnsTrue() {
        $attributes = array(
            'price'    => '34',
            'location' => 'Herald Square'
        );

        $result = $this->_db->insert($this->_domain, $attributes);
        $insert_id = $this->_db->insert_id();

        $attributes = array('price', 'location');
        $where = array('id' => $insert_id);
        $result = $this->_db->find($attributes, $this->_domain, $where);
        $this->assertTrue($result);

        $result = $this->_db->fetch();
        $check = array(
            'id'       => $insert_id,
            'price'    => '34',
            'location' => 'Herald Square'
        );
        $this->assertEquals($result, $check);


        // Second insert
        $attributes = array(
            'price'    => '34',
            'location' => 'Over the rainbow'
        );

        $result = $this->_db->insert($this->_domain, $attributes);
        $insert_id_2 = $this->_db->insert_id();

        $attributes = array('price', 'location');
        $where = array('price' => '34');
        $result = $this->_db->find($attributes, $this->_domain, $where);
        $this->assertTrue($result);

        $result = $this->_db->fetch_all();
        $check = array(
            array(
                'id'       => $insert_id,
                'price'    => '34',
                'location' => 'Herald Square'
            ),
            array(
                'id'       => $insert_id_2,
                'price'    => '34',
                'location' => 'Over the rainbow'
            )
        );
        $this->assertEquals($result, $check);

        $attributes = array('price', 'location');
        $where = array('price' => '34');
        $result = $this->_db->find($attributes, $this->_domain, $where, 'LIMIT 1');
        $this->assertTrue($result);

        $result = $this->_db->fetch_all();
        $check = array(
            array(
                'id'       => $insert_id,
                'price'    => '34',
                'location' => 'Herald Square'
            )
        );
        $this->assertEquals($result, $check);

        $attributes = array('price', 'location');
        $where = array('id' => $insert_id);
        $result = $this->_db->find($attributes, $this->_domain, $where, 'LIMIT 1');
        $this->assertTrue($result);

        $result = $this->_db->fetch('into', new ModelMock());
        $check = new ModelMock();
        $check->set_id($insert_id);
        $check->set_price('34');
        $check->set_location('Herald Square');
        $this->assertEquals($result, $check);

        $attributes = array('price', 'location');
        $where = array(
            'id' => array(
                'in' => array($insert_id, $insert_id_2)
            )
        );
        $result = $this->_db->find($attributes, $this->_domain, $where);
        $this->assertTrue($result);

        $result = $this->_db->fetch_all();
        $check = array(
            array(
                'id'       => $insert_id,
                'price'    => '34',
                'location' => 'Herald Square'
            ),
            array(
                'id'       => $insert_id_2,
                'price'    => '34',
                'location' => 'Over the rainbow'
            )
        );
        $this->assertEquals($result, $check);

        // Cleanup
        $attributes = array('id' => $insert_id);
        $this->_db->delete($this->_domain, $attributes);

        $attributes = array('id' => $insert_id_2);
        $this->_db->delete($this->_domain, $attributes);
    }

    public function test_Update_ReturnsTrue() {
        $attributes = array(
            'price'    => '34',
            'location' => 'Herald Square'
        );

        $this->_db->insert($this->_domain, $attributes);
        $insert_id = $this->_db->insert_id();

        $attributes = array(
            'price'    => '38',
            'location' => 'Herald Fair',
            'id'       => $insert_id
        );
        $result = $this->_db->update($this->_domain, $attributes);
        $this->assertTrue($result);


        $attributes = array('price', 'location');
        $where = array('id' => $insert_id);
        $result = $this->_db->find($attributes, $this->_domain, $where);
        $this->assertTrue($result);

        $result = $this->_db->fetch();
        $check = array(
            'id'       => $insert_id,
            'price'    => '38',
            'location' => 'Herald Fair'
        );
        $this->assertEquals($result, $check);


        // Cleanup
        $insert_id = $this->_db->insert_id();
        $attributes = array('id' => $insert_id);
        $this->_db->delete($this->_domain, $attributes);
    }

    public function test_Upsert_ReturnsTrue() {
        $attributes = array(
            'price'    => '34',
            'location' => 'Herald Square'
        );

        $result = $this->_db->upsert($this->_domain, $attributes);
        $insert_id = $this->_db->insert_id();

        $this->assertTrue($result);

        $attributes = array(
            'price'    => '43',
            'location' => 'Herald Fair',
            'id'       => $insert_id
        );

        $result = $this->_db->upsert($this->_domain, $attributes);
        $this->assertTrue($result);

        $attributes = array('price', 'location');
        $where = array('id' => $insert_id);
        $result = $this->_db->find($attributes, $this->_domain, $where);
        $this->assertTrue($result);

        $result = $this->_db->fetch();
        $check = array(
            'id' => $insert_id,
            'price'    => '43',
            'location' => 'Herald Fair'
        );
        $this->assertEquals($result, $check);

        // Cleanup
        $attributes = array('id' => $insert_id);
        $this->_db->delete($this->_domain, $attributes);
    }

}
?>
