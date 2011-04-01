<?php

class ApplicationModel extends core\Model
{

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
        $db = new \plugin\db\PDO_MySQL(DATABASE_NAME, DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
        $cache = new \plugin\cache\MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        return new \plugin\repository\Repository($db, $cache); 
    }

}

?>
