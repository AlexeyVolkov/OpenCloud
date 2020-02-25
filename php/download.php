<?php
require 'variables.php';
require 'core/db.php';

if ($_POST && isset($_POST['files_list'])) {
    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    $files = Opencloud__db_get_files($mysql);

    header('Content-Type: application/json');
    echo json_encode($files);
    Opencloud__db_close($mysql);
}

if ($_GET && isset($_GET['download_file__id'])) {
    $download_file__id = filter_input(INPUT_GET, 'download_file__id', FILTER_SANITIZE_NUMBER_INT);

    $mysql = Opencloud__db_connect(HOST, USER, PASSWORD, DATABASE);
    $files = Opencloud__db_get_files($mysql);

    foreach ($files as $file) {
        $hash__path = TARGET_DIR . $file['hash__name'];
        $type = Opencloud__db_get_extension_type($mysql, $file['id']);
        header('Content-Type: ' . $type);
        header("Content-disposition: attachment; filename=\"" . basename($file['real_name']) . "\"");
        readfile($hash__path);
    }


    Opencloud__db_close($mysql);
}