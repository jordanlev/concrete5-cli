<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');

define('ADMIN_PASSWORD', 'admin'); //DON'T USE A REAL PASSWORD**
define('ADMIN_EMAIL', 'you@example.com');

define('HTDOCS_DIR', '/Applications/MAMP/htdocs/'); //INCLUDE preceeding AND trailing slashes
define('BASE_URL', 'http://localhost:8888'); //NO trailing slash

/**
 * NOTE: For security purposes, the ADMIN_PASSWORD should be something generic (e.g. "admin" or "password")
 * because it gets sent in the url querystring to the temp login page (which will stick around in browser history).
 * You can change the password to something better via the dashboard after installing the site.
 */