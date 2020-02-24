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
        if ($stmt = $mysqli->prepare("SELECT * FROM `files` WHERE `files`.`user_id`=?;")) {

            /* bind parameters for markers */
            $stmt->bind_param("i", $user_id);

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($files);

            /* fetch value */
            $stmt->fetch();

            /* close statement */
            $stmt->close();
        }

        /* close connection */
        $mysqli->close();

        return $files;
    }
}