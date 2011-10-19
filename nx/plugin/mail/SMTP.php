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
            // TODO: Change this to be an empty string
            // and allow ssl or tls
            //'authentication' => ''

            // TODO: Remove this
            'auth'           => true
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
            'content-type' => 'text/html',
            'message'      => '',
            'attachments'  => array(),
            'read-receipt' => ''
        );
        $options += $defaults;

        // TODO: The rest of this function is temporary
        $headers = array(
            'From'    => $options['from'],
            'To'      => $options['to'],
            'Subject' => $options['subject']
        );

        require('Mail.php');
        $smtp = \Mail::factory('smtp', $this->_config);
        $mail = $smtp->send($options['to'], $headers, $options['message']);

        if ( \PEAR::isError($mail) ) {
            throw new \Exception($mail->getMessage()
                . ' Mail to ' . $options['to'] . ' could not be sent.');
            return false;
        }

        return true;

    }

}

?>
