<?php
require 'core/core.php';

// VARIABLES
$_target_dir = "E:/Sync/Work/2020/2job/napopravku/OpenCloud/files/";
$_file_name = 'files';

/**
 * MAIN CODE BEGINs here
 * 
 * @link https://www.w3schools.com/php/php_file_upload.asp
 */
if ($_FILES[$_file_name]) {
    $file_ary = Opencloud__reArrayFiles($_FILES[$_file_name]);

    foreach ($file_ary as $file) {
        if (0 >= $file['size']) {
            continue; // skip empty files
        }
        $target_file = $_target_dir . basename($file['name']);
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!Opencloud__exist($target_file, $_target_dir)) {
            if (Opencloud__upload($file, $target_file)) {
                print 'saved!';
            }
        }
    }
}
/**
 * MAIN CODE ENDs here
 */