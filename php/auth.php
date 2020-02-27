<?php
require 'core/core.php';
require 'variables.php';
require 'core/db.php';

/**
 * Secure Login System with PHP and MySQL
 * 
 * @link https://codeshack.io/secure-login-system-php-mysql/
 */
if (
    $_POST
    && isset($_POST['submit__login'])
    && !empty($_POST['submit__login'])
    && isset($_POST['username'])
    && isset($_POST['password'])
) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // open connection
    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    // set defaults

    $answer = Opencloud__db_login($mysql, $username, $password);

    // output result
    header('Content-Type: application/json');
    echo json_encode($answer);
    // close connection
    Opencloud__db_close($mysql);
}

if (
    $_POST
    && isset($_POST['check_login'])
    && !empty($_POST['check_login'])
) {
    // open connection
    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);

    // set defaults
    $answer = array(
        'status' => false,
        'text' => 'Default text'
    );
    if (Opencloud__db_check_login($mysql)) {
        $answer['status'] = true;
        $answer['text'] = 'Verification success!';
        http_response_code(200);
    } else {
        http_response_code(401);
    }

    // output result
    header('Content-Type: application/json');
    echo json_encode($answer);

    // close connection
    Opencloud__db_close($mysql);
}

if (
    $_GET
    && isset($_GET['logout'])
    && !empty($_GET['logout'])
) {
    unset($_COOKIE[COOKIE__USER_LOGGED_IN]);
    setcookie(COOKIE__USER_LOGGED_IN, null, -1, '/');
    unset($_COOKIE[COOKIE__USER_PASSWORD]);
    setcookie(COOKIE__USER_PASSWORD, null, -1,);
    unset($_COOKIE[COOKIE__USER_NAME]);
    setcookie(COOKIE__USER_NAME, null, -1,);
    unset($_COOKIE[COOKIE__USER_ID]);
    setcookie(COOKIE__USER_ID, null, -1,);

    // Redirect to the index page:
    header("HTTP/1.1 200 OK");
    header('Location: ' . htmlspecialchars(WEBSITE_ADDRESS));
    exit();
}