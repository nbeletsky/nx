<?php
namespace lib;

class URL {

   /**
    * Return name if 'current url' matches 'url', or <a href='url'>name</a>.
    *  Useful for navigation systems.
    */
    public function match_or_link($url, $name)
    {
        if ($this->url_matches($url)) 
            return "<a href='$url' class='menumatch'>$name</a>";
        else 
            return "<a href='$url'>$name</a>";
    }

    public function parent_url()
    {
	if ($_REQUEST['parent'] and $_REQUEST['parentid'])
	    return "/".$_REQUEST['parent']."/".$_REQUEST['parentid'];
	
	return "";
    }

    /**
    * What is probably a horrible url matcher against the uri...
    */
    public function url_matches($url_to_match_against_uri)
    {
        $url= $url_to_match_against_uri;
        $uri= $_SERVER['REQUEST_URI'];
        
        if ($url=="/")
            return ($uri=="/");
        
        list($url_con, $url_act, $url_id) = $this->get_url_parts($url);
        list($uri_con, $uri_act, $uri_id) = $this->get_url_parts($uri);
            
        if ($url_con and $url_act and $url_id)
            return $this->get_url_parts($url) == $this->get_url_parts($uri);
        if ($url_con and $url_act)
            return ($url_con == $uri_con and $url_act == $uri_act);
        if ($url_con)
            return ($url_con == $uri_con);
    }       

}

?>
