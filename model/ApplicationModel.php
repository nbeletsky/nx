<?php

class ApplicationModel extends core\Model
{
    protected $db= null;

    public function __construct($id=null, $repository=null) 
    {
        if ( is_null($repository) )
        {
            $repository = $this->_get_default_repository();
        }
        parent::__construct($id, $repository);
    }

    private function _get_default_repository()
    {
        $db = new PDO_MySQL(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASS);
        $cache = new MemcachedCache();
        return new Repository($db, $cache); 
    }

}

?>
