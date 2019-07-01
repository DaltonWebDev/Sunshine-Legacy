<?php
$sessionId = !empty($_REQUEST["session_id"]) ? strtolower($_REQUEST["session_id"]) : false;
$unfollowThem = !empty($_REQUEST["unfollow_them"]) ? $_REQUEST["unfollow_them"] : false;
if ($sessionId !== false) {
    $memberId = checkSession($sessionId);
}
if ($sessionId === false) {
	$error = "SESSION_ID_MISSING";
} else if ($memberId === false) {
	$error = "INVALID_SESSION";
} else if ($unfollowThem === false) {
	$error = "UNFOLLOW_THEM_MISSING";
} else if (!followExists($memberId, $unfollowThem)) {
	$error = "NONEXISTENT_FOLLOW";
} else {
	$result = unfollow($memberId, $unfollowThem);
	if ($result === false) {
		$error = "UNKNOWN_ERROR";
	} else {
		$error = false;
	}
}
?>