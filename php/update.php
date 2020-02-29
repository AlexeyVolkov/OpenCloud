<?php
require 'core/core.php';
require 'variables.php';
require 'core/db.php';


if (
    $_POST
    && isset($_POST['file__rename'])
    && !empty($_POST['file__rename'])
    && isset($_POST['file__id'])
    && !empty($_POST['file__id'])
    && isset($_POST['file__name'])
    && !empty($_POST['file__name'])
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
    $mysql = Opencloud__Db_connect(HOST, USER, PASSWORD, DATABASE);
    /**
     * Security check
     */
    if (!Opencloud__Db_check_login($mysql)) {
        http_response_code(401);
        print 'You cannot rename file.';
        return false;
    }

    $file__id = filter_input(INPUT_POST, 'file__id', FILTER_SANITIZE_NUMBER_INT);
    $file__name = filter_input(INPUT_POST, 'file__name', FILTER_SANITIZE_STRING);

    $file_renamed = Opencloud__Db_rename($mysql, $file__id, $file__name);

    if ($file_renamed) {
        http_response_code(200);
        print 'File was renamed Successfully!';
    } else {
        http_response_code(400);
        print 'Cannot rename file';
    }
    Opencloud__Db_close($mysql);
}