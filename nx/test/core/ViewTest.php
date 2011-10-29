<?php

namespace nx\test\core;

use nx\core\View;

class ViewTest extends \PHPUnit_Framework_TestCase {

    protected $_compiled_dir = 'compiled/';
    protected $_file;
    protected $_path;
    protected $_view;

    public function setUp() {
        $this->_view = new View();

        $this->_path = dirname(__FILE__) . '/';
        $this->_file = $this->_path . 'test.html';
        $contents = "<html>
    <body>\n
        <?=\$hello;?> <?=str_replace('test', 'yes', \$hello);?> <?=\$this->_form->email(array('class' => 'test'));?> <?php echo \$hello; ?>\n
    </body>\n
</html>";

        file_put_contents($this->_file, $contents);
    }

    public function tearDown() {
        $pattern = $this->_path .$this->_compiled_dir . '*.html';
        foreach ( glob($pattern) as $file ) {
            unlink($file);
        }
        rmdir($this->_path . $this->_compiled_dir);
        unlink($this->_file);
    }

    public function test_RenderFile_ReturnsHTML() {
        $hello = 'test please';
        $result = $this->_view->render($this->_file, compact('hello'));
        $check = "<html>
    <body>\n
        test please yes please <input type='email' class='test' /> test please
    </body>\n
</html>";
        $this->assertEquals($result, $check);
    }


}

?>
