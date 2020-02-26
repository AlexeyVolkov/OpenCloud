<?php
require 'core/core.php';
require 'variables.php';
require 'core/db.php';

/**
 * @link https://www.w3schools.com/php/php_file_upload.asp
 */
if ($_FILES && isset($_FILES[POST_FILE_FIELD]) && !empty($_FILES[POST_FILE_FIELD])) {
    $file_ary = Opencloud__reArrayFiles($_FILES[POST_FILE_FIELD]);

    foreach ($file_ary as $file) {
        if (0 >= $file['size']) {
            continue; // skip empty files
        }
        $target_file = TARGET_DIR . basename($file['name']);
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        /**
         * Hash for security
         */
        $hash__file =  hash_file('md5', $file['tmp_name']);
        $hash__name = hash('md5', $file['name']);
        $hash__path = TARGET_DIR . $hash__name;

        if (!Opencloud__exist($hash__path, TARGET_DIR)) {
            // 1. Put Info to DB
            $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);

            $extension__id = Opencloud__db_get_extension_id($mysql, $file['type']);
            $status__id = 1; // 1 - existing; 0 - deleted
            $size = filesize($file['tmp_name']);
            $parent_folder__id = 1; // 1 - root (default)

            // put file upload info to DB
            $file_uploaded = false;
            if (Opencloud__db_put_file($mysql, $hash__name, $hash__file, 1, $file['name'], $extension__id, $status__id, $size, $parent_folder__id)) {
                $file_uploaded = true;
            }
            // 2. Upload File
            if ($file_uploaded) {
                if (Opencloud__upload($file, $hash__path)) {
                    print 'File is Uploaded!';
                } else {
                    print 'Cannot upload file';
                }
            }
            Opencloud__db_close($mysql);
        } else {
            print 'This file already exists';
        }
    }
}

if (
    $_POST
    && isset($_POST['add_folder'])
    && !empty($_POST['add_folder'])
    && isset($_POST['add_folder__name'])
    && isset($_POST['add_folder__user_id'])
) {
    $add_folder__name = filter_input(INPUT_POST, 'add_folder__name', FILTER_SANITIZE_STRING);
    $add_folder__user_id = filter_input(INPUT_POST, 'add_folder__user_id', FILTER_SANITIZE_NUMBER_INT);
    // open connection
    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    // add folder
    $answer = Opencloud__db_put_folder($mysql, $add_folder__name,    $add_folder__user_id);

    // output result
    header('Content-Type: application/json');
    echo json_encode($answer);
    // close connection
    Opencloud__db_close($mysql);
}