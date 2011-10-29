<?php

namespace nx\test\lib;

use nx\lib\Compiler;

class CompilerTest extends \PHPUnit_Framework_TestCase {

    protected $_compiled_dir = 'compiled/';
    protected $_file;
    protected $_path;

    public function setUp() {
        $this->_path = dirname(__FILE__) . '/';
        $this->_file = $this->_path . 'test.html';
        $contents = "<html>
    <body>
        <?=\$hello;?>
        <?=str_replace('hi', 'howdy', \$hello);?>
        <?=\$this->_form->email(array('class' => 'test'));?>
        <?php echo \$hello; ?>
    </body>
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

    public function test_CompileFile_ReturnsFileLocation() {
        $path = $this->_compiled_dir;
        $result = Compiler::compile($this->_file, compact('path'));
        $this->assertTrue(file_exists($result));
    }

    public function test_CompileFile_ReturnsCompiledContents() {
        $path = $this->_compiled_dir;
        $result = Compiler::compile($this->_file, compact('path'));
        $result = file_get_contents($result);
        $check = "<html>
    <body>
        <?php echo \$this->_form->escape(\$hello); ?>
        <?php echo \$this->_form->escape(str_replace('hi', 'howdy', \$hello)); ?>
        <?php echo \$this->_form->email(array('class' => 'test')); ?>
        <?php echo \$hello; ?>
    </body>
</html>";
        $this->assertEquals($result, $check);
    }

    public function test_CompileFile_HitsCache() {
        $path = $this->_compiled_dir;

        $first = Compiler::compile($this->_file, compact('path'));
        $first_glob = glob($this->_path . $this->_compiled_dir . '/*');

        clearstatcache();
        $cached = Compiler::compile($this->_file, compact('path'));
        $second_glob = glob($this->_path . $this->_compiled_dir . '/*');
        $this->assertEquals($cached, $first);
        $this->assertEquals($first_glob, $second_glob);

        file_put_contents($this->_file, 'Some new stuff');
        clearstatcache();
        $new = Compiler::compile($this->_file, compact('path'));
        $new_glob = glob($this->_path . $this->_compiled_dir . '/*');

        $this->assertNotEquals($cached, $new);
        $this->assertEquals(count($first_glob), count($new_glob));
        $this->assertNotEquals($first_glob, $new_glob);
    }

}

?>
