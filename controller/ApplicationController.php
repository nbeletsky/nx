<?php

class ApplicationController extends core\Controller
{
    protected $_session;
    protected $_user;

    public function __construct() 
    {
        $this->_session = $this->_get_default_session();

        if ( $this->_session->is_logged_in() )
        {
            $this->_user = $this->_get_default_user($this->_session->get_user_id());
        }
        else
        {
            $this->_user = null;
        }
    }

    private function _get_default_session()
    {
        return new Session(); 
    }

    private function _get_default_user($user_id)
    {
        return new User($user_id);
    }

    /**
     * data is expected in the format:
     *      { url: parent_controller_action, obj_id: object_id, 
     *            classname: obvious, field: field_to_validate, 
     *            fullname: data[Classname][field][], id: class_foo_id, 
     *               value: value_to_validate }
     */
    // TODO: Fix
    function validate()
    {
        $php_data= json_decode(stripslashes($_REQUEST['data']));
        if (is_array($php_data))
        {
            $obj = $php_data[0];
            $page= explode("=", parse_url($obj->url, PHP_URL_QUERY));
            $page= $page[1];
            $level = explode("/", parse_url($obj->url, PHP_URL_PATH));
            if ($level[1] == 'Reports')
            {
                $rptjob= core\Session::object()->RptJob->find_object(array('id'=>$level[3]));
                $return= ValidationSuite::for_change($page, $rptjob, $obj->classname, $obj->obj_id, $obj->field, $obj->value);
            }
            echo json_encode($return);
        }
    }

}

?>
