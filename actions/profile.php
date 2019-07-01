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
	if ($memberId === false) {
		$error = "UNKNOWN_ERROR";
	} else {
        $error = false;
        if ($sessionId !== false) {
            $data["info"]["username"] = getUsername($memberId);
        }
        $data["info"]["verified"] = isVerified($memberId);
        $data["stats"]["followers"] = countFollowers($memberId);
        $data["stats"]["following"] = countFollows($memberId);
        $data["stats"]["posts"] = countStatuses($memberId);
    }
}
?>