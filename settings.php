<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');

define('HTDOCS_DIR', '/Applications/MAMP/htdocs/'); //Must be the top-level htdocs directory (not a subdirectory -- that can be entered later during the installation process)
define('BASE_URL', 'http://localhost:8888'); //Must be the url to the top-level htdocs directory

define('ADMIN_EMAIL', 'you@example.com');
define('ADMIN_PASSWORD', 'admin'); //<--DON'T USE A REAL PASSWORD! (see note below)

/**
 * NOTE: For security purposes, the ADMIN_PASSWORD should be something generic (e.g. "admin" or "password")
 * because it gets sent in the url querystring to the temp login page (which will stick around in browser history).
 * You can (and should) change the password to something better via the dashboard after installing the site.
 */
