<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\lib;

/*
 *  The `File` class contains methods that deal with
 *  file manipulation.
 *
 *  @package lib
 */
class File {

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
        foreach ( new \DirectoryIterator($directory) as $file ) {
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
