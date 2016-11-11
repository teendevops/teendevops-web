<?php
include "config.php";

function getConnection() {
    return new mysqli(HOST, USER, PASSWORD, DATABASE);
}

sec_session_start();

function isSignedIn() {
    return isset($_SESSION['signed_in']) && $_SESSION['signed_in'];
}

function sec_session_start() {
    $session_name = SESSION_ID_NAME;
    $secure = false; // true if https
    $httponly = true;
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
                              $cookieParams["path"],
                              $cookieParams["domain"],
                             $secure,
                             $httponly);
    session_name($session_name);
    
    session_start();
    
    if(!isset($_SESSION['csrf']))
        generateCSRFToken();
}

function register($username, $email, $password) {
    $mysqli = getConnection();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `banned`) VALUES (NULL, ?, ?, ?, ?, 'false')");
    $stmt->bind_param('ssss', $username, $password_hash, $username, $email);
    $stmt->execute();
        
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `username`=? OR `email`=?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_n, $username_n, $password_n, $email_n, $name_n, $banned_n);
    $stmt->fetch();
    
    $stmt = $mysqli->prepare("INSERT INTO `settings` (`id`, `description`, `languages`, `location`) VALUES (?, 'Write something about yourself here...', 'None', 'cat location > /dev/null')");
    $stmt->bind_param('i', $id_n);
    $stmt->execute(); // insert row into settings
}

function getUser($id) {
    $mysqli = getConnection();
    
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id`=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_n, $username_n, $password_n, $email_n, $name_n, $banned_n);
    $stmt->fetch();
    
    $user = getSettings($id);
    $user['id'] = "-1";
    $user['username'] = "404page";
    $user['email'] = "404@page.com";
    $user['name'] = "404page";
    $user['banned'] = "false";
    
    $user['id'] = $id_n;
    $user['username'] = $username_n;
    $user['email'] = $email_n;
    $user['name'] = $name_n;
    $user['banned'] = $banned_n;
    
    return $user;
}

function getSettings($id_real) {
    $arr = array();
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("SELECT * FROM `settings` WHERE `id`=?");
    $stmt->bind_param('i', $id_real);
    $stmt->execute();
    
    $stmt->store_result();
    $stmt->bind_result($id, $description, $languages, $location);
    while ($stmt->fetch()) {
        $arr['id'] = $id;
        $arr['description'] = $description;
        $arr['languages'] = $languages;
        $arr['location'] = $location;
        
        return $arr;
    }
    
    $arr['id'] = "-1";
    $arr['description'] = "A nonexistant user you wish existed";
    $arr['languages'] = "Wishful";
    $arr['location'] = "Internet";
    
    return $arr;
}

function setSettings($id, $description, $languages, $location) {
    $mysqli = getConnection();
    
    $stmt = $mysqli->prepare("UPDATE `settings` SET `description`=?, `languages`=?, `location`=? WHERE `id`=?");
    $stmt->bind_param('sssi', $description, $languages, $location, $id);
    $stmt->execute() or die("Error: Failed to save settings.");
}

function login($username_or_email, $password_real) {
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `username`=? OR `email`=?");
    $stmt->bind_param('ss', $username_or_email, $username_or_email) or die("Error: Failed to bind params first time");
    $stmt->execute() or die("Error: Failed to select user");
    
    $stmt->store_result();
    $stmt->bind_result($id, $username, $password, $email, $name, $banned) or die("Error: Failed to bind params first time");
    while ($stmt->fetch() ) {
        if(isBruteForcing($id, 10)) {
            return 4;
        } else if($banned == 'true') {
            return 2;
        } else {
            $success = password_verify($password_real, $password);
            loginAttempt($mysqli, $id, $success);
            
            if($success) {
                generateCSRFToken();// or die("Error: rip csrf token 2016");
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['html_username'] = htmlspecialchars($username);
                $_SESSION['email'] = $email;
                $_SESSION['html_email'] = htmlspecialchars($email);
                $_SESSION['banned'] = $banned;
                $_SESSION['name'] = $name;
                $_SESSION['signed_in'] = true;
                
                return 0;
            } else {
                return 1;
            }
        }
    }
    
    return 3;
}

function loginAttempt($mysqli, $id, $success) {
    $sc = ($success) ? 'true' : 'false';
    $forwarded = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== NULL) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : "undefined";
    $stmt = $mysqli->prepare("INSERT INTO `login_attempts` (`id`, `time`, `ip`, `insecure_ip`, `success`) VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?)") or die("Error: Failed to prepare statement @ login_attempts");
    $stmt->bind_param('isss', $id, $_SERVER['REMOTE_ADDR'], $forwarded, $sc) or die("Error: Failed to login bind param.");
    
    $stmt->execute() or die("Error: Failed to execute query. q=1 ");
}

function isBruteForcing($id, $tops) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT `time` FROM `login_attempts` WHERE `id`=? OR `ip`=? AND `time`>(NOW() - INTERVAL 1 HOUR) AND `success`='false'");
	$stmt->bind_param ('is', $id, $_SERVER['REMOTE_ADDR']);
	$stmt->execute() or die("Error: Failed to execute brute forcing query");
	$stmt->store_result();
	
	if ($stmt->num_rows > $tops)
		return true;
	return false;
}

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
            "channel"=>$channel,
            "message"=>$message,
            "deleted"=>$deleted,
            "message_id"=>$id_n
        );
    }
    
    return $arr;
}

function sendChat($username, $channel, $message) {
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
    $stmt = $mysqli->prepare("INSERT INTO `chat` (`username`, `timestamp`, `channel`, `message`, `deleted`, `id`) VALUES (?, CURRENT_TIMESTAMP, ?, ?, 'false', NULL)");
    $stmt->bind_param('sis', $username, $channel, $message);
    $stmt->execute(); // insert row into chat table
}

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
        );;
    }
    
    return $arr;
}

function getUsersByLanguage($languages) {
    $data = array();
    $arr = array();
    
    $mysqli = getConnection() or die("Error: Failed to get connection to MySQL database.");
	$stmt = $mysqli->prepare("SELECT `id` FROM `settings` WHERE `languages`=? LIMIT 20");
	$stmt->bind_param("s", $languages);
    $stmt->execute();
    
    $stmt->store_result();
    $stmt->bind_result($id);
    while ($stmt->fetch()) {
        $data[] = $id;
    }
    
    $sofar = "";
    foreach($data as $uid) {
        if($sofar == "")
            $sofar = "`id`='$uid'";
        else
            $sofar = $sofar . " OR `id`='$uid'";
    }
    
    if($sofar == "")
        return $arr;
    
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE $sofar" . ($sofar != "" ? "AND " : "") . "`banned`='false' LIMIT 20") or die("Error: Failed to prepare query.");
    $stmt->execute();
    
    $stmt->store_result();
    $stmt->bind_result($id, $username, $password, $name, $email, $banned);
    while ($stmt->fetch()) {
        $arr[] = array(
            "id"=>$id,
            "username"=>$username,
            "name"=>$name,
            "banned"=>$banned,
            "language"=>$languages
        );
    }
    
    return $arr;
}

function isLanguageValid($language) {
    $allowed = array('None', 'Java', 'C', 'C++', 'C#', 'Python', 'PHP', 'NodeJS', 'Scratch', 'Visual Basic', 'HTML/CSS/JS', 'Assembly', 'Ruby', 'Perl', 'Pascal', 'Scala', 'Lua', 'D', 'Swift', 'Objective-C', 'R', 'Go', 'SQL');
    return in_array($language, $allowed);
}

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

function gone($var) {
    return ($var == '' || $var == NULL) ? true : false;
}

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

function getCSRFToken() {
    if(!isset($_SESSION['csrf']) || $_SESSION['csrf'] == "")
        generateCSRFToken();
    return $_SESSION['csrf'];
}

function generateCSRFToken() {
    $_SESSION['csrf'] = md5(rand() . uniqid(rand(), true) . rand());
}

function usernameExists($username) {
    $mysqli = getConnection();
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE lower(`username`)=?");
    $stmt->bind_param('s', strtolower($username));
    $stmt->execute();
    $stmt->store_result();
    
    return $stmt->num_rows != 0;
}

function emailExists($email) {
    $stmt = getConnection()->prepare("SELECT * FROM `users` WHERE lower(`email`)=?");
    $stmt->bind_param('s', strtolower($email));
    $stmt->execute();
    $stmt->store_result();
    
    return $stmt->num_rows != 0;
}

function isPasswordSecure($password) {
    // DANGER: To protect your sanity, do not read the below string!
    $list = "123456\npassword\n12345678\nqwerty\n123456789\n12345\n1234\n111111\n1234567\ndragon\nasdfasdf\n123123\nbaseball\nabc123\nfootball\nmonkey\nletmein\n696969\nshadow\nmaster\n666666\nqwertyuiop\n123321\nmustang\n1234567890\nmichael\n654321\npussy\nsuperman\n1qaz2wsx\n7777777\nfuckyou\n121212\n000000\nqazwsx\n123qwe\nkiller\ntrustno1\njordan\njennifer\nzxcvbnm\nasdfgh\nhunter\nbuster\nsoccer\nharley\nbatman\nandrew\ntigger\nsunshine\niloveyou\nfuckme\n2000\ncharlie\nrobert\nthomas\nhockey\nranger\ndaniel\nstarwars\nklaster\n112233\ngeorge\nasshole\ncomputer\nmichelle\njessica\npepper\n1111\nzxcvbn\n555555\n11111111\n131313\nfreedom\n777777\npass\nfuck\nmaggie\n159753\naaaaaa\nginger\nprincess\njoshua\ncheese\namanda\nsummer\nlove\nashley\n6969\nnicole\nchelsea\nbiteme\nmatthew\naccess\nyankees\n987654321\ndallas\naustin\nthunder\ntaylor\nmatrix";
    
    return !(strpos($list, $password) !== false);
}

function isEmailValid($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isUsernameValid($str) {
    return preg_match('/^[a-zA-Z0-9_]+$/',$str);
}

function showSimilar() {
    if(isSignedIn()) {
        echo "<center><h1>Meet other devs...<h1></center>";
        echo "<div class=\"container\">
  <div class=\"row\">";
  
        $settings = getSettings($_SESSION['id']);
        $array = getUsersByLanguage($settings['languages']);
        
        foreach($array as $usr) {
            echo "          <div class=\"col-sm-3\"><center>
                                <img src=\"assets/user-icons/default.png\" id=\"icon-front\">
                                <h3>" . $settings['languages'] . " Developer</h3>
                            </center></div>";
            echo "          <div class=\"col-sm-3\">
                                <center><h2><a href=\"profile.php?id=" . $usr['id'] . "\">" . htmlspecialchars($usr['username']) . "</a></h2>
                                This is a sample text description of the user. Later, when I join the `settings` and `users` tables, I'll be able to implement this and make it actually display the real description. For now, however, this pointless paragraph will remain riiiiiight here.
                            </div></center>";
        }
        
        echo "</div></div>";
    }
}
?>
