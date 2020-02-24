<?php

if (!function_exists('Opencloud__db_connect')) {
    function Opencloud__db_connect($host, $username, $password, $database)
    {
        $mysqli = mysqli_connect($host, $username, $password, $database);
        if (mysqli_connect_errno()) {
            return "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        return $mysqli;
    }
}

if (!function_exists('Opencloud__db_get_files')) {
    function Opencloud__db_get_files($mysqli, $user_id = 1)
    {
        $files = false;
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare("SELECT `upload_date`, `user_id`, `real_name` FROM `files` WHERE `files`.`user_id`=?;")) {

            /* bind parameters for markers */
            $stmt->bind_param("i", $user_id);

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($upload_date, $user_id, $real_name);

            /* fetch values */
            while ($stmt->fetch()) {
                $files[] = array($upload_date, $user_id, $real_name);
            }

            /* instead of bind_result: */
            // $files = $stmt->get_result();

            /* close statement */
            $stmt->close();
        }

        /* close connection */
        $mysqli->close();

        return $files;
    }
}