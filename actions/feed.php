<?php
$sessionId = !empty($_REQUEST["session_id"]) ? strtolower($_REQUEST["session_id"]) : false;
if ($sessionId !== false) {
    $memberId = checkSession($sessionId);
}
if ($sessionId === false) {
	$error = "SESSION_ID_MISSING";
} else if ($memberId === false) {
	$error = "INVALID_SESSION";
} else {
	$result = latestStatus($memberId);
	if ($result === false) {
		$error = "UNKNOWN_ERROR";
	} else {
        $error = false;
        $data = $result;
	}
}
?>