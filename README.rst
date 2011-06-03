==
NX
==

Because the best framework is your own.


Server Configurations
---------------------

The following configurations will help you rewrite requests in accordance with the url layout described below.

URL layout
``````````
::

foobar.com/
foobar.com/controller[?args]
foobar.com/controller/id[?args]
foobar.com/controller/action[?args]
foobar.com/controller/action/id[?args]


nginx
`````
::

rewrite ^/$ index.php;
rewrite ^/([A-Za-z0-9\-]+)/?$ index.php?controller=$1&args=$args? break;
rewrite ^/([A-Za-z0-9\-]+)/([\d]+)/?$ index.php?controller=$1&id=$2&args=$args? break;
rewrite ^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/?$ index.php?controller=$1&action=$2&args=$args? break;
rewrite ^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/([\d]+)/?$ index.php?controller=$1&action=$2&id=$3&args=$args? break;


lighttpd
````````
::

url.rewrite-once = (
        "^/$"=>"/index.php",
        "^/([A-Za-z0-9\-]+)/?$"=>"/index.php?controller=$1",
        "^/([A-Za-z0-9\-]+)/([\d]+)/?$"=>"/index.php?controller=$1&id=$2",
        "^/([A-Za-z0-9\-]+)\?(.+)$"=>"/index.php?controller=$1&args=$2",
        "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/?$"=>"/index.php?controller=$1&action=$2",
        "^/([A-Za-z0-9\-]+)/([\d]+)\?(.+)$"=>"/index.php?controller=$1&id=$2&args=$3",
        "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/([\d]+)/?$"=>"/index.php?controller=$1&action=$2&id=$3",
        "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)\?(.+)$"=>"/index.php?controller=$1&action=$2&args=$3",
        "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/([\d]+)\?(.+)$"=>"/index.php?controller=$1&action=$2&id=$3&args=$4"
)


apache
``````

please don't use apache.  you'll thank me later.
