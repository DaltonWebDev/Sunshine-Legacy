<?php
$statusId = !empty($_REQUEST["status_id"]) ? $_REQUEST["status_id"] : false;
if ($statusId === false) {
    $error = "STATUS_ID_MISSING";
} else if (!statusExists($statusId)) {
	$error = "NONEXISTENT_STATUS";
} else {
    $error = false;
    $data["author"] = getStatusAuthor($statusId);
    $data["username"] = getUsername($data["author"]);
    $data["status"] = getStatusFromId($statusId);
    $data["timestamp"] = getStatusTimestamp($statusId);
    $data["likes"] = countStatusLikes($statusId);
}
?>