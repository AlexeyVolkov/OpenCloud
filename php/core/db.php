<?php

/**
 * OpenCloud Database funtions
 *
 * @package Opencloud
 * @since 1.0.0
 */

/**
 * Table of Contents:
 * 
 * Opencloud__Db_connect
 * Opencloud__Db_close
 * Opencloud__Db_Get_files
 * Opencloud__Db_put_file
 * Opencloud__Db_get_extension_id
 * Opencloud__Db_put_extension
 * Opencloud__Db_put_folder
 * Opencloud__Db_get_extension_type
 * Opencloud__Db_get_filePathById
 * Opencloud__Db_delete_file
 * Opencloud__Db_login
 * Opencloud__Db_check_login
 */

if (!function_exists('Opencloud__Db_connect')) {
    /**
     * Connects to DataBase
     * 
     * @param string $host Host name.
     * @param string $username The MySQL user name.
     * @param string $password The MySQL password.
     * @param string $database The MySQL database.
     * 
     * @return mysqli|false Object which represents the connection to a MySQL Server or false if an error occurred.
     */
    function Opencloud__Db_connect($host, $username, $password, $database)
    {
        // filter input
        $host = filter_var(trim($host), FILTER_SANITIZE_STRING);
        $username = filter_var(trim($username), FILTER_SANITIZE_STRING);
        $password = filter_var(trim($password), FILTER_SANITIZE_STRING);
        $database = filter_var(trim($database), FILTER_SANITIZE_STRING);

        $mysqli = mysqli_connect($host, $username, $password, $database);
        if (mysqli_connect_errno()) {
            print 'Debug Info<hr><pre>';
            print 'Failed to connect to MySQL @ Opencloud__Db_connect' . '<br>';
            print 'mysqli_connect_error:' . htmlspecialchars(mysqli_connect_error()) . '<br>';
            print '<hr></pre>';
        }
        return $mysqli;
    }
}

if (!function_exists('Opencloud__Db_close')) {
    /**
     * Close connection with DataBase
     * 
     * @param mysqli $mysqli Object which represents the connection to a MySQL Server.
     * 
     * @see Opencloud__Db_connect
     * 
     * @return void
     */
    function Opencloud__Db_close($mysqli)
    {
        /* close connection */
        $mysqli->close();
    }
}

if (!function_exists('Opencloud__Db_Get_files')) {
    /**
     * Returns list of files
     * 
     * @param mysqli $mysqli Object which represents the connection to a MySQL Server.
     * @param int $user_id User ID.
     * @param int $parent_folder_id Parent folder ID.
     * 
     * @return array|false List of files
     */
    function Opencloud__Db_Get_files($mysqli, $user_id = 1, $parent_folder_id = 1)
    {
        // filter input
        $user_id = filter_var(trim($user_id), FILTER_SANITIZE_NUMBER_INT);
        $parent_folder_id = filter_var(trim($parent_folder_id), FILTER_SANITIZE_NUMBER_INT);
        // set defaults
        $files = false;
        $files_showing = false;
        // GET all the files and folders
        $sql = <<< SQL
        SELECT
            `upload_date`,
            `user_id`,
            `real_name`,
            `id`,
            `hash__name`,
            `extension__id`,
            `type`
        FROM
            `files`
        WHERE
            `files`.`user_id` = ? AND `files`.`parent_folder__id` = ?;
        SQL;
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare($sql)) {

            /* bind parameters for markers */
            $stmt->bind_param("ii", $user_id, $parent_folder_id);

            /* execute query */
            $stmt->execute();

            /* bind result variables */
            $stmt->bind_result($upload_date, $user_idDB, $real_name, $id, $hash__name, $extension__id, $type);

            /* fetch values */
            while ($stmt->fetch()) {
                $files[] = array(
                    'upload_date' => htmlentities($upload_date, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'user_id' => htmlentities($user_idDB, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'real_name' => htmlentities($real_name, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'id' => htmlentities($id, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'hash__name' => htmlentities($hash__name, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'extension__id' => htmlentities($extension__id, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'type' => htmlentities($type, ENT_QUOTES | ENT_IGNORE, "UTF-8")
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
            print 'Cannot prepare SQL @ Opencloud__Db_Get_files' . '<br>';
            print 'user_id:' . htmlspecialchars($user_id) . '<br>';
            print 'getID:' . htmlspecialchars($getID) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }
        if ($files_showing) {
            return $files;
        } else {
            return false;
        }
    }
}

if (!function_exists('Opencloud__Db_Get_file')) {
    /**
     * Returns array of file
     * 
     * @param mysqli $mysqli Object which represents the connection to a MySQL Server.
     * @param int $user_id User ID.
     * @param int $file__id File ID.
     * 
     * @return array|false Array of file
     */
    function Opencloud__Db_Get_file($mysqli, $user_id, $file__id)
    {
        // filter input
        $user_id = filter_var(trim($user_id), FILTER_SANITIZE_NUMBER_INT);
        $file__id = filter_var(trim($file__id), FILTER_SANITIZE_NUMBER_INT);
        // set defaults
        $file = false;
        $file_showing = false;
        // GET all the files and folders
        $sql = <<< SQL
        SELECT
            `upload_date`,
            `real_name`,
            `hash__name`,
            `extensions`.`type`
        FROM
            `files`
        INNER JOIN `extensions` ON `files`.`extension__id` = `extensions`.`id`
        WHERE
            `files`.`user_id` = ? AND `files`.`id` = ? AND `files`.`type` = 1
        LIMIT 1;
        SQL;
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare($sql)) {
            /* bind parameters for markers */
            $stmt->bind_param("ii", $user_id, $file__id);
            /* execute query */
            $stmt->execute();
            /* bind result variables */
            $stmt->bind_result($upload_date, $real_name, $hash__name, $type);
            /* fetch values */
            $stmt->fetch();
            if ($upload_date) {
                $file = array(
                    'upload_date' => htmlentities($upload_date, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'real_name' => htmlentities($real_name, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'hash__name' => htmlentities($hash__name, ENT_QUOTES | ENT_IGNORE, "UTF-8"),
                    'type' => htmlentities($type, ENT_QUOTES | ENT_IGNORE, "UTF-8")
                );
                $file_showing = true;
            } else {
                $file['error_text'] = 'There are '
                    . htmlentities($stmt->num_rows, ENT_QUOTES | ENT_IGNORE, "UTF-8") .
                    ' files [user_id:'
                    . htmlentities($user_id, ENT_QUOTES | ENT_IGNORE, "UTF-8") .
                    ']';
            }
            /* close statement */
            $stmt->close();
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__Db_Get_files' . '<br>';
            print 'user_id:' . htmlspecialchars($user_id) . '<br>';
            print 'getID:' . htmlspecialchars($getID) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }
        if ($file_showing) {
            return $file;
        } else {
            return false;
        }
    }
}

if (!function_exists('Opencloud__Db_put_file')) {
    /**
     * Uploads list of files
     * 
     * @param mysqli $mysqli Object which represents the connection to a MySQL Server.
     * @param string $hash__name File name hash.
     * @param string $hash__file File hash.
     * @param int $user_id User ID.
     * @param string $real_name File name.
     * @param int $extension__id Extension ID.
     * @param int $status__id File status: existing|removed.
     * @param int $size File size.
     * @param int $parent_folder_id Parent folder ID.
     * 
     * @return bool File is uploaded
     */
    function Opencloud__Db_put_file($mysqli, $hash__name, $hash__file, $user_id, $real_name, $extension__id, $status__id, $size, $parent_folder__id)
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
        $file_uploaded = false;
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

            $file_uploaded = true;
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__Db_put_file' . '<br>';
            print 'hash__name:' . htmlspecialchars($hash__name) . '<br>';
            print 'hash__file:' . htmlspecialchars($hash__file) . '<br>';
            print 'user_id:' . htmlspecialchars($user_id) . '<br>';
            print 'real_name:' . htmlspecialchars($real_name) . '<br>';
            print 'extension__id:' . htmlspecialchars($extension__id) . '<br>';
            print 'status__id:' . htmlspecialchars($status__id) . '<br>';
            print 'size:' . htmlspecialchars($size) . '<br>';
            print 'parent_folder__id:' . htmlspecialchars($parent_folder__id) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return $file_uploaded;
    }
}

if (!function_exists('Opencloud__Db_get_extension_id')) {
    /**
     * Returns extension ID form DB
     * 
     * @param mysqli $mysqli Object which represents the connection to a MySQL Server.
     * @param string $extension__string Extenstion. Example: image/jpeg.
     * 
     * @return int|bool Extension ID form DB
     */
    function Opencloud__Db_get_extension_id($mysqli, $extension__string)
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
                Opencloud__Db_put_extension($mysqli, $extension__string);
                // Get an ID again
                return Opencloud__Db_get_extension_id($mysqli, $extension__string);
            }
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__Db_get_extension_id' . '<br>';
            print 'extension__string:' . htmlspecialchars($extension__string) . '<br>';
            // print 'mysqli->error:' . $mysqli->error . '<br>';
            print '<hr></pre>';
        }

        return false;
    }
}

if (!function_exists('Opencloud__Db_put_extension')) {
    function Opencloud__Db_put_extension($mysqli, $extension__string)
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
            print 'Cannot prepare SQL @ Opencloud__Db_put_extension' . '<br>';
            print 'extension__string:' . htmlspecialchars($extension__string) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return $flag;
    }
}

if (!function_exists('Opencloud__Db_put_folder')) {
    /**
     * TODO: check file`s hashes, not names
     */
    function Opencloud__Db_put_folder($mysqli, $add_folder__name,    $add_folder__user_id, $parent_folder__id)
    {
        $answer = false;
        $add_folder__name = filter_var(trim($add_folder__name), FILTER_SANITIZE_STRING);
        $parent_folder__id = filter_var(trim($parent_folder__id), FILTER_SANITIZE_NUMBER_INT);
        $add_folder__user_id = filter_var(trim($add_folder__user_id), FILTER_SANITIZE_NUMBER_INT);

        $sql = <<<SQL
            INSERT INTO `files`(
                `id`,
                `upload_date`,
                `user_id`,
                `parent_folder__id`,
                `real_name`,
                `type`
            )
            VALUES(NULL, NOW(), ?, ?, ?, 2);
        SQL;
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare($sql)) {
            /* bind parameters for markers */
            $stmt->bind_param("iis", $add_folder__user_id, $parent_folder__id, $add_folder__name);
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
            print 'Cannot prepare statement @ Opencloud__Db_put_folder' . '<br>';
            print 'parent_folder__id:' . htmlspecialchars($parent_folder__id) . '<br>';
            print 'add_folder__user_id:' . htmlspecialchars($add_folder__user_id) . '<br>';
            print 'add_folder__name:' . htmlspecialchars($add_folder__name) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return $answer;
    }
}

if (!function_exists('Opencloud__Db_get_extension_type')) {
    function Opencloud__Db_get_extension_type($mysqli, $extension__id)
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
            print 'Cannot prepare statement @ Opencloud__Db_get_extension_type' . '<br>';
            print 'extension__id:' . htmlspecialchars($extension__id) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return $return_extension__type;
    }
}

if (!function_exists('Opencloud__Db_get_filePathById')) {
    function Opencloud__Db_get_filePathById($mysqli, $remove_file__id)
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
            print 'Cannot prepare statement @ Opencloud__Db_get_filePathById' . '<br>';
            print 'remove_file__id:' . htmlspecialchars($remove_file__id) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return TARGET_DIR . $return_path;
    }
}

if (!function_exists('Opencloud__Db_delete_file')) {
    function Opencloud__Db_delete_file($mysqli, $remove_file__id)
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
            print 'Cannot prepare statement @ Opencloud__Db_delete_file' . '<br>';
            print 'remove_file__id:' . htmlspecialchars($remove_file__id) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return $flag;
    }
}

if (!function_exists('Opencloud__Db_login')) {
    function Opencloud__Db_login($mysqli, $username, $password)
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
                    setcookie(COOKIE__USER_PASSWORD, htmlspecialchars($passwordPOST), time() + 3600);
                    setcookie(COOKIE__USER_NAME, htmlspecialchars($usernamePOST), time() + 3600);
                    setcookie(COOKIE__USER_ID, htmlspecialchars($id), time() + 3600);

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
            $answer['text'] = 'Cannot prepare SQL @ Opencloud__Db_login';
        }
        return $answer;
    }
}

if (!function_exists('Opencloud__Db_check_login')) {
    function Opencloud__Db_check_login($mysqli)
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

            $logged_in__answer = Opencloud__Db_login($mysqli, $usernameCOOKIE, $passwordCOOKIE);
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


if (!function_exists('Opencloud__Db_rename')) {
    /**
     * Update Name
     * 
     * @param int $file__id File ID
     * @param string $file__name New File name
     * 
     * @return bool Was it renamed?
     */
    function Opencloud__Db_rename($mysqli, $file__id, $file__name)
    {
        if (!Opencloud__Db_check_login($mysqli)) {
            http_response_code(401);
            print 'You cannot rename files.';
            return false;
        }
        // filter input
        $file__id = filter_var(trim($file__id), FILTER_SANITIZE_NUMBER_INT);
        $file__name = filter_var(trim($file__name), FILTER_SANITIZE_STRING);
        $user__id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);
        // set defaults
        $file_renamed = false;
        /* create a prepared statement */
        $sql = 'UPDATE `files` SET `real_name` = ? WHERE `files`.`id` = ? AND `files`.`user_id` = ?;';
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind parameters (s = string, i = int, b = blob, etc)
            $stmt->bind_param('sii', $file__name, $file__id, $user__id);
            $stmt->execute();
            // Store the result so we can check if it exists in the database.
            $stmt->store_result();
            // if anything was updated
            if ($stmt->affected_rows > 0) {
                $file_renamed = true;
            }
            /* close statement */
            $stmt->close();
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__Db_rename' . '<br>';
            print 'file__id:' . htmlspecialchars($file__id) . '<br>';
            print 'file__name:' . htmlspecialchars($file__name) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return $file_renamed;
    }
}

if (!function_exists('Opencloud__Db_get_public_link')) {
    /**
     * Generates public link to download file
     * 
     * @param mysqli $mysqli Object which represents the connection to a MySQL Server.
     * @param int $file__id File ID
     * 
     * @return string|false Relative Link
     */
    function Opencloud__Db_get_public_link($mysqli, $file__id)
    {
        if (!Opencloud__Db_check_login($mysqli)) {
            http_response_code(401);
            print 'You cannot get a link.';
            return false;
        }
        // filter input
        $file__id = filter_var(trim($file__id), FILTER_SANITIZE_NUMBER_INT);
        $user__id = filter_input(INPUT_COOKIE, COOKIE__USER_ID, FILTER_SANITIZE_NUMBER_INT);
        $file__name = false;

        $sql = 'SELECT `real_name` FROM `files` WHERE `user_id` = ? AND `id` = ? LIMIT 1';
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind parameters (s = string, i = int, b = blob, etc)
            $stmt->bind_param('ii', $user__id, $file__id);
            $stmt->execute();
            /* bind result variables */
            $stmt->bind_result($file__name);
            $stmt->fetch();
            /* close statement */
            $stmt->close();
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__Db_get_public_link' . '<br>';
            print 'file__id:' . htmlspecialchars($file__id) . '<br>';
            print 'user__id:' . htmlspecialchars($user__id) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }
        if (!$file__name) {
            http_response_code(400);
            print 'Debug Info<hr><pre>';
            print 'Cannot find file @ Opencloud__Db_rename' . '<br>';
            print 'file__id:' . htmlspecialchars($file__id) . '<br>';
            print 'user__id:' . htmlspecialchars($user__id) . '<br>';
            print 'file__name:' . htmlspecialchars($file__name) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
            return false;
        }
        $public_link = hash('ripemd160', $file__name);
        /* create a prepared statement */
        /*
            PROCEDURE selectInsertPublicLink(
                IN file__id_in INT,
                IN user__id_in INT,
                IN link_in VARCHAR(255)
            )
            IF NOT EXISTS
                ( SELECT `id` FROM `public_links`
                WHERE
                    `public_links`.`file__id` = file__id_in AND `public_links`.`link` = link_in
            ) THEN
            INSERT INTO `public_links`(`id`, `link`, `file__id`)
            VALUES(NULL, link_in, file__id_in) ;
         */
        $sql = 'CALL selectInsertPublicLink(?, ?, ?);';
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind parameters (s = string, i = int, b = blob, etc)
            $stmt->bind_param('iis', $file__id, $user__id, $public_link);
            $stmt->execute();
            /* close statement */
            $stmt->close();
        } else {
            print 'Debug Info<hr><pre>';
            print 'Cannot prepare SQL @ Opencloud__Db_rename' . '<br>';
            print 'file__id:' . htmlspecialchars($file__id) . '<br>';
            print 'file__name:' . htmlspecialchars($file__name) . '<br>';
            print 'mysqli->error:' . htmlspecialchars($mysqli->error) . '<br>';
            print '<hr></pre>';
        }

        return $public_link;
    }
}


if (!function_exists('Opencloud__Db_Get_Public_file')) {
    /**
     * Returns one file
     * 
     * @param mysqli $mysqli Object which represents the connection to a MySQL Server.
     * @param string $public_link Public link.
     * 
     * @return array|false File
     */
    function Opencloud__Db_Get_Public_file($mysqli, $public_link)
    {
        // filter input
        $public_link = filter_var(trim($public_link), FILTER_SANITIZE_STRING);
        // set defaults
        $answer = false;

        $sql = <<<SQL
            SELECT
                `files`.`real_name`,
                `files`.`hash__name`,
                `extensions`.`type`
            FROM
                `files`
            INNER JOIN `extensions` ON `files`.`extension__id` = `extensions`.`id`
            INNER JOIN `public_links` ON `files`.`id` = `public_links`.`file__id`
            WHERE
                `public_links`.`link` = ?
            LIMIT 1;
        SQL;

        /* create a prepared statement */
        if ($stmt = $mysqli->prepare($sql)) {
            /* bind parameters for markers */
            $stmt->bind_param("s", $public_link);
            /* execute query */
            $stmt->execute();
            /* bind result variables */
            $stmt->bind_result($real_name, $hash__name, $type);

            /* fetch values */
            $stmt->fetch();
            if ($real_name) {
                http_response_code(200);
                $answer = array(
                    'real_name' => $real_name,
                    'hash__name' => $hash__name,
                    'type' => $type
                );
            } else {
                http_response_code(400);
            }
            /* close statement */
            $stmt->close();
        }
        return $answer;
    }
}