<?php
require 'core/db.php';

define('HOST', 'localhost');
define('USER', 'root');
define('PASSWORD', '');
define('DATABASE', 'open_cloud');

if ($_POST['files_list']) {
    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    $files = Opencloud__db_get_files($mysql);

    header('Content-Type: application/json');
    echo json_encode($files);
}