<?php

namespace lib;

// TODO: Clean this file!
class File {

   /**
    *  Creates a snapshot of the test results.
    *
    *  @param string $data            The data to be written.
    *  @param string $filename        The filename to be used. 
    *  @access public
    *  @return void
    */
    public function create_snapshot($data, $filename) {
        $top = BASE_INSTALL . '/' . SNAPSHOT_DIRECTORY;
        if ( $top{strlen($top) - 1} !== '/' ) {
            $top .= '/';
        }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $final_name = $top .  $ext . '/' . basename($filename, '.' . $ext) . '_' . date('d-m-Y G:i') . '.' . $ext;
        $this->write_file($final_name, $data);
        chmod($filename, 0777);
    }

   /**
    *  Erases the contents of a file. 
    *
    *  @param string $filename        The file to be emptied.
    *  @access public
    *  @return void
    */
    public function empty_file($filename) {
        $this->write_file($filename, '', 'w');
    }

    public function get_filenames_within($directory) {
        $filenames = array();
        foreach (new \DirectoryIterator($directory) as $file) {
            if ( $file->isDot() ) {
                continue;
            }
            $filenames[] = $file->getFilename();
        }
        return $filenames;
    }

   /**
    * Determines the Content-Type for a given file
    * 
    * @param string $file_name
    */
    public function parse_content_type($file_name) {
	$ext = array_pop(explode(".", $file_name));

	$supported_types = array('doc', 'pdf', 'ppt', 'png', 'jpg', 'xls');
	if (!in_array($ext, $supported_types)) {
	    throw new \Exception('Unsupported extension: ' . $ext . ' from file name ' . $file_name);
	}
	return 'application/'.strtolower($ext);
    }
	
   /**
    * Outputs a single file in response to HTTP get
    *     
    * @param string $os_file_name Full path file name for file
    * @param string $user_file_name File name displayed to the user
    */
    public function render($os_file_name, $user_file_name) {
    	if(!file_exists($os_file_name)) {
    	    throw new exception\PloofException("File $os_file_name Not found");
        }

    	// header("Cache-control: none");
    	header("Pragma: private");
    	header("Cache-control: private, must-revalidate");
    	header("Content-Type: ". $this->parse_content_type($os_file_name));
    	header('Content-Disposition: attachment; filename="'.$user_file_name.'"');
    	$content = file_get_contents ($os_file_name);
    	print($content);
    	exit();
    }

   /**
    *  Writes data to a file.
    *
    *  @param string $filename        The name of the file.
    *  @param string $data            The data to be written.
    *  @param string $mode            The type of access to be granted to the file handle.
    *  @access public
    *  @return string
    */
    public function write_file($filename, $data, $mode = 'a') {
        $handle = @fopen($filename, $mode);
        if ( !$handle ) {
            // TODO: Set exception handler!
            throw new \Exception('Could not open ' . $filename . ' for writing.  Check the location and permissions of the file and try again.');
        }

        fwrite($handle, $data);
        fclose($handle);
        return true;
    }

}
