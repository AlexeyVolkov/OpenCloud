<?php
require 'variables.php';
require 'core/db.php';

if ($_POST['files_list']) {
    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    $files = Opencloud__db_get_files($mysql);

    header('Content-Type: application/json');
    echo json_encode($files);
}