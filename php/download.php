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
    $mysql = Opencloud__Db_connect(HOST, USER, PASSWORD, DATABASE);
    /**
     * Security check
     */
    if (!Opencloud__Db_check_login($mysql)) {
        http_response_code(401);
        print 'You cannot see files.';
        return false;
    }

    $user__id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);

    $files = Opencloud__Db_get_files($mysql, $user__id);

    header('Content-Type: application/json');
    echo json_encode($files);
    Opencloud__Db_close($mysql);
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
    $mysql = Opencloud__Db_connect(HOST, USER, PASSWORD, DATABASE);
    /**
     * Security check
     */
    if (!Opencloud__Db_check_login($mysql)) {
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
        header('Location: ' . htmlspecialchars(WEBSITE_ADDRESS));
        exit();
    }

    $files = Opencloud__Db_get_files($mysql, $user__id, $download_file__id);

    foreach ($files as $file) {
        $hash__path = TARGET_DIR . $file['hash__name'];
        $type = Opencloud__Db_get_extension_type($mysql, $file['extension__id']);
        header('Content-Type: ' . $type);
        header("Content-disposition: attachment; filename=\"" . basename(htmlspecialchars($file['real_name'])) . "\"");
        readfile($hash__path);
    }

    Opencloud__Db_close($mysql);
}

if (
    $_POST
    && isset($_POST['get_public_link'])
    && !empty($_POST['get_public_link'])
    && isset($_POST['file__id'])
    && !empty($_POST['file__id'])
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
        print 'You cannot download file.';
        return false;
    }

    // filter input
    $file__id = filter_input(INPUT_POST, 'file__id', FILTER_SANITIZE_NUMBER_INT);
    $user__id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);
    $public_link = Opencloud__Db_get_public_link($mysql, $file__id);
    if ($public_link) {
        http_response_code(200);
        $answer = array(
            'public_link' => htmlspecialchars($public_link)
        );
        header('Content-Type: application/json');
        echo json_encode($answer);
    } else {
        http_response_code(400);
        print 'No link found.';
    }

    Opencloud__Db_close($mysql);
}

/**
 * Public Download Link Handler.
 * Shows file
 * 
 * @param string $public_link
 * 
 * @return void
 */
if (
    $_GET
    && isset($_GET['public_link'])
    && !empty($_GET['public_link'])
) {
    // open connection
    $mysql = Opencloud__Db_connect(HOST, USER, PASSWORD, DATABASE);
    /**
     * Security check
     */
    if (!Opencloud__Db_check_login($mysql)) {
        http_response_code(401);
        print 'You cannot see files.';
        return false;
    }

    // filter input
    $public_link = filter_input(INPUT_GET, 'public_link', FILTER_SANITIZE_STRING);

    $file = Opencloud__Db_Get_Public_file($mysql, $public_link);
    if ($file) {
        http_response_code(200);
        $hash__path = TARGET_DIR . $file['hash__name'];
        $type = $file['type'];
        header('Content-Type: ' . $type);
        header("Content-disposition: attachment; filename=\"" . basename(htmlspecialchars($file['real_name'])) . "\"");
        readfile($hash__path);
    } else {
        http_response_code(400);
        print 'Cannot find public file';
    }
    Opencloud__Db_close($mysql);
}