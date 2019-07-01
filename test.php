<?php
require "vendor/autoload.php";
require "functions.php";

$member = "2069a3ab-1a3a-4a09-8615-5a53640f5fa9";
$type = "test";
$content = "This is a test notification. Kindly ignore it!";
$statusId = false;
$official = 1;

//$test = sendNotification($member, $type, $content, $statusId, $official);

$input = "Hello 👍🏼 World 👨‍👩‍👦‍👦";
$emoji = Emoji\detect_emoji($input);

print_r($emoji);

if ($test !== false) {
   // echo "TRUE";
} else {
   // echo "FALSE";
}
?>