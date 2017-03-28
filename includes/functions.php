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
