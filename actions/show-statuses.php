<?php
$identifier = !empty($_REQUEST["identifier"]) ? $_REQUEST["identifier"] : false;
$sessionId = !empty($_REQUEST["session_id"]) ? $_REQUEST["session_id"] : false;
if ($sessionId !== false) {
    $checkSession = checkSession($sessionId);
}
if ($identifier === false && $sessionId === false) {
    $error = "IDENTIFIER_MISSING";
} else if ($sessionId !== false && $checkSession === false) {
    $error = "INVALID_SESSION";
} else {
	if ($identifier !== false) {
        $memberId = getId($identifier);
    } else if ($sessionId !== false) {
        $memberId = $checkSession;
    }
	$result = listStatuses($memberId);
	$x = 0;
	foreach ($result as $object) {
		$result[$x]["likes"] = countStatusLikes($object["status_id"]);
		$x++;
	}
	if ($result === false) {
		$error = "UNKNOWN_ERROR";
	} else {
        $error = false;
        $data = $result;
	}
}
?>