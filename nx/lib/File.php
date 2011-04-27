<?php

namespace nx\lib;

class File {

   /**
    *  Creates a snapshot of the test results.
    *
    *  @param string $data            The data to be written.
    *  @param string $filename        The filename to be used. 
    *  @access public
    *  @return void
    */
    // TODO: Fix this
    public static function create_snapshot($data, $filename) {
        $top = BASE_INSTALL . '/' . SNAPSHOT_DIRECTORY;
        if ( $top{strlen($top) - 1} !== '/' ) {
            $top .= '/';
        }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $final_name = $top .  $ext . '/' . basename($filename, '.' . $ext) . '_' . date('d-m-Y G:i') . '.' . $ext;
        self::write($final_name, $data);
        chmod($filename, 0777);
    }

   /**
    *  Erases the contents of a file. 
    *
    *  @param string $filename        The file to be emptied.
    *  @access public
    *  @return void
    */
    public static function empty_file($filename) {
        self::write($filename, '', 'w');
    }

   /**
    *  Returns the names of all of the files within a directory.
    *
    *  @param string $directory        The directory from which to retrieve the filenames.
    *  @access public
    *  @return array
    */
    public static function get_filenames_within($directory) {
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
    *  Writes data to a file.
    *
    *  @param string $filename        The name of the file.
    *  @param string $data            The data to be written.
    *  @param string $mode            The type of access to be granted to the file handle.
    *  @access public
    *  @return bool
    */
    public static function write($filename, $data, $mode = 'a') {
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
