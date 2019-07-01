<?php
$identifier = !empty($_REQUEST["identifier"]) ? strtolower($_REQUEST["identifier"]) : false;
$password = !empty($_REQUEST["password"]) ? $_REQUEST["password"] : false;
$extended = !empty($_REQUEST["extended"]) ? $_REQUEST["extended"] : 0;
$allowedExtended = [0,1];
if ($identifier === false) {
	$error = "IDENTIFIER_MISSING";
} else if ($password === false) {
	$error = "PASSWORD_MISSING";
} else if (!in_array($extended, $allowedExtended)) {
	$error = "INVALID_COOKIE_LENGTH";
} else {
	$login = login($identifier, $password, $extended);
	if ($login === false) {
		$error = "PASSWORD_INCORRECT";
	} else {
		$error = false;
		$data = $login;
	}
}
?>