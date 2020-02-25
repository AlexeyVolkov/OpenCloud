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

if (!function_exists('Opencloud__db_close')) {
    function Opencloud__db_close($mysqli)
    {
        /* close connection */
        $mysqli->close();
    }
}

if (!function_exists('Opencloud__db_get_files')) {
    function Opencloud__db_get_files($mysqli, $user_id = 1, $getID = 0)
    {
        $files = false;
        $user_id = filter_var(trim($user_id), FILTER_SANITIZE_NUMBER_INT);
        $getID = filter_var(trim($getID), FILTER_SANITIZE_NUMBER_INT);

        $sql = "SELECT `upload_date`, `user_id`, `real_name`, `id`, `hash__name`, `extension__id` FROM `files` WHERE `files`.`id`=? LIMIT 1;";

        if (0 == $getID) {
            $sql = "SELECT `upload_date`, `user_id`, `real_name`, `id`, `hash__name`, `extension__id` FROM `files` WHERE `files`.`user_id`=?;";
        }
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare($sql)) {

            /* bind parameters for markers */
            if (0 == $getID) {
                $stmt->bind_param("i", $user_id);
            } else {
                $stmt->bind_param("i", $getID);
            }

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($upload_date, $user_id, $real_name, $id, $hash__name, $extension__id);

            /* fetch values */
            while ($stmt->fetch()) {
                $files[] = array(
                    'upload_date' => $upload_date,
                    'user_id' => $user_id,
                    'real_name' => $real_name,
                    'id' => $id,
                    'hash__name' => $hash__name,
                    'extension__id' => $extension__id
                );
            }
            /* close statement */
            $stmt->close();
        }

        return $files;
    }
}


if (!function_exists('Opencloud__db_put_file')) {
    function Opencloud__db_put_file($mysqli, $hash__name, $hash__file, $user_id, $real_name, $extension__id, $status__id, $size, $parent_folder__id)
    {
        // filter input
        $hash__name = filter_var(trim($hash__name), FILTER_SANITIZE_STRING);
        $hash__file = filter_var(trim($hash__file), FILTER_SANITIZE_STRING);
        $user_id = filter_var(trim($user_id), FILTER_SANITIZE_NUMBER_INT);
        $real_name = filter_var(trim($real_name), FILTER_SANITIZE_STRING);
        $extension__id = filter_var(trim($extension__id), FILTER_SANITIZE_NUMBER_INT);
        $status__id = filter_var(trim($status__id), FILTER_SANITIZE_NUMBER_INT);
        $size = filter_var(trim($size), FILTER_SANITIZE_NUMBER_INT);
        $parent_folder__id = filter_var(trim($parent_folder__id), FILTER_SANITIZE_NUMBER_INT);
        // set defaults
        $flag = false;
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare(
            "INSERT INTO `files` (`id`, `upload_date`, `hash__name`, `hash__file`, `user_id`, `real_name`, `extension__id`, `status__id`, `size`, `parent_folder__id`) VALUES (NULL, NOW(), ?, ?, ?, ?, ?, ?, ?, ?);"
        )) {
            /* bind parameters for markers */
            $stmt->bind_param("ssisiiii", $hash__name, $hash__file, $user_id, $real_name, $extension__id, $status__id, $size, $parent_folder__id);
            /* execute query */
            $stmt->execute();
            /* close statement */
            $stmt->close();

            $flag = true;
        } else {
            print 'Cannot prepare SQL @ Opencloud__db_put_file';
        }

        return $flag;
    }
}

if (!function_exists('Opencloud__db_get_extension_id')) {
    function Opencloud__db_get_extension_id($mysqli, $extension__string)
    {
        // filter input
        $extension__string = filter_var(trim($extension__string), FILTER_SANITIZE_STRING);
        // set defaults
        $return_extension__id = 1; // undefined

        /* create a prepared statement */
        if ($stmt = $mysqli->prepare("SELECT `id` FROM `extensions` WHERE `type`=? LIMIT 1;")) {

            /* bind parameters for markers */
            $stmt->bind_param("s", $extension__string);

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($extension__id);

            /* fetch value */
            $stmt->fetch();
            if (1 < $extension__id) {
                $return_extension__id = $extension__id;
            } else {
                // Add new Type to DB
                Opencloud__db_put_extension($mysqli, $extension__string);
                // Get an ID again
                Opencloud__db_get_extension_id($mysqli, $extension__string);
            }
            /* close statement */
            $stmt->close();
        }

        return $return_extension__id;
    }
}

if (!function_exists('Opencloud__db_put_extension')) {
    function Opencloud__db_put_extension($mysqli, $extension__string)
    {
        $flag = false;
        $extension__string = filter_var(trim($extension__string), FILTER_SANITIZE_STRING);

        /* create a prepared statement */
        if ($stmt = $mysqli->prepare(
            "INSERT INTO `extensions` (`id`, `type`) VALUES (NULL, ?);"
        )) {
            /* bind parameters for markers */
            $stmt->bind_param("s", $extension__string);
            /* execute query */
            $stmt->execute();
            /* close statement */
            $stmt->close();

            $flag = true;
        }

        return $flag;
    }
}

if (!function_exists('Opencloud__db_get_extension_type')) {
    function Opencloud__db_get_extension_type($mysqli, $extension__id)
    {
        // filter input
        $extension__id = filter_var(trim($extension__id), FILTER_SANITIZE_NUMBER_INT);
        // set defaults
        $return_extension__type = 'text/plain'; // undefined

        /* create a prepared statement */
        if ($stmt = $mysqli->prepare("SELECT `type` FROM `extensions` WHERE `id`=? LIMIT 1;")) {

            /* bind parameters for markers */
            $stmt->bind_param("s", $extension__id);

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($extension__type);

            /* fetch value */
            $stmt->fetch();
            if (1 < strlen($extension__type)) {
                $return_extension__type = $extension__type;
            }
            /* close statement */
            $stmt->close();
        }

        return $return_extension__type;
    }
}