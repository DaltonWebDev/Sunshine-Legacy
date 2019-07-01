<?php
$identifier = !empty($_REQUEST["identifier"]) ? $_REQUEST["identifier"] : false;
if ($identifier === false) {
	$error = "IDENTIFIER_MISSING";
} else {
    $memberId = getId($identifier);
	if ($memberId === false) {
        $error = "MEMBER_NONEXISTENT";
    } else if (countFollows($memberId) === 0) {
        $error = "NO_FOLLOWING";
	} else {	    
        $error = false;
        $data["count"] = countFollows($memberId);
        $data["list"] = listFollowing($memberId);
	}
}
?>