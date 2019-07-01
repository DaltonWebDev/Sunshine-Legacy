<?php
$username = !empty($_REQUEST["username"]) ? strtolower($_REQUEST["username"]) : false;
$email = !empty($_REQUEST["email"]) ? strtolower($_REQUEST["email"]) : false;
$password = !empty($_REQUEST["password"]) ? $_REQUEST["password"] : false;
if ($username === false) {
	$error = "USERNAME_MISSING";
} else if (!ctype_alnum($username)) {
	$error = "USERNAME_ALPHANUMERIC";
} else if (strlen($username) < 5) {
	$error = "USERNAME_TOO_SHORT";
} else if (strlen($username) > 20) {
	$error = "USERNAME_TOO_LONG";
} else if (usernameExists($username)) {
	$error = "USERNAME_EXISTS";
} else if ($email === false) {
	$error = "EMAIL_MISSING";
} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$error = "EMAIL_INVALID";
} else if (emailExists($email)) {
	$error = "EMAIL_EXISTS";
} else if ($password === false) {
	$error = "PASSWORD_MISSING";
} else if (strlen($password) < 12) {
	$error = "PASSWORD_TOO_SHORT";
} else if (strlen($password) > 1000) {
	$error = "PASSWORD_TOO_LONG";
} else {
	$register = register($username, $email, $password);
	if ($register === false) {
		$error = "UNKNOWN_ERROR";
	} else {
		$error = false;
		$data = $register;
	}
}
?>