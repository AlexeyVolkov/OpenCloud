<?php
// debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// database
define('HOST', 'localhost');
define('USER', 'root');
define('PASSWORD', '');
define('DATABASE', 'open_cloud');
// file system
define('TARGET_DIR', 'E:/Sync/Work/2020/2job/napopravku/OpenCloud/files/');
define('POST_FILE_FIELD', 'files');
define('WEBSITE_ADDRESS', 'http://test.home/');
// login system
define('COOKIE__USER_LOGGED_IN', 'user__loggedin');
define('COOKIE__USER_ID', 'user__id');
define('COOKIE__USER_NAME', 'user__name');
define('COOKIE__USER_PASSWORD', 'user__password');