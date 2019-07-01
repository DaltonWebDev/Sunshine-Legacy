<?php
$sessionId = !empty($_REQUEST["session_id"]) ? strtolower($_REQUEST["session_id"]) : false;
$statusId = !empty($_REQUEST["status_id"]) ? $_REQUEST["status_id"] : false;
if ($sessionId !== false) {
    $memberId = checkSession($sessionId);
}
if ($sessionId === false) {
	$error = "SESSION_ID_MISSING";
} else if ($memberId === false) {
	$error = "INVALID_SESSION";
} else if ($statusId === false) {
	$error = "STATUS_ID_MISSING";
} else if (!statusExists($statusId)) {
	$error = "NONEXISTENT_STATUS";
} else if (likeExists($memberId, $statusId)) {
	$error = "LIKE_EXISTS";
} else {
	$result = like($memberId, $statusId);
	if ($result === false) {
		$error = "UNKNOWN_ERROR";
	} else {
		$error = false;
	}
}
?>