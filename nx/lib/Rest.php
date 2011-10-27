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
 *  The `Rest` class is used to handle common REST operations.
 *
 *  @package lib
 */
class Rest {

    /**
    *  Performs a POST request to a given url with supplied parameters.
    *
    *  @param string $url              The url to which to send the request.
    *  @param string $query_string     Contains the parameters to be sent.

    *  @access public
    *  @return string
    */
    public static function post($url, $query_string, $headers) {
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_HEADER, 0);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_POST, 1);
        curl_setopt($handler, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $query_string);

        $response = curl_exec($handler);
        curl_close($handler);

        return $response;
    }

}

?>
