<?php

namespace nx\test\lib;

use nx\lib\Form;

class ModelMock extends \nx\core\Model {
    protected $id;
    protected $test_name = 'test value';

    public function set_id($val) {
        $this->id = $val;
    }
}

class ModelMock2 extends \nx\core\Model {
    protected $id;
    protected $test_name = 'test value';

    public function set_id($val) {
        $this->id = $val;
    }
}

class FormTest extends \PHPUnit_Framework_TestCase {

    protected $_form;

    public function setUp() {
        $this->_form = new Form();
    }

    public function test_FormHelpersNoBindings_ReturnsHtml() {
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name',
            'value' => 'test value'
        );

        $input = $this->_form->checkbox($attributes);
        $check = "<input type='checkbox' id='test_id' class='test_class' name='test_name' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->email($attributes);
        $check = "<input type='email' id='test_id' class='test_class' name='test_name' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->hidden($attributes);
        $check = "<input type='hidden' id='test_id' class='test_class' name='test_name' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->number($attributes);
        $check = "<input type='number' id='test_id' class='test_class' name='test_name' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->search($attributes);
        $check = "<input type='search' id='test_id' class='test_class' name='test_name' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->text($attributes);
        $check = "<input type='text' id='test_id' class='test_class' name='test_name' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->textarea($attributes);
        $check = "<textarea id='test_id' class='test_class' name='test_name'>test value</textarea>";
        $this->assertEquals($check, $input);

        $attributes = array(
            'name'  => 'test_name',
            'value' => array(
                'green'  => 'green',
                'orange' => 'orange',
                'red'    => 'red',
                'blue'   => 'blue'
            )
        );

        $input = $this->_form->radios($attributes);
        $check = "<input type='radio' name='test_name' value='green' /> <label>green</label>"
            . "<input type='radio' name='test_name' value='orange' /> <label>orange</label>"
            . "<input type='radio' name='test_name' value='red' /> <label>red</label>"
            . "<input type='radio' name='test_name' value='blue' /> <label>blue</label>";
        $this->assertEquals($check, $input);

        $attributes['value'] = array(
            '17' => 'green',
            '18' => 'orange',
            '19' => 'red',
            '20' => 'blue'
        );

        $input = $this->_form->radios($attributes);
        $check = "<input type='radio' name='test_name' value='17' /> <label>green</label>"
            . "<input type='radio' name='test_name' value='18' /> <label>orange</label>"
            . "<input type='radio' name='test_name' value='19' /> <label>red</label>"
            . "<input type='radio' name='test_name' value='20' /> <label>blue</label>";
        $this->assertEquals($check, $input);

        $attributes['id'] = array('one', 'two', 'three', 'four');
        $input = $this->_form->radios($attributes);
        $check = "<input type='radio' name='test_name' id='one' value='17' /> <label for='one'>green</label>"
            . "<input type='radio' name='test_name' id='two' value='18' /> <label for='two'>orange</label>"
            . "<input type='radio' name='test_name' id='three' value='19' /> <label for='three'>red</label>"
            . "<input type='radio' name='test_name' id='four' value='20' /> <label for='four'>blue</label>";
        $this->assertEquals($check, $input);

        $attributes = array(
            'class' => 'test_class',
            'name'  => 'test_name'
        );

        $values = array(
            'green'  => 'green',
            'orange' => 'orange',
            'red'    => 'red',
            'blue'   => 'blue'
        );

        $input = $this->_form->select($attributes, $values);
        $check = "<select class='test_class' name='test_name'>"
            . "<option value='green'>green</option><option value='orange'>orange</option>"
            . "<option value='red'>red</option><option value='blue'>blue</option>"
            . "</select>";
        $this->assertEquals($check, $input);

        $values = array(
            '17' => 'green',
            '18' => 'orange',
            '19' => 'red',
            '20' => 'blue'
        );

        $input = $this->_form->select($attributes, $values);
        $check = "<select class='test_class' name='test_name'>"
            . "<option value='17'>green</option><option value='18'>orange</option>"
            . "<option value='19'>red</option><option value='20'>blue</option>"
            . "</select>";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'autofocus',
            'name'  => 'test_name',
            'value' => 'test value'
        );

        $input = $this->_form->email($attributes);
        $check = "<input type='email' id='test_id' class='test_class' autofocus name='test_name' value='test value' />";
        $this->assertEquals($check, $input);

    }

    public function test_FormHelpersWithBindingsWithoutId_ReturnsHtml() {
        $binding = new ModelMock();

        // Test with a custom value
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name',
            'value' => 'test'
        );
        $input = $this->_form->checkbox($attributes, $binding);
        $check = "<input type='checkbox' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_2',
            'value' => 'test'
        );
        $input = $this->_form->email($attributes, $binding);
        $check = "<input type='email' id='test_id' class='test_class' name='ModelMock[0][test_name_2]' value='test' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_3',
            'value' => 'test'
        );
        $input = $this->_form->hidden($attributes, $binding);
        $check = "<input type='hidden' id='test_id' class='test_class' name='ModelMock[0][test_name_3]' value='test' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_4',
            'value' => 'test'
        );
        $input = $this->_form->number($attributes, $binding);
        $check = "<input type='number' id='test_id' class='test_class' name='ModelMock[0][test_name_4]' value='test' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_5',
            'value' => 'test'
        );
        $input = $this->_form->search($attributes, $binding);
        $check = "<input type='search' id='test_id' class='test_class' name='ModelMock[0][test_name_5]' value='test' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_6',
            'value' => 'test'
        );
        $input = $this->_form->text($attributes, $binding);
        $check = "<input type='text' id='test_id' class='test_class' name='ModelMock[0][test_name_6]' value='test' />";
        $this->assertEquals($check, $input);


        // Test with value the same as $binding->name
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name',
            'value' => 'test value'
        );
        $input = $this->_form->checkbox($attributes, $binding);
        $check = "<input type='checkbox' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' checked='checked' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_2',
            'value' => 'test value'
        );
        $input = $this->_form->email($attributes, $binding);
        $check = "<input type='email' id='test_id' class='test_class' name='ModelMock[0][test_name_2]' value='test value' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_3',
            'value' => 'test value'
        );
        $input = $this->_form->hidden($attributes, $binding);
        $check = "<input type='hidden' id='test_id' class='test_class' name='ModelMock[0][test_name_3]' value='test value' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_4',
            'value' => 'test value'
        );
        $input = $this->_form->number($attributes, $binding);
        $check = "<input type='number' id='test_id' class='test_class' name='ModelMock[0][test_name_4]' value='test value' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_5',
            'value' => 'test value'
        );
        $input = $this->_form->search($attributes, $binding);
        $check = "<input type='search' id='test_id' class='test_class' name='ModelMock[0][test_name_5]' value='test value' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_6',
            'value' => 'test value'
        );
        $input = $this->_form->text($attributes, $binding);
        $check = "<input type='text' id='test_id' class='test_class' name='ModelMock[0][test_name_6]' value='test value' />";
        $this->assertEquals($check, $input);


        // Test with no value, but with name
        // set as the property of our object
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name'
        );
        $input = $this->_form->checkbox($attributes, $binding);
        $check = "<input type='checkbox' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->email($attributes, $binding);
        $check = "<input type='email' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->hidden($attributes, $binding);
        $check = "<input type='hidden' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->number($attributes, $binding);
        $check = "<input type='number' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->search($attributes, $binding);
        $check = "<input type='search' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->text($attributes, $binding);
        $check = "<input type='text' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $input = $this->_form->textarea($attributes, $binding);
        $check = "<textarea id='test_id' class='test_class' name='ModelMock[0][test_name]'>test value</textarea>";
        $this->assertEquals($check, $input);

    }

    public function test_RadiosWithBindingsWithoutId_ReturnsHtml() {
        $binding = new ModelMock();

        // Name set as the property of our object
        $attributes = array(
            'name'  => 'test_name',
            'value' => array(
                'green'      => 'green',
                'orange'     => 'orange',
                'test value' => 'yellow',
                'red'        => 'red',
                'blue'       => 'blue'
            )
        );

        $input = $this->_form->radios($attributes, $binding);
        $check = "<input type='radio' name='ModelMock[0][test_name]' value='green' /> <label>green</label>"
            . "<input type='radio' name='ModelMock[0][test_name]' value='orange' /> <label>orange</label>"
            . "<input type='radio' name='ModelMock[0][test_name]' value='test value' checked='checked' /> <label>yellow</label>"
            . "<input type='radio' name='ModelMock[0][test_name]' value='red' /> <label>red</label>"
            . "<input type='radio' name='ModelMock[0][test_name]' value='blue' /> <label>blue</label>";
        $this->assertEquals($check, $input);

        // Name different from the property of our object
        $attributes = array(
            'name'  => 'test',
            'value' => array(
                '17'         => 'green',
                '18'         => 'orange',
                'test value' => 'yellow',
                '19'         => 'red',
                '20'         => 'blue'
            )
        );

        $input = $this->_form->radios($attributes, $binding);
        $check = "<input type='radio' name='ModelMock[0][test]' value='17' /> <label>green</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='18' /> <label>orange</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='test value' /> <label>yellow</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='19' /> <label>red</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='20' /> <label>blue</label>";
        $this->assertEquals($check, $input);

        // Name different from the property of our object
        // and values as a non-associative array
        $attributes = array(
            'name'  => 'test',
            'value' => array('green', 'orange', 'yellow', 'red', 'blue')
        );

        $input = $this->_form->radios($attributes, $binding);
        $check = "<input type='radio' name='ModelMock[0][test]' value='green' /> <label>green</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='orange' /> <label>orange</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='yellow' /> <label>yellow</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='red' /> <label>red</label>"
            . "<input type='radio' name='ModelMock[0][test]' value='blue' /> <label>blue</label>";
        $this->assertEquals($check, $input);
    }

    public function test_SelectWithBindingsWithoutId_ReturnsHtml() {
        $binding = new ModelMock();

        // Name set as the property of our object
        $attributes = array(
            'name'  => 'test_name'
        );

        $values = array(
            'green'      => 'green',
            'orange'     => 'orange',
            'test value' => 'yellow',
            'red'        => 'red',
            'blue'       => 'blue'
        );

        $input = $this->_form->select($attributes, $values, $binding);
        $check = "<select name='ModelMock[0][test_name]'>"
            . "<option value='green'>green</option><option value='orange'>orange</option>"
            . "<option value='test value' selected='selected'>yellow</option>"
            . "<option value='red'>red</option><option value='blue'>blue</option>"
            . "</select>";
        $this->assertEquals($check, $input);

        // Name different from the property of our object
        $attributes = array('name'  => 'test');
        $values = array(
            '17'         => 'green',
            '18'         => 'orange',
            'test value' => 'yellow',
            '19'         => 'red',
            '20'         => 'blue'
        );

        $input = $this->_form->select($attributes, $values, $binding);
        $check = "<select name='ModelMock[0][test]'>"
            . "<option value='17'>green</option><option value='18'>orange</option>"
            . "<option value='test value'>yellow</option>"
            . "<option value='19'>red</option><option value='20'>blue</option>"
            . "</select>";
        $this->assertEquals($check, $input);

        // Name different from the property of our object
        // and values as a non-associative array
        $attributes = array('name'  => 'test');
        $values = array('green', 'orange', 'yellow', 'red', 'blue');

        $input = $this->_form->select($attributes, $values, $binding);
        $check = "<select name='ModelMock[0][test]'>"
            . "<option value='green'>green</option><option value='orange'>orange</option>"
            . "<option value='yellow'>yellow</option>"
            . "<option value='red'>red</option><option value='blue'>blue</option>"
            . "</select>";
        $this->assertEquals($check, $input);
    }

    public function test_MultipleBindingsWithoutId_ReturnsHtml() {
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name',
            'value' => 'test value'
        );

        $binding_1 = new ModelMock();

        $input = $this->_form->text($attributes, $binding_1);
        $check = "<input type='text' id='test_id' class='test_class' name='ModelMock[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $binding_2 = new ModelMock();

        $input = $this->_form->text($attributes, $binding_2);
        $check = "<input type='text' id='test_id' class='test_class' name='ModelMock[1][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

        $binding_3 = new ModelMock();

        $input = $this->_form->email($attributes, $binding_3);
        $check = "<input type='email' id='test_id' class='test_class' name='ModelMock[2][test_name]' value='test value' />";
        $this->assertEquals($check, $input);


        $binding_4 = new ModelMock();

        $attributes = array(
            'name'  => 'test',
            'value' => array(
                '17'         => 'green',
                '18'         => 'orange',
                'test value' => 'yellow',
                '19'         => 'red',
                '20'         => 'blue'
            ),
            'id'    => array('one', 'two', 'three', 'four', 'five')
        );

        $input = $this->_form->radios($attributes, $binding_4);
        $check = "<input type='radio' name='ModelMock[3][test]' id='one' value='17' /> <label for='one'>green</label>"
            . "<input type='radio' name='ModelMock[3][test]' id='two' value='18' /> <label for='two'>orange</label>"
            . "<input type='radio' name='ModelMock[3][test]' id='three' value='test value' /> <label for='three'>yellow</label>"
            . "<input type='radio' name='ModelMock[3][test]' id='four' value='19' /> <label for='four'>red</label>"
            . "<input type='radio' name='ModelMock[3][test]' id='five' value='20' /> <label for='five'>blue</label>";
        $this->assertEquals($check, $input);

        // Test with another object
        $binding_other = new ModelMock2();
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name',
            'value' => 'test value'
        );

        $input = $this->_form->text($attributes, $binding_other);
        $check = "<input type='text' id='test_id' class='test_class' name='ModelMock2[0][test_name]' value='test value' />";
        $this->assertEquals($check, $input);

    }

    public function test_BindingsWithId_ReturnsHtml() {
        $binding = new ModelMock();
        $binding->set_id(27);

        // Test with a custom value
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name',
            'value' => 'test'
        );
        $input = $this->_form->checkbox($attributes, $binding);
        $check = "<input type='checkbox' id='test_id' class='test_class' name='ModelMock|27[test_name]' value='test' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_2',
            'value' => 'test'
        );
        $input = $this->_form->email($attributes, $binding);
        $check = "<input type='email' id='test_id' class='test_class' name='ModelMock|27[test_name_2]' value='test' />";
        $this->assertEquals($check, $input);

        // Test with another object
        $binding = new ModelMock2();
        $binding->set_id(38);

        // Test with a custom value
        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name',
            'value' => 'test'
        );
        $input = $this->_form->checkbox($attributes, $binding);
        $check = "<input type='checkbox' id='test_id' class='test_class' name='ModelMock2|38[test_name]' value='test' />";
        $this->assertEquals($check, $input);

        $attributes = array(
            'id'    => 'test_id',
            'class' => 'test_class',
            'name'  => 'test_name_2',
            'value' => 'test'
        );
        $input = $this->_form->email($attributes, $binding);
        $check = "<input type='email' id='test_id' class='test_class' name='ModelMock2|38[test_name_2]' value='test' />";
        $this->assertEquals($check, $input);
    }


}
?>
