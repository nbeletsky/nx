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
        $db = new plugins\DB\PDO_MySQL(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASS);
        $cache = new plugins\cache\MemcachedCache();
        $cache->add_server(MEMCACHED_HOST);
        return new core\Repository($db, $cache); 
    }

}

?>
