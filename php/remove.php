<?php
require 'core/core.php';
require 'variables.php';
require 'core/db.php';

if ($_GET && isset($_GET['remove_file__id'])) {
    $remove_file__id = filter_input(INPUT_GET, 'remove_file__id', FILTER_SANITIZE_NUMBER_INT);

    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    $file_path = Opencloud__db_get_filePathById($mysql, $remove_file__id);
    if ($file_path && Opencloud__db_delete_file($mysql, $remove_file__id)) { // file info is deleted
        Opencloud__remove($file_path);
    }

    Opencloud__db_close($mysql);
}