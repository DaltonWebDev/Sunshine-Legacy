<?php
require "vendor/autoload.php";
if (true === false) {
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);
}
$dbCredentials = [
	"server" => "localhost",
	"username" => "dalton",
	"password" => "database",
	"database" => "sunshine"
];
$conn = new mysqli($dbCredentials["server"], $dbCredentials["username"], $dbCredentials["password"], $dbCredentials["database"]);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
$membersSql = "CREATE TABLE IF NOT EXISTS members (
	member_id VARCHAR(36) PRIMARY KEY, 
	username VARCHAR(20) NOT NULL UNIQUE,
	profile_name VARCHAR(100) DEFAULT NULL,
	profile_bio VARCHAR(200) DEFAULT NULL,
	profile_website VARCHAR(100) DEFAULT NULL,
	profile_picture VARCHAR(32) DEFAULT NULL,
	profile_header VARCHAR(32) DEFAULT NULL,
	verified TINYINT(1) DEFAULT 0,
	moderator TINYINT(1) DEFAULT 0,
	suspended TINYINT(1) DEFAULT 0,
	suspended_until DATETIME DEFAULT NULL,
	premium TINYINT(1) DEFAULT 0,
	premium_expiration DATETIME DEFAULT NULL,
	email VARCHAR(254) NOT NULL UNIQUE,
	email_verified TINYINT(1) DEFAULT 0,
	email_verification_token VARCHAR(32),
	password_hash VARCHAR(255) NOT NULL,
	api_key_hash VARCHAR(255) DEFAULT NULL,
	mood VARCHAR(50) DEFAULT NULL,
	birthday DATETIME,
	reg_date DATETIME,
	log_date DATETIME DEFAULT NULL
)";
$conn->query($membersSql);
$sessionsSql = "CREATE TABLE IF NOT EXISTS sessions (
	session_id VARCHAR(32) PRIMARY KEY,
	member_id VARCHAR(36),
	FOREIGN KEY (member_id) REFERENCES members(member_id),
	ip VARCHAR(45) NOT NULL,
	started DATETIME,
	extended TINYINT(1) DEFAULT 0
)";
$conn->query($sessionsSql);
$followsSql = "CREATE TABLE IF NOT EXISTS follows (
	member VARCHAR(36) NOT NULL, 
	followed VARCHAR(36) NOT NULL,
	FOREIGN KEY (member) REFERENCES members(member_id),
	FOREIGN KEY (followed) REFERENCES members(member_id),
	date DATETIME NOT NULL,
	PRIMARY KEY (member, followed)
)";
$conn->query($followsSql);
$notificationsSql = "CREATE TABLE IF NOT EXISTS notifications (
	member VARCHAR(36) NOT NULL,
	FOREIGN KEY (member) REFERENCES members(member_id),
	notification_id VARCHAR(36) NOT NULL,
	type VARCHAR(20) NOT NULL,
	content VARCHAR(500) NOT NULL,
	status_id VARCHAR(36) DEFAULT NULL,
	FOREIGN KEY (status_id) REFERENCES statuses(status_id),
	date DATETIME NOT NULL,
	`read` TINYINT(1) DEFAULT 0,
	official TINYINT(1) DEFAULT 0,
	PRIMARY KEY (member, notification_id)
)";
$conn->query($notificationsSql);
$likesSql = "CREATE TABLE IF NOT EXISTS likes (
	member VARCHAR(36) NOT NULL, 
	liked VARCHAR(36) NOT NULL,
	FOREIGN KEY (member) REFERENCES members(member_id),
	FOREIGN KEY (liked) REFERENCES statuses(status_id),
	date DATETIME NOT NULL,
	PRIMARY KEY (member, liked)
)";
$conn->query($likesSql);
$skipsSql = "CREATE TABLE IF NOT EXISTS skips (
	member VARCHAR(36) NOT NULL,
	skipped VARCHAR(36) NOT NULL,
	FOREIGN KEY (member) REFERENCES members(member_id),
	FOREIGN KEY (skipped) REFERENCES statuses(status_id),
	date DATE NOT NULL,
	PRIMARY KEY (member, skipped)
)";
$conn->query($skipsSql);
$statusesSql = "CREATE TABLE IF NOT EXISTS statuses (
	status_id VARCHAR(36) PRIMARY KEY,
	author VARCHAR(36) NOT NULL,
	FOREIGN KEY (author) REFERENCES members(member_id),
	status VARCHAR(1000) NOT NULL,
	date DATETIME NOT NULL
)";
$conn->query($statusesSql);
$commentsSql = "CREATE TABLE IF NOT EXISTS comments (
	status_id VARCHAR(36) NOT NULL,
	FOREIGN KEY (status_id) REFERENCES statuses(status_id),
	comment_id VARCHAR(36) PRIMARY KEY,
	author VARCHAR(36) NOT NULL,
	FOREIGN KEY (author) REFERENCES members(member_id),
	comment VARCHAR(500) NOT NULL,
	date DATETIME NOT NULL
)";
$conn->query($commentsSql);
date_default_timezone_set("America/New_York");
$ip = $_SERVER["REMOTE_ADDR"];
$date = date("Y-m-d H:i:s");
function guidv4() {
    $data = random_bytes(16);
    assert(strlen($data) == 16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
function usernameExists($username) {
	global $conn;
	$result = $conn->prepare("SELECT * FROM members WHERE username = ?");
	$result->bind_param("s", $username);
	$result->execute();
	$result->store_result();
	$rows = $result->num_rows;
	$result->close();
	if ($rows > 0) {
		return true;
	} else {
		return false;
	}
}
function emailExists($email) {
	global $conn;
	$result = $conn->prepare("SELECT * FROM members WHERE email = ?");
	$result->bind_param("s", $email);
	$result->execute();
	$result->store_result();
	$rows = $result->num_rows;
	$result->close();
	if ($rows > 0) {
		return true;
	} else {
		return false;
	}
}
function getId($identifier) {
	global $conn;
	if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
		$result = $conn->prepare("SELECT member_id FROM members WHERE email = ?");
	} else {
		$result = $conn->prepare("SELECT member_id FROM members WHERE username = ?");
	}
	$result->bind_param("s", $identifier);
	$result->execute();
	$result->bind_result($memberId);
	$result->fetch();
	$result->close();
	if (!isset($memberId)) {
		// NO DATABASE RESULTS
		return false;
	} else {
		return $memberId;
	}
}
function getUsername($memberId) {
	global $conn;
	$result = $conn->prepare("SELECT username FROM members WHERE member_id = ?");
	$result->bind_param("s", $memberId);
	$result->execute();
	$result->bind_result($username);
	$result->fetch();
	$result->close();
	if (!isset($username)) {
		// NO DATABASE RESULTS
		return false;
	} else {
		return $username;
	}
}
function getStatusAuthor($statusId) {
	global $conn;
	$result = $conn->prepare("SELECT author FROM statuses WHERE status_id = ?");
	$result->bind_param("s", $statusId);
	$result->execute();
	$result->bind_result($author);
	$result->fetch();
	$result->close();
	if (!isset($author)) {
		// NO DATABASE RESULTS
		return false;
	} else {
		return $author;
	}
}
function getStatusFromId($statusId) {
	global $conn;
	$result = $conn->prepare("SELECT status FROM statuses WHERE status_id = ?");
	$result->bind_param("s", $statusId);
	$result->execute();
	$result->bind_result($status);
	$result->fetch();
	$result->close();
	if (!isset($status)) {
		// NO DATABASE RESULTS
		return false;
	} else {
		return $status;
	}
}
function getStatusTimestamp($statusId) {
	global $conn;
	$result = $conn->prepare("SELECT date FROM statuses WHERE status_id = ?");
	$result->bind_param("s", $statusId);
	$result->execute();
	$result->bind_result($date);
	$result->fetch();
	$result->close();
	if (!isset($date)) {
		// NO DATABASE RESULTS
		return false;
	} else {
		return $date;
	}
}
function createSession($memberId, $extended = 0) {
	global $conn;
	global $ip;
	global $date;
	$sessionId = bin2hex(random_bytes(16));
	$checkIfSessionExists = $conn->prepare("SELECT session_id FROM sessions WHERE session_id = ?");
	$checkIfSessionExists->bind_param("s", $sessionId);
	$checkIfSessionExists->execute();
	$checkIfSessionExists->bind_result($sessionIdFromDb);
	$checkIfSessionExists->fetch();
	$checkIfSessionExists->close();
	while (isset($sessionIdFromDb)) {
		$sessionId = bin2hex(random_bytes(16));
		$checkIfSessionExists = $conn->prepare("SELECT session_id FROM sessions WHERE session_id = ?");
		$checkIfSessionExists->bind_param("s", $sessionId);
		$checkIfSessionExists->execute();
		$checkIfSessionExists->bind_result($sessionIdFromDb);
		$checkIfSessionExists->fetch();
		$checkIfSessionExists->close();
	}
	$createSession = $conn->prepare("INSERT INTO sessions (session_id, member_id, ip, started, extended) VALUES (?, ?, ?, ?, ?)");
	$createSession->bind_param("ssssi", $sessionId, $memberId, $ip, $date, $extended);
	$createSession->execute();
	$createSession->close();
	return $sessionId;
}
function register($username, $email, $password) {
	global $conn;
	global $ip;
	global $date;
	$guid = guidv4();
	$checkIfIdExists = $conn->prepare("SELECT member_id FROM members WHERE member_id = ?");
	$checkIfIdExists->bind_param("s", $guid);
	$checkIfIdExists->execute();
	$checkIfIdExists->bind_result($memberId);
	$checkIfIdExists->fetch();
	$checkIfIdExists->close();
	while (isset($memberId)) {
		$guid = guidv4();
		$checkIfIdExists = $conn->prepare("SELECT member_id FROM members WHERE member_id = ?");
		$checkIfIdExists->bind_param("s", $guid);
		$checkIfIdExists->execute();
		$checkIfIdExists->bind_result($memberId);
		$checkIfIdExists->fetch();
		$checkIfIdExists->close();
	}
	$passwordHash = password_hash(hash("sha256", $password), PASSWORD_DEFAULT);
	$emailVerificationToken = bin2hex(random_bytes(16));
	$stmt = $conn->prepare("INSERT INTO members (member_id, username, email, password_hash, email_verification_token, reg_date) VALUES (?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssssss", $guid, $username, $email, $passwordHash, $emailVerificationToken, $date);
	$stmt->execute();
	if ($stmt->affected_rows > 0) {
		$return = createSession($guid);
	} else {
		$return = false;
	}
	$stmt->close();
	return $return;
}
function login($identifier, $password, $extended = 0) {
	global $conn;
	global $date;
	if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
		$result = $conn->prepare("SELECT password_hash FROM members WHERE email = ?");
	} else {
		$result = $conn->prepare("SELECT password_hash FROM members WHERE username = ?");
	}
	$result->bind_param("s", $identifier);
	$result->execute();
	$result->bind_result($passwordHash);
	$result->fetch();
	$result->close();
	if (!isset($passwordHash)) {
		// NO DATABASE RESULTS
		return false;
	} else if (!password_verify(hash("sha256", $password), $passwordHash)) {
		// INVALID PASSWORD
		return false;
	} else {
		// LOGGED IN
		if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
			$updateLoggedInTime = $conn->prepare("UPDATE members SET log_date = ? WHERE email = ?");
		} else {
			$updateLoggedInTime = $conn->prepare("UPDATE members SET log_date = ? WHERE username = ?");
		}
		$updateLoggedInTime->bind_param("ss", $date, $identifier);
		$updateLoggedInTime->execute();
		$updateLoggedInTime->close();
		$memberId = getId($identifier);
		$sessionId = createSession($memberId, $extended);
		return $sessionId;
	}
}
function checkSession($sessionId) {
	global $conn;
	$result = $conn->prepare("SELECT member_id, started, extended FROM sessions WHERE session_id = ?");
	$result->bind_param("s", $sessionId);
	$result->execute();
	$result->bind_result($memberId, $startedDate, $extended);
	$result->fetch();
	$result->close();
	$now = strtotime(date("Y-m-d H:i:s", strtotime($startedDate)));
	if (!isset($startedDate)) {
		// NO ENTRIES
		return false;
	} else if ($extended === 0 && time() > $now + 172800) {
		// SESSION EXPIRED (LASTS 2 DAYS)
		return false;
	} else if ($extended === 1 && time() > $now + 604800) {
		// SESSION EXPIRED (LASTS 7 DAYS)
		return false;
	} else {
		return $memberId;
	}
}
function isVerified($memberId) {
	global $conn;
	$result = $conn->prepare("SELECT verified FROM members WHERE member_id = ?");
	$result->bind_param("s", $memberId);
	$result->execute();
	$result->bind_result($verified);
	$result->fetch();
	$result->close();
	if (!isset($verified)) {
		// NO ENTRIES
		return false;
	} else if ($verified !== 1) {
		return false;
	} else {
		return true;
	}
}
function verifyEmail($email, $verificationToken) {
	global $conn;
	$result = $conn->prepare("SELECT email_verified FROM members WHERE email = ? AND email_verification_token = ?");
	$result->bind_param("ss", $email, $verificationToken);
	$result->execute();
	$result->bind_result($emailVerified);
	$result->fetch();
	$result->close();
	if (!isset($emailVerified)) {
		// NO DATABASE RESULTS (INVALID VERIFICATION TOKEN?)
		return false;
	} else if ($emailVerified === 1) {
		// ALREADY VERIFIED
		return false;
	} else {
		// VERIFY THEIR EMAIL
		$verifyEmail = $conn->prepare("UPDATE members SET email_verified = ?, email_verification_token = ? WHERE email = ?");
		$param1 = 1;
		$param2 = null;
		$verifyEmail->bind_param("iss", $param1, $param2, $email);
		$verifyEmail->execute();
		$verifyEmail->close();
		return true;
	}
}
function sendNotification($member, $type, $content, $statusId = false, $official) {
	global $conn;
	global $date;
	$guid = guidv4();
	$checkIfIdExists = $conn->prepare("SELECT notification_id FROM notifications WHERE notification_id = ?");
	$checkIfIdExists->bind_param("s", $guid);
	$checkIfIdExists->execute();
	$checkIfIdExists->bind_result($notificationIdFromDb);
	$checkIfIdExists->fetch();
	$checkIfIdExists->close();
	while (isset($notificationIdFromDb)) {
		$guid = guidv4();
		$checkIfIdExists = $conn->prepare("SELECT notification_id FROM notifications WHERE notification_id = ?");
		$checkIfIdExists->bind_param("s", $guid);
		$checkIfIdExists->execute();
		$checkIfIdExists->bind_result($notificationIdFromDb);
		$checkIfIdExists->fetch();
		$checkIfIdExists->close();
	}
	if ($statusId === false) {
		$stmt = $conn->prepare("INSERT INTO notifications (notification_id, member, type, content, date, official) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sssssi", $guid, $member, $type, $content, $date, $official);
	} else {
		$stmt = $conn->prepare("INSERT INTO notifications (notification_id, member, type, content, status_id, date, official) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssssi", $guid, $member, $type, $content, $statusId, $date, $official);
	}
	$stmt->execute();
	if (mysqli_stmt_error($stmt)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
	}
	$stmt->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return true;
	}
}
function changeUsername($id, $newUsername) {
	global $conn;
	$changeUsername = $conn->prepare("UPDATE members SET username = ? WHERE member_id = ?");
	$changeUsername->bind_param("ss", $newUsername, $id);
	$changeUsername->execute();
	if (mysqli_stmt_error($changeUsername)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
	}
	$changeUsername->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return true;
	}
}
function changeEmail($id, $newEmail) {
	global $conn;
	$changeEmail = $conn->prepare("UPDATE members SET email = ?, email_verified = ?, email_verification_token = ? WHERE member_id = ?");
	$emailVerified = 0;
	$emailVerificationToken = bin2hex(random_bytes(16));
	$changeEmail->bind_param("siss", $newEmail, $emailVerified, $emailVerificationToken, $id);
	$changeEmail->execute();
	if (mysqli_stmt_error($changeEmail)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
	}
	$changeEmail->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return true;
	}
}
function changePassword($id, $newPassword) {
	global $conn;
	$passwordHash = password_hash(hash("sha256", $newPassword), PASSWORD_DEFAULT);
	$changePassword = $conn->prepare("UPDATE members SET password_hash = ? WHERE member_id = ?");
	$changePassword->bind_param("ss", $passwordHash, $id);
	$changePassword->execute();
	if (mysqli_stmt_error($changePassword)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
	}
	$changePassword->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return true;
	}
}
function followExists($member, $followed) {
	global $conn;
	$result = $conn->prepare("SELECT * FROM follows WHERE member = ? AND followed = ?");
	$result->bind_param("ss", $member, $followed);
	$result->execute();
	$result->store_result();
	$rows = $result->num_rows;
	$result->close();
	if ($rows > 0) {
		return true;
	} else {
		return false;
	}
}
function follow($member, $followed) {
	global $conn;
	global $date;
	$follow = $conn->prepare("INSERT INTO follows (member, followed, date) VALUES (?, ?, ?)");
	$follow->bind_param("sss", $member, $followed, $date);
	$follow->execute();
	if ($follow->affected_rows > 0) {
		$return = true;
	} else {
		$return = false;
	}
	$follow->close();
	return $return;
}
function unfollow($member, $unfollowed) {
	global $conn;
	$unfollow = $conn->prepare("DELETE FROM follows WHERE member = ? AND followed = ?");
	$unfollow->bind_param("ss", $member, $unfollowed);
	$unfollow->execute();
	if ($unfollow->affected_rows > 0) {
		$return = true;
	} else {
		$return = false;
	}
	$unfollow->close();
	return $return;
}
function countFollows($id) {
	global $conn;
	$countFollows = $conn->prepare("SELECT COUNT(*) FROM follows WHERE member = ?");
	$countFollows->bind_param("s", $id);
	$countFollows->execute();
	if (mysqli_stmt_error($countFollows)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$row = $countFollows->get_result()->fetch_row();
		$followsCount = $row[0];
	}
	$countFollows->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return $followsCount;
	}
}
function countFollowers($id) {
	global $conn;
	$countFollowers = $conn->prepare("SELECT COUNT(*) FROM follows WHERE followed = ?");
	$countFollowers->bind_param("s", $id);
	$countFollowers->execute();
	if (mysqli_stmt_error($countFollowers)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$row = $countFollowers->get_result()->fetch_row();
		$followersCount = $row[0];
	}
	$countFollowers->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return $followersCount;
	}
}
function statusExists($statusId) {
	global $conn;
	$result = $conn->prepare("SELECT * FROM statuses WHERE status_id = ?");
	$result->bind_param("s", $statusId);
	$result->execute();
	$result->store_result();
	$rows = $result->num_rows;
	$result->close();
	if ($rows > 0) {
		return true;
	} else {
		return false;
	}
}
function status($author, $status) {
	global $conn;
	global $date;
	$guid = guidv4();
	$checkIfIdExists = $conn->prepare("SELECT status_id FROM statuses WHERE status_id = ?");
	$checkIfIdExists->bind_param("s", $guid);
	$checkIfIdExists->execute();
	$checkIfIdExists->bind_result($statusIdFromDb);
	$checkIfIdExists->fetch();
	$checkIfIdExists->close();
	while (isset($statusIdFromDb)) {
		$guid = guidv4();
		$checkIfIdExists = $conn->prepare("SELECT status_id FROM statuses WHERE status_id = ?");
		$checkIfIdExists->bind_param("s", $guid);
		$checkIfIdExists->execute();
		$checkIfIdExists->bind_result($statusIdFromDb);
		$checkIfIdExists->fetch();
		$checkIfIdExists->close();
	}
	$stmt = $conn->prepare("INSERT INTO statuses (status_id, author, status, date) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("ssss", $guid, $author, $status, $date);
	$stmt->execute();
	if (mysqli_stmt_error($stmt)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
	}
	$stmt->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return true;
	}
}
function countStatuses($memberId) {
	global $conn;
	$countStatuses = $conn->prepare("SELECT COUNT(*) FROM statuses WHERE author = ?");
	$countStatuses->bind_param("s", $memberId);
	$countStatuses->execute();
	if (mysqli_stmt_error($countStatuses)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$row = $countStatuses->get_result()->fetch_row();
		$statusCount = $row[0];
	}
	$countStatuses->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return $statusCount;
	}
}
function likeExists($member, $liked) {
	global $conn;
	$result = $conn->prepare("SELECT * FROM likes WHERE member = ? AND liked = ?");
	$result->bind_param("ss", $member, $liked);
	$result->execute();
	$result->store_result();
	$rows = $result->num_rows;
	$result->close();
	if ($rows > 0) {
		return true;
	} else {
		return false;
	}
}
function skipExists($member, $skipped) {
	global $conn;
	$result = $conn->prepare("SELECT * FROM skips WHERE member = ? AND skipped = ?");
	$result->bind_param("ss", $member, $skipped);
	$result->execute();
	$result->store_result();
	$rows = $result->num_rows;
	$result->close();
	if ($rows > 0) {
		return true;
	} else {
		return false;
	}
}
function skip($member, $skipped) {
	global $conn;
	global $date;
	$skip = $conn->prepare("INSERT INTO skips (member, skipped, date) VALUES (?, ?, ?)");
	$skip->bind_param("sss", $member, $skipped, $date);
	$skip->execute();
	if ($skip->affected_rows > 0) {
		$return = true;
	} else {
		$return = false;
	}
	$skip->close();
	return $return;
}
function like($member, $liked) {
	global $conn;
	global $date;
	$like = $conn->prepare("INSERT INTO likes (member, liked, date) VALUES (?, ?, ?)");
	$like->bind_param("sss", $member, $liked, $date);
	$like->execute();
	if ($like->affected_rows > 0) {
		$return = true;
	} else {
		$return = false;
	}
	$like->close();
	return $return;
}
function unlike($member, $unliked) {
	global $conn;
	$unlike = $conn->prepare("DELETE FROM likes WHERE member = ? AND liked = ?");
	$unlike->bind_param("ss", $member, $unliked);
	$unlike->execute();
	if ($unlike->affected_rows > 0) {
		$return = true;
	} else {
		$return = false;
	}
	$unlike->close();
	return $return;
}
function countStatusLikes($statusId) {
	global $conn;
	$countStatusLikes = $conn->prepare("SELECT COUNT(*) FROM likes WHERE liked = ?");
	$countStatusLikes->bind_param("s", $statusId);
	$countStatusLikes->execute();
	if (mysqli_stmt_error($countStatusLikes)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$row = $countStatusLikes->get_result()->fetch_row();
		$likesCount = $row[0];
	}
	$countStatusLikes->close();
	if ($mysqlError === true) {
		return false;
	} else {
		return $likesCount;
	}
}
function listStatuses($member) {
	global $conn;
	$listStatuses = $conn->prepare("SELECT status,status_id,date FROM statuses WHERE author = ?");
	$listStatuses->bind_param("s", $member);
	$listStatuses->execute();
	if (mysqli_stmt_error($listStatuses)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$statuses = $listStatuses->get_result()->fetch_all(MYSQLI_ASSOC);
	}
	$listStatuses->close();
	if ($mysqlError === true) {
		return false;
	} else if (empty($statuses)) {
		return false;
	} else {
		return $statuses;
	}
}
function listFollowers($member) {
	global $conn;
	$listFollowers = $conn->prepare("SELECT member,date FROM follows WHERE followed = ?");
	$listFollowers->bind_param("s", $member);
	$listFollowers->execute();
	if (mysqli_stmt_error($listFollowers)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$followers = $listFollowers->get_result()->fetch_all(MYSQLI_ASSOC);
	}
	$listFollowers->close();
	if ($mysqlError === true) {
		return false;
	} else if (empty($followers)) {
		return false;
	} else {
        return $followers;
	}
}
function listFollowing($member, $limit = 500) {
	global $conn;
	//$listFollowing = $conn->prepare("SELECT followed,date FROM follows WHERE member = ? ORDER BY date DESC LIMIT ?");
	$listFollowing = $conn->prepare("SELECT * FROM statuses WHERE author IN (SELECT followed FROM follows WHERE member = ?) ORDER BY date DESC LIMIT ?");
	$listFollowing->bind_param("si", $member, $limit);
	$listFollowing->execute();
	if (mysqli_stmt_error($listFollowing)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$following = $listFollowing->get_result()->fetch_all(MYSQLI_ASSOC);
	}
	$listFollowing->close();
	if ($mysqlError === true) {
		return false;
	} else if (empty($following)) {
		return false;
	} else {
        return $following;
	}
}
function latestStatusId($member) {
	global $conn;
	$latestStatusId = $conn->prepare("SELECT status_id FROM statuses WHERE author = ? ORDER BY date DESC LIMIT 1");
	$latestStatusId->bind_param("s", $member);
	$latestStatusId->execute();
	if (mysqli_stmt_error($latestStatusId)) {
		$mysqlError = true;
	} else {
		$mysqlError = false;
		$latestStatusId->bind_result($statusId);
		$latestStatusId->fetch();
	}
	$latestStatusId->close();
	if ($mysqlError === true) {
		return false;
	} else if (empty($statusId)) {
		return false;
	} else {
        return $statusId;
	}
}
function latestStatus($member) {
	global $conn;
	global $date;
	$following = listFollowing($member);
	if ($following === false) {
		return false;
	} else {
		foreach ($following as $object) {
			$memberId = $object["author"];
			$statusId = $object["status_id"];
			if (likeExists($member, $statusId)) {
				// let's remove this status!
			} else if (skipExists($member, $statusId)) {
				// let's remove this status
			} else {
				$array = [
					"member_id" => $memberId,
					"status_id" => $statusId
				];
				break;
			}
		}
		if (!isset($array)) {
			return false;
		} else {
			return $array;
		}
	}
}
?>