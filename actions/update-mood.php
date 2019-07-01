<?php
//$sessionId = !empty($_REQUEST["session_id"]) ? strtolower($_REQUEST["session_id"]) : false;
$emoji = !empty($_REQUEST["emoji"]) ? $_REQUEST["emoji"] : false;
if ($emoji !== false) {
    $emojiCheck = Emoji\is_single_emoji($emoji);
}
//if ($sessionId !== false) {
//    $memberId = checkSession($sessionId);
//}
//if ($sessionId === false) {
//	$error = "SESSION_ID_MISSING";
//} else if ($memberId === false) {
//	$error = "INVALID_SESSION";
//} else if...
if ($emoji === false) {
    $error = "EMOJI_MISSING";
} else if ($emojiCheck === false) {
    $error = "INVALID_EMOJI";
} else {
	//$result = follow($memberId, $followThem);
	//if ($result === false) {
	//	$error = "UNKNOWN_ERROR";
	//} else {
        $error = false;
        $data = $emoji;
	//}
}
?>