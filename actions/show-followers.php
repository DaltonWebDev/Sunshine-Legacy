<?php
$identifier = !empty($_REQUEST["identifier"]) ? $_REQUEST["identifier"] : false;
if ($identifier === false) {
	$error = "IDENTIFIER_MISSING";
} else {
	$memberId = getId($identifier);
	if ($memberId === false) {
		$error = "MEMBER_NONEXISTENT";
	} else if (countFollowers($memberId) === 0) {
        $error = "NO_FOLLOWERS";
	} else {
        $error = false;
        $data["count"] = countFollowers($memberId);
        $data["list"] = listFollowers($memberId);
	}
}
?>