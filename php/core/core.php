<?php
if (!function_exists('Opencloud__upload')) {
    function Opencloud__upload($file, $target_file)
    {
        return move_uploaded_file($file["tmp_name"], $target_file);
    }
}

if (!function_exists('Opencloud__exist')) {
    function Opencloud__exist($target_file, $target_dir = "files/")
    {
        return file_exists($target_file);
    }
}


if (!function_exists('Opencloud__reArrayFiles')) {
    /**
     * Ugly array -> pretty array
     * @link https://www.php.net/manual/en/features.file-upload.multiple.php#53240
     */
    function Opencloud__reArrayFiles(&$file_post)
    {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }
}