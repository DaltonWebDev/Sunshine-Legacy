<?php
$sessionId = !empty($_REQUEST["session_id"]) ? strtolower($_REQUEST["session_id"]) : false;
$status = !empty($_REQUEST["status"]) ? $_REQUEST["status"] : false;
if ($sessionId !== false) {
    $memberId = checkSession($sessionId);
}
if ($sessionId === false) {
	$error = "SESSION_ID_MISSING";
} else if ($memberId === false) {
	$error = "INVALID_SESSION";
} else if ($status === false) {
	$error = "STATUS_MISSING";
} else if (strlen($status) > 1000) {
	$error = "STATUS_TOO_LONG";
} else {
	$result = status($memberId, $status);
	if ($result === false) {
		$error = "UNKNOWN_ERROR";
	} else {
		$error = false;
	}
}
?>