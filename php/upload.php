<?php
require 'core/core.php';
require 'variables.php';
require 'core/db.php';

/**
 * @link https://www.w3schools.com/php/php_file_upload.asp
 */
if (
    $_FILES
    && isset($_FILES[POST_FILE_FIELD])
    && !empty($_FILES[POST_FILE_FIELD])
    && $_POST
    && isset($_POST['parent_folder__id'])
    && !empty($_POST['parent_folder__id'])
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
        print 'You cannot upload files.';
        return false;
    }

    $file_ary = Opencloud__reArrayFiles($_FILES[POST_FILE_FIELD]);
    // filter input
    $user__id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);
    $parent_folder__id = filter_input(INPUT_POST, 'parent_folder__id', FILTER_SANITIZE_NUMBER_INT);

    foreach ($file_ary as $file) {
        if (0 >= $file['size']) {
            continue; // skip empty files
        }
        $target_file = TARGET_DIR . basename($file['name']);
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        /**
         * Hash for security
         */
        $hash__file = hash_file('md5', $file['tmp_name']);
        $hash__name = hash('md5', $file['name']);
        $hash__path = TARGET_DIR . $hash__name;

        // 1. Put Info to DB

        $extension__id = Opencloud__Db_get_extension_id($mysql, $file['type']);
        $status__id = 1; // 1 - existing; 0 - deleted
        $size = filesize($file['tmp_name']);

        // put file upload info to DB
        $file_uploaded = false;
        if (Opencloud__Db_put_file($mysql, $hash__name, $hash__file, $user__id, $file['name'], $extension__id, $status__id, $size, $parent_folder__id)) {
            $file_uploaded = true;
        }
        // 2. Upload File
        if ($file_uploaded && !Opencloud__exist($hash__path, TARGET_DIR)) {
            if (Opencloud__upload($file, $hash__path)) {
                http_response_code(200);
                print ' File is Uploaded! ';
            } else {
                print 'Cannot upload file or this file already exists';
            }
        }
    }
    Opencloud__Db_close($mysql);
}

if (
    $_POST
    && isset($_POST['add_folder'])
    && !empty($_POST['add_folder'])
    && isset($_POST['add_folder__name'])
    && isset($_POST['parent_folder__id'])
    && !empty($_POST['parent_folder__id'])
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
        print 'You cannot add folder.';
        return false;
    }

    $add_folder__name = filter_input(INPUT_POST, 'add_folder__name', FILTER_SANITIZE_STRING);
    $parent_folder__id = filter_input(INPUT_POST, 'parent_folder__id', FILTER_SANITIZE_NUMBER_INT);
    $add_folder__user_id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);
    // add folder
    $answer = Opencloud__Db_put_folder($mysql, $add_folder__name, $add_folder__user_id, $parent_folder__id);

    if ($answer) {
        http_response_code(200);
        // output result
        header('Content-Type: application/json');
        echo json_encode($answer);
    }
    // close connection
    Opencloud__Db_close($mysql);
}