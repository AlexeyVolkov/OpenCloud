<?php
require 'core/core.php';
require 'variables.php';
require 'core/db.php';

if (
    $_GET
    && isset($_GET['remove_file__id'])
    && $_COOKIE
    && isset($_COOKIE[COOKIE__USER_LOGGED_IN])
    && !empty($_COOKIE[COOKIE__USER_LOGGED_IN])
    && 1 == $_COOKIE[COOKIE__USER_LOGGED_IN]
    && isset($_COOKIE[COOKIE__USER_NAME])
    && !empty($_COOKIE[COOKIE__USER_NAME])
    && isset($_COOKIE[COOKIE__USER_ID])
    && !empty($_COOKIE[COOKIE__USER_ID])
) {
    // open connection
    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    /**
     * Security check
     */
    if (!Opencloud__db_check_login($mysql)) {
        print 'You cannot remove file.';
        return false;
    }

    $remove_file__id = filter_input(INPUT_GET, 'remove_file__id', FILTER_SANITIZE_NUMBER_INT);

    $file_path = Opencloud__db_get_filePathById($mysql, $remove_file__id);
    if ($file_path && Opencloud__db_delete_file($mysql, $remove_file__id)) { // file info is deleted
        Opencloud__remove($file_path);
    }

    Opencloud__db_close($mysql);
    // Redirect to the index page:
    header("HTTP/1.1 200 OK");
    header('Location: ' . WEBSITE_ADDRESS);
    exit();
}