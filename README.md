Il faut également créer une fichier `.htaccess` dans le répertoire avec le contenu suivant :
```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?_uri=/$1 [QSA,NC,L]
</IfModule>
```
