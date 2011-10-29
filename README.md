# NX

## URL layouts

NX supports the following URL constructions:

    foobar.com/
    foobar.com/controller[?args]
    foobar.com/controller/id[?args]
    foobar.com/controller/action[?args]
    foobar.com/controller/action/id[?args]

## Server Configurations

### nginx

(Note that you will need to change the directories according to your filesystem.  In this example, /srv/http/ava/ is the project root.)

	server {
		 server_name     nara;
		 access_log      /var/log/nginx/nara/access.log;
		 error_log       /var/log/nginx/nara/error.log;
	 
		 location / {
			 root    /srv/http/ava/app/public;
			 index   index.html index.htm index.php;
		 }
	 
		 rewrite ^/js\/(.+)\.js$ /js/$1.js break;
		 rewrite ^/css\/(.+)\.css$ /css/$1.css break;
		 rewrite ^/img\/(.+)\.(jpg|gif|jpeg|png)$ /img/$1.$2 break;
		 rewrite ^/(.+)$ /index.php?url=$1 break;

		 location ~ \.php$ {
			 root            /srv/http/ava/app/public;       
			 fastcgi_pass    unix:/var/run/php-fpm/php-fpm.sock;
			 fastcgi_index   index.php;
			 fastcgi_param   SCRIPT_FILENAME  /srv/http/ava/app/public/$fastcgi_script_name;
			 include         fastcgi_params;
		 }
	 }

### lighttpd

    TODO

### apache

    TODO
