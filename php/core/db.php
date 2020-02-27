<?php

if (!function_exists('Opencloud__db_connect')) {
    function Opencloud__db_connect($host, $username, $password, $database)
    {
        // filter input
        $host = filter_var(trim($host), FILTER_SANITIZE_STRING);
        $username = filter_var(trim($username), FILTER_SANITIZE_STRING);
        $password = filter_var(trim($password), FILTER_SANITIZE_STRING);
        $database = filter_var(trim($database), FILTER_SANITIZE_STRING);

        $mysqli = mysqli_connect($host, $username, $password, $database);
        if (mysqli_connect_errno()) {
            print 'Debug Info<hr><pre>';
            print 'Failed to connect to MySQL @ Opencloud__db_connect' . '<br>';
            print 'mysqli_connect_error:' . mysqli_connect_error() . '<br>';
            print '<hr></pre>';
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
        // filter input
        $user_id = filter_var(trim($user_id), FILTER_SANITIZE_NUMBER_INT);
        $getID = filter_var(trim($getID), FILTER_SANITIZE_NUMBER_INT);
        $parent_folder_id = filter_var(trim($parent_folder_id), FILTER_SANITIZE_NUMBER_INT);
        // set defaults
        $files = false;
        $files_showing = false;

        $sql = "SELECT `upload_date`, `user_id`, `real_name`, `id`, `hash__name`, `extension__id` FROM `files` WHERE `files`.`id`=? AND `files`.`user_id`=? LIMIT 1;";

        if (0 == $getID) {
            $sql = "SELECT `upload_date`, `user_id`, `real_name`, `id`, `hash__name`, `extension__id` FROM `files` WHERE `files`.`user_id` = ?;";
        }
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare($sql)) {

            /* bind parameters for markers */
            if (0 == $getID) {
                $stmt->bind_param("i", $user_id);
            } else {
                $stmt->bind_param("ii", $getID, $user_id);
            }

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($upload_date, $user_idDB, $real_name, $id, $hash__name, $extension__id);

            /* fetch values */
            while ($stmt->fetch()) {
                $files[] = array(
                    'upload_date' => htmlentities($upload_date, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'user_id' => htmlentities($user_idDB, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'real_name' => htmlentities($real_name, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'id' => htmlentities($id, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'hash__name' => htmlentities($hash__name, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'extension__id' => htmlentities($extension__id, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'type' => 'file'
                );
            }
            if (0 == $stmt->num_rows) {
                $files[0]['error_text'] = 'There are '
                    . htmlentities($stmt->num_rows, ENT_QUOTES | ENT_IGNORE, "UTF-8") .
                    ' files [user_id:'
                    . htmlentities($user_id, ENT_QUOTES | ENT_IGNORE, "UTF-8") .
                    ']';
            } else {
                $files_showing = true;
            }
            /* close statement */
            $stmt->close();
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__db_get_files' . '<br>';
            print 'user_id:' . $user_id . '<br>';
            print 'getID:' . $getID . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
        }
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
                    'user_id' => htmlentities($user_id, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'real_name' => htmlentities($folder__name, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'id' => htmlentities($folder__id, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'hash__name' => '',
                    'extension__id' => 0,
                    'type' => 'folder'
                );
            }
            if (0 == $stmt->num_rows) {
                $files[0]['error_text'] = 'There are '
                    . htmlentities($stmt->num_rows, ENT_QUOTES | ENT_IGNORE, "UTF-8") .
                    ' folders [user_id:'
                    . htmlentities($user_id, ENT_QUOTES | ENT_IGNORE, "UTF-8") .
                    ', parent_folder_id:'
                    . htmlentities($parent_folder_id, ENT_QUOTES | ENT_IGNORE, "UTF-8") .
                    ']';
            } else {
                $files_showing = true;
            }
            /* close statement */
            $stmt->close();
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__db_get_files | folder' . '<br>';
            print 'user_id:' . $user_id . '<br>';
            print 'getID:' . $getID . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
        }
        if ($files_showing) {
            return $files;
        } else {
            return false;
        }
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
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__db_put_file' . '<br>';
            print 'hash__name:' . $hash__name . '<br>';
            print 'hash__file:' . $hash__file . '<br>';
            print 'user_id:' . $user_id . '<br>';
            print 'real_name:' . $real_name . '<br>';
            print 'extension__id:' . $extension__id . '<br>';
            print 'status__id:' . $status__id . '<br>';
            print 'size:' . $size . '<br>';
            print 'parent_folder__id:' . $parent_folder__id . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
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
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__db_get_extension_id' . '<br>';
            print 'extension__string:' . $extension__string . '<br>';
            // print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
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
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__db_put_extension' . '<br>';
            print 'extension__string:' . $extension__string . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
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
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare statement @ Opencloud__db_put_folder' . '<br>';
            print 'parent_folder_id:' . $parent_folder_id . '<br>';
            print 'add_folder__user_id:' . $add_folder__user_id . '<br>';
            print 'add_folder__name:' . $add_folder__name . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
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
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare statement @ Opencloud__db_get_extension_type' . '<br>';
            print 'extension__id:' . $extension__id . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
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
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare statement @ Opencloud__db_get_filePathById' . '<br>';
            print 'remove_file__id:' . $remove_file__id . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
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
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare statement @ Opencloud__db_delete_file' . '<br>';
            print 'remove_file__id:' . $remove_file__id . '<br>';
            print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
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
                    // cookie will expire in 1 hour
                    setcookie(COOKIE__USER_LOGGED_IN, TRUE, time() + 3600, '/');
                    setcookie(COOKIE__USER_PASSWORD, $passwordPOST, time() + 3600);
                    setcookie(COOKIE__USER_NAME, $usernamePOST, time() + 3600);
                    setcookie(COOKIE__USER_ID, $id, time() + 3600);

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
            && isset($_COOKIE[COOKIE__USER_LOGGED_IN])
            && !empty($_COOKIE[COOKIE__USER_LOGGED_IN])
            && 1 == $_COOKIE[COOKIE__USER_LOGGED_IN]
            && isset($_COOKIE[COOKIE__USER_PASSWORD])
            && !empty($_COOKIE[COOKIE__USER_PASSWORD])
            && isset($_COOKIE[COOKIE__USER_NAME])
            && !empty($_COOKIE[COOKIE__USER_NAME])
            && isset($_COOKIE[COOKIE__USER_ID])
            && !empty($_COOKIE[COOKIE__USER_ID])
        ) {
            // filter input
            $passwordCOOKIE = filter_input(INPUT_COOKIE, COOKIE__USER_PASSWORD, FILTER_SANITIZE_STRING);
            $usernameCOOKIE = filter_input(INPUT_COOKIE, COOKIE__USER_NAME, FILTER_SANITIZE_STRING);
            // $idCOOKIE = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);

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