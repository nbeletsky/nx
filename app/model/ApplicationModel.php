<?php

namespace app\model;

class ApplicationModel extends \nx\core\Model {

    public function get_option($option) {
        return $this->_options[$option];
    }

}

?>
