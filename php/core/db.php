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
    function Opencloud__db_get_files($mysqli, $user_id = 1, $getID = 0, $parent_folder_id = 0)
    {
        $files = false;
        $user_id = filter_var(trim($user_id), FILTER_SANITIZE_NUMBER_INT);
        $getID = filter_var(trim($getID), FILTER_SANITIZE_NUMBER_INT);
        $parent_folder_id = filter_var(trim($parent_folder_id), FILTER_SANITIZE_NUMBER_INT);

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
                    'extension__id' => $extension__id,
                    'type' => 'file'
                );
            }
            /* close statement */
            $stmt->close();

            $sql = "SELECT `id`, `name` FROM `folders` WHERE `folders`.`parent_folder_id` = ? AND `folders`.`user__id` = ?;";
            /* create a prepared statement */
            if ($stmt = $mysqli->prepare($sql)) {
                /* bind parameters for markers */
                $stmt->bind_param("ii", $parent_folder_id,  $user_id);
                /* execute query */
                $stmt->execute();
                /* bind result variables */
                $stmt->bind_result($folder__id, $folder__name);
                /* fetch values */
                while ($stmt->fetch()) {
                    $files[] = array(
                        'upload_date' => '0000-00-00 00:00:00',
                        'user_id' => $user_id,
                        'real_name' => $folder__name,
                        'id' => $folder__id,
                        'hash__name' => '',
                        'extension__id' => 0,
                        'type' => 'folder'
                    );
                }
                /* close statement */
                $stmt->close();
            } else {
                return 'Cannot prepare SQL @ Opencloud__db_put_file | folder';
            }
        } else {
            return 'Cannot prepare SQL @ Opencloud__db_put_file';
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

            /* close statement */
            $stmt->close();
            if ($extension__id && 0 < $extension__id) {
                return $extension__id;
            } else {
                // Add new Type to DB
                Opencloud__db_put_extension($mysqli, $extension__string);
                // Get an ID again
                return Opencloud__db_get_extension_id($mysqli, $extension__string);
            }
        }

        return false;
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

if (!function_exists('Opencloud__db_put_folder')) {
    function Opencloud__db_put_folder($mysqli, $add_folder__name,    $add_folder__user_id)
    {
        $answer = false;
        $add_folder__name = filter_var(trim($add_folder__name), FILTER_SANITIZE_STRING);
        $add_folder__user_id = filter_var(trim($add_folder__user_id), FILTER_SANITIZE_NUMBER_INT);

        $parent_folder_id = 0;

        /* create a prepared statement */
        if ($stmt = $mysqli->prepare(
            "INSERT INTO `folders` (`id`, `parent_folder_id`, `user__id`, `name`) VALUES (NULL, ?, ?, ?);"
        )) {
            /* bind parameters for markers */
            $stmt->bind_param("iis", $parent_folder_id, $add_folder__user_id, $add_folder__name);
            /* execute query */
            $stmt->execute();
            /* close statement */
            $stmt->close();

            $answer[] = array(
                'text' => 'Folder was successfully added',
                'code' => 200,
                'status' => true
            );
        } else {
            $answer = 'Cannot prepare SQL @ Opencloud__db_put_file';
        }

        return $answer;
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

if (!function_exists('Opencloud__db_get_filePathById')) {
    function Opencloud__db_get_filePathById($mysqli, $remove_file__id)
    {
        // filter input
        $remove_file__id = filter_var(trim($remove_file__id), FILTER_SANITIZE_NUMBER_INT);

        // set defaults
        $return_path = false;

        /* create a prepared statement */
        if ($stmt = $mysqli->prepare("SELECT `hash__name` FROM `files` WHERE `id`=? LIMIT 1;")) {
            /* bind parameters for markers */
            $stmt->bind_param("i", $remove_file__id);

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($db_path);

            /* fetch value */
            $stmt->fetch();
            if (1 < strlen($db_path)) {
                $return_path = $db_path;
            }
            /* close statement */
            $stmt->close();
        }

        return TARGET_DIR . $return_path;
    }
}

if (!function_exists('Opencloud__db_delete_file')) {
    function Opencloud__db_delete_file($mysqli, $remove_file__id)
    {
        // filter input
        $remove_file__id = filter_var(trim($remove_file__id), FILTER_SANITIZE_NUMBER_INT);
        // set defaults
        $flag = false;
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare(
            "DELETE FROM `files` WHERE `files`.`id` = ?;"
        )) {
            /* bind parameters for markers */
            $stmt->bind_param("i", $remove_file__id);
            /* execute query */
            $stmt->execute();
            /* close statement */
            $stmt->close();

            $flag = true;
        } else {
            print 'Cannot prepare SQL @ Opencloud__db_delete_file';
        }

        return $flag;
    }
}

if (!function_exists('Opencloud__db_login')) {
    function Opencloud__db_login($mysqli, $username, $password)
    {
        // filter input
        $usernamePOST = filter_var(trim($username), FILTER_SANITIZE_STRING);
        $passwordPOST = filter_var(trim($password), FILTER_SANITIZE_STRING);
        // set defaults
        $answer = array(
            'status' => false,
            'text' => 'Default text'
        );
        /* create a prepared statement */
        $sql = 'SELECT `id`, `password` FROM `users` WHERE `username` = ? LIMIT 1;';
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            // if check_login           
            $stmt->bind_param('s', $usernamePOST);

            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $password);
                $stmt->fetch();
                // Account exists, now we verify the password.
                // Note: remember to use password_hash in your registration file to store the hashed passwords.
                if (password_verify($passwordPOST, $password)) {
                    // Verification success! User has loggedin!
                    // $_COOKIE['loggedin'] = TRUE;
                    // $_COOKIE['password'] = $passwordPOST;
                    // $_COOKIE['username'] = $usernamePOST;
                    // $_COOKIE['id'] = $id;
                    // cookie will expire in 1 hour
                    setcookie("loggedin", TRUE, time() + 3600);
                    setcookie("password", $passwordPOST, time() + 3600);
                    setcookie("username", $usernamePOST, time() + 3600);
                    setcookie("id", $id, time() + 3600);

                    $answer['status'] = true;
                    $answer['text'] = 'Verification success!';
                } else {
                    $answer['status'] = false;
                    $answer['text'] = 'Incorrect password!';
                }
            } else {
                // Incorrect username!
                $answer['status'] = false;
                $answer['text'] = 'Incorrect username!';
            }
            /* close statement */
            $stmt->close();
        } else {
            $answer['status'] = false;
            $answer['text'] = 'Cannot prepare SQL @ Opencloud__db_login';
        }
        return $answer;
    }
}

if (!function_exists('Opencloud__db_check_login')) {
    function Opencloud__db_check_login($mysqli)
    {
        if (
            $_COOKIE
            && isset($_COOKIE['loggedin'])
            && !empty($_COOKIE['loggedin'])
            && true === $_COOKIE['loggedin']
            && isset($_COOKIE['password'])
            && !empty($_COOKIE['password'])
            && isset($_COOKIE['username'])
            && !empty($_COOKIE['username'])
            && isset($_COOKIE['id'])
            && !empty($_COOKIE['id'])
        ) {
            // filter input
            $passwordCOOKIE = filter_input(INPUT_COOKIE, 'password', FILTER_SANITIZE_STRING);
            $usernameCOOKIE = filter_input(INPUT_COOKIE, 'username', FILTER_SANITIZE_STRING);
            // $idCOOKIE = filter_input(INPUT_COOKIE, 'id', FILTER_SANITIZE_NUMBER_INT);

            $logged_in__answer = Opencloud__db_login($mysqli, $usernameCOOKIE, $passwordCOOKIE);
            if (
                isset($logged_in__answer['status'])
                && true === $logged_in__answer['status']
            ) {
                // Verification success!
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}