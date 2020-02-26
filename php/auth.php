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
    }

    // output result
    header('Content-Type: application/json');
    echo json_encode($answer);

    // close connection
    Opencloud__db_close($mysql);
}