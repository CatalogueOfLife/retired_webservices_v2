php_value session.auto_start 0

RewriteEngine On

####################################################
# When using aliases in stead of virtual hosts,
# or if the virtual host's document root does
# not point to the directory containing index.php,
# set the RewriteBase!!
####################################################
RewriteBase /


RewriteCond %{REQUEST_FILENAME} \.(js|ico|gif|jpg|jpeg|png|css|pdf|pdf)$ [OR]
RewriteCond %{REQUEST_FILENAME} favicon.ico$ [OR]
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]
RewriteRule ^.*$ index.php [NC,L]