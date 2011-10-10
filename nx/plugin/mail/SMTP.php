<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\plugin\mail;

/*
 *  @package plugin
 */
class SMTP extends \nx\core\Object {

    public function __construct(array $config = array()) {
        $defaults = array(
            'host'           => '',
            'username'       => '',
            'password'       => '',
            'port'           => 25,
            'authentication' => ''
        );
        parent::__construct($config + $defaults);
    }

    public function send(array $options = array()) {
        $defaults = array(
            'message-id'   => '',
            'subject'      => '',
            'sender'       => '',
            'from'         => array(),
            'reply-to'     => '',
            'return-path'  => '',
            'to'           => array(),
            'cc'           => array(),
            'bcc'          => array(),
            'content-type' => 'text/html'
            'message'      => '',
            'attachments'  => array(),
            'read-receipt' => ''
        );
        $options += $defaults;

    }

}

?>
