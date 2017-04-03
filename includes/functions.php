<?php
include "config.php";
include_once "strings.php";

sec_session_start();

/* returns a new mysqli object */
function getConnection() {
    return new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
}

/* returns a boolean; whether or not the user is signed in */
function isSignedIn() {
    return isset($_SESSION['signed_in']) && $_SESSION['signed_in'];
}

/* securely starts a new session */
function sec_session_start() {
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"] + (120960), // two weeks = 120960
                              $cookieParams["path"],
                              $cookieParams["domain"],
                             HTTPS, // secure
                             true); // httponly
    session_name(SESSION_ID_NAME);

    session_start();

    if(gone($_SESSION['csrf']))
        generateCSRFToken();
}

/* returns the absolute url if possible */
function toAbsoluteURL($request) {
    return ((!gone(HTTPS) ? 'https' : 'http') .'://' . (!gone(SITE) ? SITE : (!gone($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (!gone($_SERVER['SERVER_NAME']) ?$_SERVER['SERVER_NAME'] : 'localhost'))) .(substr($request, 0, 1) !== '/' ? '/' : '') . $request);
}

/* registers a new user */
function register($username, $email, $password) { /* [column]*/
    $mysqli = getConnection();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `banned`, `verified`, `description`, `languages`, `location`, `rank`, `icon`) VALUES (NULL, ?, ?, ?, ?, 'false', 'false', 'Write something about yourself here...', 'None', 'cat location > /dev/null', '0', '/assets/user-icons/default.png')");
    $stmt->bind_param('ssss', $username, $password_hash, $username, $email);
    $stmt->execute();

    try {
        loginAttempt($mysqli, getUserByName($username)['id'], 'reg');
    } catch(Exception $e) {
        // ignore error
    }
}

/* returns an array with information about the given user */
function getUser($id, $emaild=true) { /* [column]*/
    $mysqli = getConnection();

    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id`=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_n, $username_n, $password_n, $name_n, $email_n, $banned_n, $verified_n, $description_n, $languages_n, $location_n, $rank_n, $icon_n);
    $stmt->fetch();

    $user = array();
    $user['id'] = $id_n;
    $user['username'] = $username_n;
    if($emaild)
        $user['email'] = $email_n;
    //$user['name'] = $name_n;
    $user['rank'] = intval($rank);
    $user['banned'] = boolval($banned_n);
    $user['verified'] = boolval($banned_n);
    $user['description'] = $description_n;
    $user['languages'] = $languages_n;
    $user['location'] = $location_n;
    $user['icon'] = profileImageExists($icon_n);

    return $user;
}

/* returns an array with information about the given user */
function getUserByName($id, $emaild=true) { /* [column]*/
    $mysqli = getConnection();

    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `username`=?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_n, $username_n, $password_n, $name_n, $email_n, $banned_n, $verified_n, $description_n, $languages_n, $location_n, $rank_n, $icon_n);
    $stmt->fetch();

    $user = array();
    $user['id'] = $id_n;
    $user['username'] = $username_n;
    if($emaild)
        $user['email'] = $email_n;
    //$user['name'] = $name_n;
    $user['rank'] = intval($rank_n);
    $user['banned'] = boolval($banned_n);
    $user['verified'] = boolval($verified_n);
    $user['description'] = $description_n;
    $user['languages'] = $languages_n;
    $user['location'] = $location_n;
    $user['icon'] = profileImageExists($icon_n);

    return $user;
}

/* returns an array with information about the given user */
function getUserByEmail($id, $emaild=true) { /* [column]*/
    $mysqli = getConnection();

    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `email`=?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_n, $username_n, $password_n, $name_n, $email_n, $banned_n, $verified_n, $description_n, $languages_n, $location_n, $rank_n, $icon_n);
    $stmt->fetch();

    $user = array();
    $user['id'] = $id_n;
    $user['username'] = $username_n;
    if($emaild)
        $user['email'] = $email_n;
    //$user['name'] = $name_n;
    $user['rank'] = intval($rank_n);
    $user['banned'] = boolval($banned_n);
    $user['verified'] = boolval($verified_n);
    $user['description'] = $description_n;
    $user['languages'] = $languages_n;
    $user['location'] = $location_n;
    $user['icon'] = profileImageExists($icon_n);

    return $user;
}

/* Check if profile icon exists or return the default one */
function profileImageExists($icon) {
    $default = '/assets/user-icons/default.png';
    return (!gone($icon) ? (file_exists($_SERVER['DOCUMENT_ROOT'] . $icon) ? $icon : $default) : $default);
}

/* obselete. see:getUser */
function getSettings($id_real) { // this function is obselete.
    return getUser($id_real);
}

/* sets the settings for a user. TODO: make an array instead of variables */
function setSettings($id, $description, $languages, $location) {
    $mysqli = getConnection();

    if(isSignedIn() && $id == $_SESSION['id']) {
        $_SESSION['description'] = $description;
        $_SESSION['html_description'] = htmlspecialchars($description);
        $_SESSION['languages'] = $languages;
        $_SESSION['html_languages'] = htmlspecialchars($languages);
        $_SESSION['language'] = $languages;
        $_SESSION['html_language'] = htmlspecialchars($languages);
        $_SESSION['location'] = $location;
        $_SESSION['html_location'] = $location;
    }

    $stmt = $mysqli->prepare("UPDATE `users` SET `description`=?, `languages`=?, `location`=? WHERE `id`=?");
    $stmt->bind_param('sssi', $description, $languages, $location, $id);
    $stmt->execute() or die("Error: Failed to save settings.");
}

/* sets the url to the profile image */
function setProfileImage($id, $url) {
    $mysqli = getConnection();

    $stmt = $mysqli->prepare("UPDATE `users` SET `icon`=? WHERE `id`=?");
    $stmt->bind_param('si', $url, $id);
    $stmt->execute() or die("Error: Failed to save settings.");
}

/* sets a user to banned or not */
function setBanned($id, $banned) {
    $mysqli = getConnection();

    $stmt = $mysqli->prepare("UPDATE `users` SET `banned`=? WHERE `id`=?");
    $stmt->bind_param('si', $banned, $id);
    $stmt->execute() or die("Error: Failed to save settings.");
}

/* sets a user's rank' */
function setRank($id, $rank) {
    $mysqli = getConnection();

    $stmt = $mysqli->prepare("UPDATE `users` SET `rank`=? WHERE `id`=?");
    $stmt->bind_param('ii', $rank, $id);
    $stmt->execute() or die("Error: Failed to save settings.");
}

/* logs in the user */
function login($username_or_email, $password_real, $skip = false) { /* [column]*/
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `username`=? OR `email`=?");
    $stmt->bind_param('ss', $username_or_email, $username_or_email) or die("Error: Failed to bind params first time");
    $stmt->execute() or die("Error: Failed to select user");

    $stmt->store_result();
    $stmt->bind_result($id, $username, $password, $email, $name, $banned, $verified, $description, $languages, $location, $rank, $icon);
    while ($stmt->fetch() ) {
        if(isBruteForcing($id, MAX_LOGIN_ATTEMPTS)) {
            return 4; // brute forcing warning
        } else if($banned == 'true') {
            return 2; // yo banned!
        } else if($verified == 'false') {
            $_SESSION['id_unverified'] = $id;
            return 5;
        } else {
            $success = password_verify($password_real, $password); // TODO: timing attack via $skip?
            if(!$skip)
                loginAttempt($mysqli, $id, $success); // store the login attempt

            if($success || $skip) {
                session_regenerate_id(true);
                sec_session_start();

                generateCSRFToken();
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['html_username'] = htmlspecialchars($username);
                $_SESSION['email'] = $email;
                $_SESSION['html_email'] = htmlspecialchars($email);
                $_SESSION['rank'] = $rank;
                $_SESSION['rank_html'] = htmlspecialchars(getRank($rank)); // later refactor this variable... rename.
                $_SESSION['banned'] = $banned;
                $_SESSION['name'] = $name;
                $_SESSION['signed_in'] = true;
                $_SESSION['description'] = $description;
                $_SESSION['html_description'] = htmlspecialchars($description);
                $_SESSION['languages'] = $languages;
                $_SESSION['html_languages'] = htmlspecialchars($languages);
                $_SESSION['language'] = $languages;
                $_SESSION['html_language'] = htmlspecialchars($languages);
                $_SESSION['location'] = $location;
                $_SESSION['html_location'] = htmlspecialchars($location);
                $_SESSION['icon'] = profileImageExists($icon);

                return 0; // success
            } else {
                return 1; // failure
            }
        }
    }

    return 3; // unknown error
}

/* log api request */
function logAPI($endpoint) {
    $ip = $_SERVER['REMOTE_ADDR'];
    // token parameter below is for if someone is abusing the api, the admins can block something unique about the abuser if they are spoofing the ip.
    // the admins will periodically delete everything in the token column to save space, as it only has one purpose.
    $token = (isSignedIn() ? $_SESSION['id'] : (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : base64_encode($_SERVER['HTTP_USER_AGENT'])));

    $params_unencoded = $_REQUEST; // copy array
    if(array_key_exists('password', $params_unencoded)) // remove sensitive information
        $params_unencoded['password'] = '[snip]';
    if(array_key_exists('confirm_password', $params_unencoded))
        $params_unencoded['confirm_password'] = '[snip]';
    if(array_key_exists('csrf', $params_unencoded))
        $params_unencoded['csrf'] = '[snip]';
    if(array_key_exists('teendevops_session', $params_unencoded))
        $params_unencoded['teendevops_session'] = '[snip]';
    $parameters = json_encode($params_unencoded); // encode array

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");

    $stmt = $mysqli->prepare("INSERT INTO `api` (`time`, `endpoint`, `ip`, `parameters`, `token`) VALUES (CURRENT_TIMESTAMP, ?, ?, ?, ?)");
    $stmt->bind_param('ssss', $endpoint, $ip, $parameters, $token);
    $stmt->execute();
}

/* check api log for application DOS attempts */
function checkAPILog($hours=1) { // limit is 600 per hour
    $ip = $_SERVER['REMOTE_ADDR'];

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");

    // yes, yes. It looks like sql injection, but $hours will NEVER take user input.
	$stmt = $mysqli->prepare("SELECT `endpoint` FROM `api` WHERE `time`>(NOW() - INTERVAL " . $hours . " HOUR) AND `ip`=?"); // maybe check if IP is correct? idk
	$stmt->bind_param ('s', $ip);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows >= (600 * $hours))
		return false; // not good, requests going fast
	return true; // ok, we're good.
}

/* write unsuccess message to array if app dos */
function checkAPIRate() {
    if(!checkAPILog()) {
        $json = array();

        $json['success'] = false;
        $json['error'] = 'You have hit the API rate limit. Contact info@teendevops.net to adjust this limit.';
        dump($json);
    }
}

/* generate a cryptographically secure token */
function generateSecureCrypto($length) {
    return bin2hex(openssl_random_pseudo_bytes($length));
}

/*
 * Token types:
 * 1 = password reset
 * 2 = email verification
 *
 */

/* insert a new token into the db */
function generateToken($id, $type) {
    invalidateToken($id, $type);

    $token = generateSecureCrypto(32);

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");

    $stmt = $mysqli->prepare("INSERT INTO `tokens` (`id`, `time`, `ip`, `type`, `token`) VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?)") or die("Error: Failed to prepare statement @ reset_token");
    $stmt->bind_param('isis', $id, $_SERVER['REMOTE_ADDR'], $type, $token) or die("Error: Failed to login bind param.");
    $stmt->execute() or die("Error: Failed to save token to database.");

    return $token;
}

/* checks if a token is valid */
function validToken($id, $type, $token) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT * FROM `tokens` WHERE `time`>(NOW() - INTERVAL 12 HOUR) AND `id`=? AND `type`=? AND `token`=?"); // maybe check if IP is correct? idk
	$stmt->bind_param ('iis', $id, $type, $token);
	$stmt->execute() or die("Error: Failed to execute reset password query");
	$stmt->store_result();

	if ($stmt->num_rows != 0)
		return true;
	return false;
}

/* invalidates tokens */
function invalidateToken($id, $type) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("DELETE FROM `tokens` WHERE `id`=? AND `type`=?");
	$stmt->bind_param ('ii', $id, $type);
	$stmt->execute() or die("Error: Failed to execute delete reset password query");
}

/* sets a new password on an account */
function setPassword($id, $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
    $stmt = $mysqli->prepare("UPDATE `users` SET `password`=? WHERE `id`=?");
    $stmt->bind_param('si', $password_hash, $id);
    $stmt->execute() or die("Error: Failed to set password.");
}

/* set account to verified */
function setVerified($id, $verified) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
    $stmt = $mysqli->prepare("UPDATE `users` SET `verified`=? WHERE `id`=?");
    $stmt->bind_param('si', $verified, $id);
    $stmt->execute() or die("Error: Failed to set verified.");
}

/* send email */
function sendEmail($to, $fromname, $from, $subject, $body) {
    $headers  = "Reply-To: " . $from . " \r\n";
    $headers .= "Return-Path: " . $from . " \r\n";
    $headers .= "From: \"" . $fromname ."\" <" . $from ."> \r\n";
    $headers .= "Organization: " . $fromname . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $headers .= "Content-Transfer-Encoding: binary";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: PHP" . phpversion() . "\r\n";

    if(mail($to, $subject, $body, $headers))
        return true;
    return false;
}

/* convert rank to string */
function getRank($rank) {
    if($rank == 0)
        return 'User';
    if($rank == 1)
        return 'Moderator';
    if($rank == 2)
        return 'Admin';

    return 'User';
}

/* store the login attempt */
function loginAttempt($mysqli, $id, $success) {
    $sc = ($success) ? 'true' : 'false';
    $forwarded = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== NULL) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : "undefined";
    $stmt = $mysqli->prepare("INSERT INTO `login_attempts` (`id`, `time`, `ip`, `insecure_ip`, `success`) VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?)") or die("Error: Failed to prepare statement @ login_attempts");
    $stmt->bind_param('isss', $id, $_SERVER['REMOTE_ADDR'], $forwarded, $sc) or die("Error: Failed to login bind param.");

    $stmt->execute() or die("Error: Failed to execute query");
}

/* for the admin panel: prints the recent login attempts */
function printLoginAttempts($limit, $filter) { // warning: filter does not sanitize input; do not set it to user supplied input.
    $mysqli = getConnection();
	$stmt = $mysqli->prepare("SELECT login_attempts.*, users.username FROM `login_attempts` INNER JOIN `users` ON login_attempts.id=users.id" . $filter . " LIMIT " . $limit); // ik, sql injection. No user-supplied input tho.
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($id, $time, $ip, $insecure_ip, $success, $username);
    echo '<div class="container"><table class="table table-striped"><thead><tr>
            <th>Time</th>
            <th>ID</th>
            <th>Success</th>
            <th>IP</th>
            <th>Insecure IP</th>
          </tr></thead><tbody>';
    while ($stmt->fetch() ) {
        echo '
        <tr ' . ($success == 'true' ? 'style="background-color:#11CE00;"' : 'style="background-color:#FF0000;"') .'>
            <td>' . htmlspecialchars($time) . '</td>
            <td><a href="/profile/' . htmlspecialchars($username) . '/">' . htmlspecialchars($username) . '</a></td>
            <td><a href="/admin/attempts/?filter=true">' . htmlspecialchars($success) . '</a></td>
            <td>' . htmlspecialchars($ip) . '</td>
            <td>' . htmlspecialchars($insecure_ip) . '</td>
        </tr>
        ';
    }
    echo '</tbody></table></div>';
}

/* returns a boolean; whether or not a user is being brute forced */
function isBruteForcing($id, $tops) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT * FROM `login_attempts` WHERE `time`>(NOW() - INTERVAL 1 HOUR) AND `success`='false' AND `id`=? OR `ip`=? AND `success`='false'");
	$stmt->bind_param ('is', $id, $_SERVER['REMOTE_ADDR']);
	$stmt->execute() or die("Error: Failed to execute brute forcing query");
	$stmt->store_result();

	if ($stmt->num_rows > $tops)
		return true;
	return false;
}

/* returns a portion of the chat */
function getChat($id, $limit, $deleted) {
    $arr = array();

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT * FROM `chat` WHERE `channel`=? AND `deleted`=? LIMIT ?");
    $stmt->bind_param('ssi', $id, $deleted, $limit);
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($username, $timestamp, $channel, $message, $deleted, $id_n);
    while ($stmt->fetch()) {
        $arr[] = array(
            "username"=>$username,
            "timestamp"=>$timestamp,
            "channel"=>intval($channel),
            "message"=>$message,
            "deleted"=>boolval($deleted),
            "message_id"=>intval($id_n)
        );
    }

    return $arr;
}

/* returns a portion of the chat starting at index */
function getChatByIndex($id, $index, $limit, $deleted) {
    $arr = array();

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT * FROM `chat` WHERE `channel`=? AND `deleted`=? AND `id`>? LIMIT ?");
    $stmt->bind_param('isii', $id, $deleted, $index, $limit);
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($username, $timestamp, $channel, $message, $deleted, $id_n);
    while ($stmt->fetch()) {
        $arr[] = array(
            "username"=>$username,
            "timestamp"=>$timestamp,
            "channel"=>intval($channel),
            "message"=>$message,
            "deleted"=>boolval($deleted),
            "message_id"=>intval($id_n)
        );
    }

    return $arr;
}

/* returns an int; the number of messages in a channel */
function getChatMessageCount($channel) {
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("SELECT * FROM `chat` WHERE `channel`=?");
    $stmt->bind_param('i', $channel);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows;
}

/* returns an int; the latest message id */
function getChatLatestID($channel) {
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("SELECT * FROM `chat` WHERE `channel`=? ORDER BY `id` DESC LIMIT 1");
    $stmt->bind_param('i', $channel);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $timestamp, $channel, $message, $deleted, $id_n);
    $stmt->fetch();

    return $id_n;
}

/* sends a chat message to the given channel. TODO: use id rather than username */
function sendChat($username, $channel, $message) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
    $stmt = $mysqli->prepare("INSERT INTO `chat` (`username`, `timestamp`, `channel`, `message`, `deleted`, `id`) VALUES (?, CURRENT_TIMESTAMP, ?, ?, 'false', NULL)");
    $stmt->bind_param('sis', $username, $channel, $message);
    $stmt->execute(); // insert row into chat table
}

/* returns an array of channels */
function getChannels() {
    $arr = array();

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT * FROM `channels` WHERE `deleted`='false' LIMIT 1000");
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($id, $creator, $title, $description, $deleted);
    while ($stmt->fetch()) {
        $arr[] = array(
            "id"=>$id,
            "title"=>$title,
            "description"=>$description,
            "creator"=>$creator,
        );
    }

    return $arr;
}

/* returns a list of users who program in a language */
function getUsersByLanguage($language) { /* [column]*/
    $arr = array();

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");

    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `languages`=? AND `banned`='false' LIMIT 20") or die("Error: Failed to prepare query.");
	$stmt->bind_param("s", $language);
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($id, $username, $password, $name, $email, $banned, $verified, $description, $languages, $location, $rank, $icon);
    while ($stmt->fetch()) {
        $arr[] = array(
            "id"=>$id,
            "username"=>$username,
            "name"=>$name,
            "banned"=>boolval($banned),
            "verified"=>boolval($verified),
            "description"=>$description,
            "location"=>$location,
            "languages"=>$languages,
            "rank"=>intval($rank),
            "icon"=>profileImageExists($icon)
        );
    }

    return $arr;
}

/* Returns the past 500 users. TODO: Make function more flexible and have more features */
function getUsers() { /* [column]*/
    $arr = array();

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");

    $stmt = $mysqli->prepare("SELECT * FROM `users` LIMIT 500") or die("Error: Failed to prepare query.");
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($id, $username, $password, $name, $email, $banned, $verified, $description, $languages, $location, $rank, $icon);
    while ($stmt->fetch()) {
        $arr[] = array(
            "id"=>$id,
            "username"=>$username,
            "name"=>$name,
            "banned"=>boolval($banned),
            "verified"=>boolval($verified),
            "description"=>$description,
            "location"=>$location,
            "languages"=>$languages,
            "rank"=>intval($rank),
            "icon"=>profileImageExists($icon)
        );
    }

    return $arr;
}

/* returns a boolean; whether or not a language is a valid one */
function isLanguageValid($language) {
    $allowed = array('None', 'Java', 'C', 'C++', 'C#', 'Python', 'PHP', 'NodeJS', 'Scratch', 'Visual Basic', 'HTML/CSS/JS', 'Assembly', 'Ruby', 'Perl', 'Pascal', 'Scala', 'Lua', 'D', 'Swift', 'Objective-C', 'R', 'Go', 'SQL');
    return in_array($language, $allowed);
}

/* returns whether or not a channel is existant by id */
function isChannelExistant($id) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT * FROM `channels` WHERE `deleted`='false' AND `id`=? LIMIT 1000");
	$stmt->bind_param('i', $id);
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($id_n, $creator, $title, $description, $deleted);

    while ($stmt->fetch()) {
        if($id_n == $id)
            return true;
        else
            return false;
    }

    return false;
}

/* returns a boolean; whether or not a string has content */
function gone($var) {
    return (!isset($var) || $var == '' || $var == NULL) ? true : false;
}

/* logs the user out */
function logout() { // logout
    session_destroy(); // destroy session
    $_SESSION = array(); // overwrite variables

    $params = session_get_cookie_params();
    setcookie(session_name(), // remove session cookie
            '', time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]);
} // and... tada!

/* returns the CSRF token as a string */
function getCSRFToken() {
    if(!isset($_SESSION['csrf']) || $_SESSION['csrf'] == "")
        generateCSRFToken();
    return $_SESSION['csrf'];
}

/* generates a CSRF token */
function generateCSRFToken() {
    $_SESSION['csrf'] = generateSecureCrypto(32);
}

/* checks if a CSRF token is valid and returns true or false */
function checkCSRFToken($csrf) {
    return $csrf == getCSRFToken();
}

/* prints the input for the CSRF token */
function printCSRFToken() {
    return '<input type="hidden" id="csrf" name="csrf" value="' . getCSRFToken() . '">';
}

/* returns a boolean; whether or not the username is taken */
function usernameExists($username) {
    $mysqli = getConnection();/* [column]*/
    $username = strtolower($username);
    $stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE lower(`username`)=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows != 0;
}

/* checks if the email is taken */
function emailExists($email) {
    $email = strtolower($email);/* [column]*/
    $stmt = getConnection()->prepare("SELECT * FROM `users` WHERE lower(`email`)=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows != 0;
}

/* returns a boolean; whether or not a password is secure */
function isPasswordSecure($password) {
    // DANGER: To protect your sanity, do not read the below string!
    // it contains some of the most common passwords. There is some pretty cancerous stuff below!
    $list = "123456\npassword\n12345678\nqwerty\n123456789\n12345\n1234\n111111\n1234567\ndragon\nasdfasdf\n123123\nbaseball\nabc123\nfootball\nmonkey\nletmein\n696969\nshadow\nmaster\n666666\nqwertyuiop\n123321\nmustang\n1234567890\nmichael\n654321\npussy\nsuperman\n1qaz2wsx\n7777777\nfuckyou\n121212\n000000\nqazwsx\n123qwe\nkiller\ntrustno1\njordan\njennifer\nzxcvbnm\nasdfgh\nhunter\nbuster\nsoccer\nharley\nbatman\nandrew\ntigger\nsunshine\niloveyou\nfuckme\n2000\ncharlie\nrobert\nthomas\nhockey\nranger\ndaniel\nstarwars\nklaster\n112233\ngeorge\nasshole\ncomputer\nmichelle\njessica\npepper\n1111\nzxcvbn\n555555\n11111111\n131313\nfreedom\n777777\npass\nfuck\nmaggie\n159753\naaaaaa\nginger\nprincess\njoshua\ncheese\namanda\nsummer\nlove\nashley\n6969\nnicole\nchelsea\nbiteme\nmatthew\naccess\nyankees\n987654321\ndallas\naustin\nthunder\ntaylor\nmatrix\nincorrect";

    return !(strpos($list, $password) !== false);
}

/* returns a boolean; whether or not the given email is valid */
function isEmailValid($email) {
    // below list contains disposable email address domains
    $list = "0815.ru 0clickemail.com 0-mail.com 0wnd.net 0wnd.org 0x00.name 10host.top 10mail.org 10minut.com.pl 10minutemail.co.za 10minutemail.com 11top.xyz 123-m.com 12hosting.net 12storage.com 14n.co.uk 1-8.biz 1fsdfdsfsdf.tk 1pad.de 1rentcar.top 1ss.noip.me 20email.eu 20mail.eu 20mail.it 20minute.email 20minutemail.com 2120001.net 2fdgdfgdfgdf.tk 2prong.com 2sea.xyz 30minutemail.com 33mail.com 3d-painting.com 3l6.com 3trtretgfrfe.tk 4gfdsgfdgfd.tk 4-n.us 4w.io 4warding.com 4warding.net 4warding.org 54np.club 5ghgfhfghfgh.tk 5gramos.com 5july.org 5music.info 5music.top 60minutemail.com 675hosting.com 675hosting.net 675hosting.org 6hjgjhgkilkj.tk 6paq.com 6url.com 75hosting.com 75hosting.net 75hosting.org 7rent.top 7tags.com 80665.com 88clean.pro 9me.site 9ox.net a0f7ukc.com a41odgz7jh.com a54pd15op.com a-bc.net aaaw45e.com abyssemail.com abyssmail.com academiccommunity.com adesktop.com adobeccepdm.com adwaterandstir.com adx-telecom.com aegia.net aeonpsi.com afrobacon.com agedmail.com aistis.xyz ajaxapp.net akademiyauspexa.xyz akorde.al aldeyaa.ae aligamel.com alisongamel.com allthegoodnamesaretaken.org alph.wtf al-qaeda.us alsheim.no-ip.org ama-trade.de amazon-aws.org amilegit.com amiri.net amiriindustries.com amoksystems.com ampsylike.com an.id.au anappthat.com andthen.us animesos.com anonbox.net anonmails.de anonymbox.com ansibleemail.com anthony-junkmail.com anthropologycommunity.com antichef.com antichef.net antireg.ru antispam.de antispammail.de appinventor.nl armyspy.com aron.us artman-conception.com asdfghmail.com asspoo.com assurancespourmoi.eu astroempires.info augmentationtechnology.com autorobotica.com azjuggalos.com azmeil.tk b9x45v1m.com backalleybowling.info badgerland.eu badhus.org ballsofsteel.net bandai.nom.co bareed.ws barrypov.com barryspov.com bartoparcadecabinet.com baxomale.ht.cx beck-it.net beefmilk.com bho.hu bigstring.com bigwiki.xyz bin.8191.at binka.me binkmail.com biometicsliquidvitamins.com bio-muesli.net bitwerke.com blip.ch bluedumpling.info bluewerks.com bobmail.info bodhi.lawlita.com bofthew.com bogotadc.info bonobo.email bookthemmore.com bootybay.de boun.cr bouncr.com boximail.com breakthru.com brefmail.com broadbandninja.com bsnow.net bspamfree.org bspooky.com bugmenot.com bum.net bumpymail.com bunchofidiots.com bund.us bunsenhoneydew.com burstmail.info businesscredit.xyz buygapfashion.com buymoreplays.com buyordie.info bwa33.net by8006l.com byebyemail.com byespm.com byom.de c2.hu c51vsgq.com cafecar.xyz card.zp.ua carrnelpartners.com cartelera.org casualdx.com cek.pm centermail.com centermail.net central-servers.xyz chammy.info cheaphorde.com cheaphub.net chef.asana.biz chielo.com childsavetrust.org chilelinks.cl chinatov.com chogmail.com choicemail1.com chris.burgercentral.us christopherfretz.com cigar-auctions.com civilizationdesign.xyz cl.gl clandest.in clay.xyz clinicatbf.com clipmail.eu clixser.com clrmail.com cls-audio.club cloud99.pro cloud99.top cmail.net cmail.org cnamed.com codeandscotch.com cognitiveways.xyz coldemail.info communitybuildingworks.xyz comwest.de contentwanted.com cool.fr.nf coolandwacky.us coolimpool.org correo.blogos.net cortex.kicks-ass.net cosmorph.com courriel.fr.nf courrieltemporaire.com cr97mt49.com crankhole.com crapmail.org crastination.de crossroadsmail.com cubiclink.com cultmovie.com curryworld.de cust.in cuvox.de cybersex.com czqjii8.com d3p.dk d58pb91.com d8u.us dacoolest.com daemsteam.com dammexe.net dancemanual.com dandikmail.com darkharvestfilms.com darknode.org dash-pads.com dataarca.com datarca.com datazo.ca davidkoh.net dayrep.com dcemail.com deadaddress.com deadspam.com deekayen.us defomail.com degradedfun.net delikkt.de derder.net despam.it despammed.com devnullmail.com dfgh.net diapaulpainting.com digdown.xyz digitalmariachis.com digitalsanctuary.com dingbone.com dinkmail.com disario.info discardmail.com discardmail.de dispo.in disposableaddress.com disposableemailaddresses.com disposablemails.com disposableinbox.com dispose.it disposeamail.com disposemail.com dispostable.com divismail.ru dlemail.ru dm.w3internet.co.ukexample.com dodgeit.com dodgit.com dodgit.org dolphinnet.net doanart.com donemail.ru dontreg.com dontsendmespam.de doquier.tk dotslashrage.com douchelounge.com doxcity.net dqkerui.com dr69.site dragons-spirit.org drdrb.com drdrb.net dropmail.me drynic.com dspwebservices.com dt.com dukedish.com dumpandjunk.com dump-email.info dumpmail.de dumpyemail.com e4ward.com e7n06wz.com eastwan.net easytrashmail.com eatrnet.com eb609s25w.com eco.ilmale.it edrishn.xyz eelmail.com einmalmail.de einrot.com eintagsmail.de e-mail.com e-mail.org email60.com emaildienst.de emailgo.de emailias.com emailigo.de emailinfive.com emaillime.com emailmiser.com emailsensei.com emailsingularity.net emailtemporanea.com emailtemporanea.net emailtemporar.ro emailtemporario.com.br emailthe.net emailtmp.com emailto.de emailwarden.com emailx.at.hm emailxfer.com emeil.in emeil.ir emltmp.com emz.net enterto.com eonmech.com ephemail.net ero-tube.org etgdev.de etranquil.com etranquil.net etranquil.org evopo.com evyush.com exitstageleft.net explodemail.com express.net.ua extremail.ru eyepaste.com ezfill.club ezstest.com f4k.es failbone.com faithkills.org fakeinbox.com fakeinformation.com fangoh.com fansworldwide.de fantasymail.de fartwallet.com fastacura.com fastchevy.com fastchrysler.com fastkawasaki.com fastmazda.com fastmitsubishi.com fastnissan.com fastsubaru.com fastsuzuki.com fasttoyota.com fastyamaha.com faze.biz fc66998.com fetchnet.co.uk fightallspam.com figjs.com figshot.com filzmail.com fingermouse.org fishfortomorrow.xyz fivemail.de fizmail.com flashbox.5july.org fleckens.hu flemail.ru flowu.com flyinggeek.net foodbooto.com foquita.com forecastertests.com forspam.net forward.cat fr33mail.info francanet.com.br frapmail.com freebullets.net freecat.net freechristianbookstore.com freefattymovies.com freemommyvids.com freeplumpervideos.com freeschoolgirlvids.com freeshemaledvds.com freesistervids.com freetubearchive.com friendlymail.co.uk front14.org fsagc.xyz fuckedupload.com fuckingduh.com fudgerub.com fun2.biz furzauflunge.de fux0ringduh.com fyii.de gafy.net gamegregious.com gamgling.com garliclife.com garrymccooey.com gav0.com gehensiemirnichtaufdensack.de genderfuck.net gero.us get1mail.com get2mail.fr getairmail.com getmails.eu getonemail.com getonemail.net ghosttexter.de giantmail.de gibit.us girlsundertheinfluence.com gishpuppy.com giuras.club giuypaiw8.com globaltouron.com glucosegrin.com gmial.com godataflow.xyz godut.com goemailgo.com goodjab.club gotmail.net gotmail.org gotti.otherinbox.com gowikibooks.com gowikicampus.com gowikicars.com gowikifilms.com gowikigames.com gowikimusic.great-host.in grandmamail.com great-host.in greensloth.com greenst.info greggamel.net gregorygamel.com gregorygamel.net greyjack.com grr.la gsrv.co.uk guerillamail.biz guerillamail.com guerillamail.net guerillamail.org guerrillamail.biz guerrillamail.com guerrillamail.de guerrillamail.info guerrillamail.net guerrillamail.org guerrillamailblock.com gustr.com gwspt71.com h.mintemail.com h1z8ckvz.com h8s.org h9js8y6.com habitue.net hackrz.xyz haltospam.com happykorea.club happykoreas.xyz harakirimail.com harmonyst.xyz hatespam.org hat-geld.de hdmoviestore.us healyourself.xyz heathenhero.com helloricky.com herp.in herpderp.nl hiddencorner.xyz hidemail.de hidzz.com hmamail.com hoanggiaanh.com hochsitze.com hopemail.biz hostcalls.com hostmonitor.net hotpop.com housat.com hulapla.de hvtechnical.com i201zzf8x.com i4j0j3iz0.com iaoss.com icantbelieveineedtoexplainthisshit.com icemovie.link ieatspam.eu ieatspam.info ieh-mail.de ignoremail.com ihateyoualot.info ihaxyour.info iheartspam.org ikbenspamvrij.nl illistnoise.com ilnostrogrossograssomatrimoniomolisano.com ilovespam.com imails.info imankul.com imgof.com imovie.link inapplicable.org inbax.tk inbox.si inboxalias.com inboxclean.com inboxclean.org incognitomail.com incognitomail.net incognitomail.org infocom.zp.ua inpowiki.xyz insorg-mail.info instant-mail.de ip6.li ipoo.org ipswell.com ircbox.xyz irish2me.com irssi.tv ispuntheweb.com istakalisa.club iwi.net jafps.com jamit.com.au jdmadventures.com jellyrolls.com jeramywebb.com jetable.com jetable.fr.nf jetable.net jetable.org jetableemail.com jnxjn.com jobposts.net jobs-to-be-done.net joelpet.com joetestalot.com josefadventures.org jourrapide.com j-p.us jredm.com jsrsolutions.com jswfdb48z.com jungkamushukum.com junk1e.com jv6hgh1.com jwk4227ufn.com jyliananderik.com k3663a40w.com kah.pw kaijenwan.com kampoeng3d.club kasmail.com kaspop.com katztube.com kcrw.de keepmymail.com kekecog.com kennedy808.com ketiksms.club kickmark.com kiham.club killmail.com killmail.net kir.ch.tc kismail.ru kitten-mittons.com klassmaster.com klassmaster.net klzlk.com kommunity.biz kormail.xyz kosmetik-obatkuat.com koszmail.pl kuai909.com kuaijenwan.com kulturbetrieb.info kurzepost.de kwift.net kwilco.net lackmail.ru lakelivingstonrealestate.com laoeq.com lawlita.com l-c-a.us ledoktre.com leeching.net lesbugs.com letthemeatspam.com lexisense.com lhsdv.com lifebyfood.com ligsb.com likesyouback.com lillemap.net link2mail.net litedrop.com lmcudh4h.com localserv.no-ip.org locateme10.com locomodev.net lol.ovpn.to lolfreak.net lookugly.com lopl.co.cc lortemail.dk lostpositive.xyz lr78.com lroid.com lukop.dk m21.cc m2r60ff.com m4ilweb.info maboard.com macromaid.com magicbox.ro mail.aws910.com mail.by mail.illistnoise.com mail.mailinator.com mail.mezimages.net mail.partskyline.com mail.wtf mail.zp.ua mail1a.de mail21.cc mail2rss.org mail333.com mail4trash.com mail707.com mailback.com mailbidon.com mailbiz.biz mailblocks.com mailbox80.biz mailbucket.org mailcat.biz mailcatch.com mailde.de mailde.info maildrop.cc maildx.com maileater.com mailed.ro maileimer.de maileme101.com mailexpire.com mailfa.tk mail-filter.com mailforspam.com mailfreeonline.com mailguard.me mailin8r.com mailinater.com mailinator.com mailinator.net mailinator.org mailinator2.com mailincubator.com mailismagic.com mailkor.xyz mailme.ir mailme.lv mailme24.com mailmetrash.com mailmetrash.comilzilla.org mailmoat.com mailms.com mailna.me mailnator.com mailnesia.com mailnull.com mailorc.com mailorg.org mailpick.biz mailrock.biz mailscrap.com mailshell.com mailsiphon.com mailslite.com mailspeed.ru mailtemp.info mail-temporaire.fr mailtome.de mailtothis.com mailtrash.net mailtv.net mailtv.tv mailzilla.com mailzilla.org makemetheking.com malayalamdtp.com mansiondev.com manybrain.com markmurfin.com mastahype.net mattmason.xyz mbx.cc mcache.net medsheet.com mega.zik.dj meinspamschutz.de mejjang.xyz meltmail.com messagebeamer.de messwiththebestdielikethe.rest metroset.net mezimages.net mhwolf.net midcoastcustoms.com midcoastcustoms.net midcoastsolutions.com midcoastsolutions.net midlertidig.com midlertidig.net midlertidig.org mierdamail.com ministry-of-silly-walks.de mintemail.com miodonski.ch miraigames.net misterpinball.de mmailinater.com moakt.ws moburl.com mockmyid.co mohmal.com mohmal.im mohmal.tech momentics.ru moncourrier.fr.nf monemail.fr.nf monmail.fr.nf monumentmail.com moreorcs.com msa.minsmail.com msgos.com mspeciosa.com mswork.ru msxd.com mt2009.com mt2014.com mt2015.com mtmdev.com mufux.com mugglenet.org mustbedestroyed.org mvrht.com mwarner.org mx0.wwwnew.eu my10minutemail.com mycard.net.ua mycleaninbox.net mycorneroftheinter.net mydemo.equipment mymail-in.net myn4s.ddns.net mypacks.net mypartyclip.de myphantomemail.com mysamp.de myspaceinc.com myspaceinc.net myspaceinc.org myspacepimpedup.com myspamless.com mytempemail.com mytempmail.com mythnick.club mytrashmail.com myzx.com n1nja.org nabuma.com nakedtruth.biz nanonym.ch nctuiem.xyz neibu306.com neibu963.com neomailbox.com nepwk.com nervmich.net nervtmich.net netmails.com netmails.net netris.net netzidiot.de neverbox.com newdawnnm.xyz nextstopvalhalla.com nguyenusedcars.com nice-4u.com nincsmail.com nincsmail.hu niwl.net nl.szucsati.net nnh.com noblepioneer.com nobulk.com noclickemail.com nodnor.club nogmailspam.info nomail.ch nomail.pw nomail.xl.cx nomail2me.com nomailthankyou.com nomorespamemails.com norseforce.com northemquest.com no-spam.ws nospam.ze.tc nospam4.us nospamfor.us nospammail.net nospamthanks.info nostrajewellery.xyz nothingtoseehere.ca notmailinator.com nowhere.org nowmymail.com nubescontrol.com nurfuerspam.de nwldx.com ny7.me o060bgr3qg.com o7i.net objectmail.com obobbo.com obxpestcontrol.com oceancares.xyz odnorazovoe.ru oerpub.org offshore-proxies.net ohdomain.xyz ohioticketpayments.xyz omnievents.org onebiginbox.com onelegalplan.com oneoffemail.com onewaymail.com onlatedotcom.info online.ms oolus.com oopi.org opayq.com ordinaryamerican.net otherinbox.codupmyspace.com otherinbox.com ovpn.to owlpic.com pancakemail.com parkcrestlakewood.xyz pastebitch.com paulfucksallthebitches.com pcusers.otherinbox.com pencalc.xyz penis.computer petrzilka.net photomark.net pingir.com pjjkp.com plexolan.de pokemail.net politikerclub.de polyfaust.com pooae.com poofy.org pookmail.com poopiebutt.club popesodomy.com pp.ua premiumperson.website primabananen.net privacy.net privatdemail.net projectcl.com propscore.com proxymail.eu prs7.xyz prtnx.com psychedelicwarrior.xyz pumps-fashion.com punkass.com purelogistics.org putthisinyourspamdatabase.com pwp.lv pwrby.com q5vm7pi9.com qafatwallet.com qj97r73md7v5.com qs2k.com quadrafit.com querydirect.com quickinbox.com quickmail.nl quickreport.it r8r4p0cb.com radecoratingltd.com raetp9.com rainwaterstudios.org raketenmann.de rarame.club rawhidefc.org rawmails.com rcpt.at rcs7.xyz reallymymail.com realtyalerts.ca recipeforfailure.com recode.me reconmail.com recursor.net redpeanut.com regbypass.com regbypass.comsafe-mail.net rejectmail.com reliable-mail.com remote.li reptilegenetics.com revolvingdoorhoax.org rgphotos.net rhombushorizons.com rhyta.com riamof.club riddermark.de rklips.com rmqkr.net rnailinator.com ronnierage.net rootfest.net rotaniliam.com royal.net rppkn.com rq6668f.com rtrtr.com rumgel.com ruu.kr s0ny.net s33db0x.com s51zdw001.com safe-mail.net safersignup.de safetymail.info safetypost.de sandelf.de sausen.com saynotospams.com scbox.one.pl schachrol.com schafmail.de schrott-email.de sd3.in searzh.com secretemail.de secured-link.net secure-mail.biz selfdestructingmail.com sendfree.org sendingspecialflyers.com sendspamhere.com senseless-entertainment.com services391.com sexical.com sezet.com sharklasers.com shhmail.com shhuut.org shieldemail.com shiftmail.com shitmail.de shitmail.me shitware.nl shmeriously.com shonky.info shortmail.net shotmail.ru sibmail.com sinda.club sinnlos-mail.de skeefmail.com slapsfromlastnight.com slaskpost.se slipry.net slopsbox.com slothmail.net slowfoodfoothills.xyz slutty.horse smashmail.de smellfear.com smsforum.ro smwg.info snakemail.com sneakemail.com sneakmail.de snkmail.com sofimail.com sofort-mail.de sogetthis.com solvemail.info soodonims.com sosmanga.com spa.com spaerePlease.com spam.la spam.su spam4.me spamail.de spamarrest.com spamavert.com spambob.com spambob.net spambob.org spambog.com spambog.de spambog.ru spambooger.com spambox.info spambox.irishspringrealty.com spambox.us spamcannon.com spamcannon.net spamcero.com spamcon.org spamcorptastic.com spamcowboy.com spamcowboy.net spamcowboy.org spamday.com spamex.com spamfree.eu spamfree24.com spamfree24.de spamfree24.eu spamfree24.info spamfree24.net spamfree24.org spamgoes.in spamgourmet.com spamgourmet.net spamgourmet.org spamherelots.com spamhereplease.com spamhole.com spamify.com spaminator.de spamkill.info spaml.com spaml.de spammotel.com spamobox.com spamoff.de spamslicer.com spamspot.com spamthis.co.uk spamthisplease.com spamtrail.com spamtroll.net speed.1s.fr spoofmail.de spybox.de ssgjylc1013.com statdvr.com stathost.net steamprank.com stexsy.com stg.malibucoding.com stpetersandstpauls.xyz streamfly.biz streamfly.link streetwisemail.com stromox.com studiopolka.tokyo stuffmail.de suburbanthug.com super-auswahl.de supergreatmail.com supermailer.jp superrito.com superstachel.de suremail.info surveyrnonkey.net swift10minutemail.com sxylc113.com t24e4p7.com t3t97d1d.com tafmail.com tagmymedia.com takedowns.org talkinator.com tanukis.org taosjw.com taskforcetech.com tdf-illustration.com teewars.org teleosaurs.xyz teleworm.com teleworm.us tempalias.com tempemail.biz tempemail.co.za tempemail.com tempe-mail.com tempemail.net tempinbox.co.uk tempinbox.com tempmail.de temp-mail.de tempmail.eu tempmail.it temp-mail.org temp-mail.ru tempmail2.com tempmaildemo.com tempmailer.com tempmailer.de tempomail.fr temporarily.de temporarioemail.com.br temporaryemail.net temporaryforwarding.com temporaryinbox.com temporarymailaddress.com tempthe.net testudine.com thanksnospam.info thankyou2010.com thc.st theaperturelabs.com theaperturescience.com theaviors.com thebearshark.com thelimestones.com thespawningpool.com thietbivanphong.asia thisisnotmyrealemail.com thismail.net thraml.com thrma.com throwam.com throwawayemailaddress.com throwawaymail.com thxmate.com tilien.com timekr.xyz tittbit.in tizi.com tkmy88m.com tlpn.org tmail.ws tmailinator.com tmpjr.me tntitans.club toddsbighug.com tokuriders.club tonymanso.com toomail.biz top1mail.ru top1post.ru topofertasdehoy.com topranklist.de toprumours.com totesmail.com tp-qa-mail.com tradermail.info tranceversal.com trash2009.com trash-amil.com trashdevil.com trashemail.de trashmail.at trash-mail.at trashmail.com trash-mail.com trashmail.de trash-mail.de trashmail.me trashmail.net trashmail.org trashmail.ws trashmailer.com trashymail.com trashymail.net trbvm.com trbvn.com trendingtopic.cl trialmail.de trillianpro.com trungtamtoeic.com tryalert.com tucumcaritonite.com tug.minecraftrabbithole.com turoid.com turual.com twinmail.de txt7e99.com txtadvertise.com tyldd.com u6lvty2.com ua3jx7n0w3.com ufacturing.com ufgqgrid.xyz uggsrock.com uhhu.ru umail.net unimark.org upliftnow.com uplipht.com urbanchickencoop.com uroid.com us.af uwork4.us uz6tgwk.com valhalladev.com vanacken.xyz venompen.com verdejo.com veryrealemail.com viditag.com viewcastmediae vikingsonly.com vinernet.com viralplays.com vixletdev.com vkcode.ru vmani.com vmpanda.com vncoders.net vomoto.com votiputox.org vpn.st vpn33.top vps911.net vpsorg.pro vpsorg.top vs904a6.com vsimcard.com vubby.com w22fe21.com w4i3em6r.com w918bsq.com w9f.de w9y9640c.com walkmail.ru wasteland.rfc822.org wbml.net webemail.me webm4il.info webtrip.ch wegwerfadresse.de wegwerfemail.com wegwerfemail.de weg-werf-email.de wegwerf-emails.de wegwerfmail.de wegwerfmail.info wegwerfmail.net wegwerfmail.org wetrainbayarea.com wetrainbayarea.org wg0.com wh4f.org whatifanalytics.com whyspam.me wibblesmith.com wiki.8191.at willhackforfood.biz willselfdestruct.com wimsg.com winemaven.info wishan.net wolfmission.com wr9v6at7.com wronghead.com wuzup.net wuzupmail.net www.e4ward.com www.gishpuppy.com www.mailinator.com www.redpeanut.com wwwnew.eu wyvernia.net x.ip6.li x1x22716.com x24.com x5a9m8ugq.com x8h8x941l.com xagloo.com xcompress.com xemaps.com xents.com xjoi.com xlgaokao.com xmaily.com xn--9kq967o.com xoxy.net xwaretech.info xwaretech.net xxqx3802.com yaqp.com yentzscholarship.xyz yep.it ygroupvideoarchive.com ygroupvideoarchive.net ynmrealty.com yogamaven.com yomail.info yopmail.com yopmail.fr yopmail.net youcankeepit.info yourdomain.com yourewronghereswhy.com yourlms.biz ypmail.webarnak.fr.eu.org yroid.com yui.it yuurok.com yyj295r31.com z1p.biz z7az14m.com za.com zain.site zainmax.net zane.rocks zasod.com zasve.info zehnminuten.de zehnminutenmail.de zepp.dk zetmail.com zippymail.info zoaxe.com zoemail.net zoemail.org zombie-hive.com zomg.info zumpul.com zxcxc.com";
    if((strpos($list, substr(strrchr($email, "@"), 1)) !== false))
        return false;
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/* returns a boolean; whether or not the given username is valid */
function isUsernameValid($str) {
    return preg_match('/^[a-zA-Z0-9_]+$/',$str);
}

/* prints similar devs */
function showSimilar() {
    if(isSignedIn() && $_SESSION['languages'] != 'None') {
        $array = getUsersByLanguage($_SESSION['languages']);

        if(sizeof($array) - 1 > 0) {
            echo "<center><h1>Meet other devs...<h1></center><div class=\"container\"><div class=\"row\">";

            foreach($array as $usr) {
	            if($usr['id'] != $_SESSION['id']) {
                        echo "          <div class=\"col-sm-3\"><center>
                                            <img src=\"" . $usr['icon'] . "\" id=\"icon-front\">
                                            <h3>" . $_SESSION['html_languages'] . " Developer</h3>
                                        </center></div>";
                        echo "          <div class=\"col-sm-3\">
                                            <center><h2><a href=\"profile/" . htmlspecialchars($usr['username']) . "/\">" . htmlspecialchars($usr['username']) . "</a></h2>
                                            " . htmlspecialchars($usr['description']) . "
                                        </div></center>";
	            }
	        }

            echo "</div></div>";
        }
    }
}

/* Returns boolean-- whether or not a flag is taken */
function ctfTaken($id, $flag) {
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("SELECT * FROM `flags` WHERE `id`=? AND `flag`=?");
    $stmt->bind_param('is', $id, $flag);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows != 0;
}

/* Returns the claimed flags and worth by a user */
function ctfHistory($id) {
    $arr = array();

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");

    $stmt = $mysqli->prepare("SELECT * FROM `flags` WHERE `id`=?") or die("Error: Failed to prepare query.");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($id, $flag, $worth);
    while ($stmt->fetch()) {
        $arr[] = array(
            "id"=>$id,
            "flag"=>$flag,
            "worth"=>$worth
        );
    }

    return $arr;
}

/* Returns the balance in tokens of a user */
function ctfBalance($id) {
    $balance = 0;

    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");

    $stmt = $mysqli->prepare("SELECT * FROM `flags` WHERE `id`=?") or die("Error: Failed to prepare query.");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $stmt->store_result();
    $stmt->bind_result($id, $flag, $worth);
    while ($stmt->fetch()) {
        $balance = $balance + $worth;
    }

    return $balance;
}

/* Add row to flags */
function ctfFlag($id, $flag, $worth) {
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("INSERT INTO `flags` (`id`, `flag`, `worth`) VALUES (?, ?, ?)");
    $stmt->bind_param('isi', $id, $flag, $worth);
    $stmt->execute();
}

/* Dumps the data in an array in the format requested */
function dump($array) {
    if(!gone($_REQUEST['format'])) {
        $type = $_REQUEST['format'];
        if($type == 'dump') {
            header("Content-Type: text/plain");
            print_r($array); // phpdump
            die();
        } else if($type == 'xml') {
            header("Content-Type: application/xml");
            die(toXML($array));
        }
    }

    header("Content-Type: application/json");
    die(json_encode($array)); // json
}

/* converts array to xml */
function toXML($array) {
    $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");

    foreach($array as $key => $value) {
        if(is_array($value)) {
            $key = is_numeric($key) ? "item$key" : $key;
            $subnode = $xml->addChild("$key");
            array_to_xml($value, $subnode);
        }
        else {
            $key = is_numeric($key) ? "item$key" : $key;
            $xml->addChild("$key","$value");
        }
    }

    return $xml->asXML();
}

/* Validates session ids */
function session_valid_id($session_id) {
    return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $session_id) > 0;
}
?>
