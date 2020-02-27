<?php
require 'variables.php';
require 'core/db.php';

if (
    $_POST
    && isset($_POST['files_list'])
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
        http_response_code(401);
        print 'You cannot see files.';
        return false;
    }

    $user__id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);

    $files = Opencloud__db_get_files($mysql, $user__id);

    header('Content-Type: application/json');
    echo json_encode($files);
    Opencloud__db_close($mysql);
}

if (
    $_GET
    && isset($_GET['download_file__id'])
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
        http_response_code(401);
        print 'You cannot download file.';
        return false;
    }

    // filter input
    $download_file__id = filter_input(INPUT_GET, 'download_file__id', FILTER_SANITIZE_NUMBER_INT);
    $user__id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);

    if (0 >= $download_file__id) {
        // Redirect to the index page:
        http_response_code(400);
        header('Location: ' . WEBSITE_ADDRESS);
        exit();
    }

    $files = Opencloud__db_get_files($mysql, $user__id, $download_file__id);

    foreach ($files as $file) {
        $hash__path = TARGET_DIR . $file['hash__name'];
        $type = Opencloud__db_get_extension_type($mysql, $file['extension__id']);
        header('Content-Type: ' . $type);
        header("Content-disposition: attachment; filename=\"" . basename($file['real_name']) . "\"");
        readfile($hash__path);
    }

    Opencloud__db_close($mysql);
}