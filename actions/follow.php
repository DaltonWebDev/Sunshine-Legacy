<?php
$sessionId = !empty($_REQUEST["session_id"]) ? strtolower($_REQUEST["session_id"]) : false;
$followThem = !empty($_REQUEST["follow_them"]) ? $_REQUEST["follow_them"] : false;
if ($sessionId !== false) {
    $memberId = checkSession($sessionId);
}
if ($sessionId === false) {
	$error = "SESSION_ID_MISSING";
} else if ($memberId === false) {
	$error = "INVALID_SESSION";
} else if ($followThem === false) {
    $error = "FOLLOW_THEM_MISSING";
} else if ($followThem === $memberId) {
	$error = "SELF_FOLLOW";
} else if (followExists($memberId, $followThem)) {
	$error = "FOLLOW_EXISTS";
} else {
	$result = follow($memberId, $followThem);
	if ($result === false) {
		$error = "UNKNOWN_ERROR";
	} else {
		$error = false;
	}
}
?>