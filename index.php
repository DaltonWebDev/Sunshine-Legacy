<?php
require "functions.php";
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST");
$action = !empty($_REQUEST["action"]) ? str_replace("/","", strtolower($_REQUEST["action"])) : false;
$data = false;
if ($action === false) {
	$error = "ACTION_MISSING";
} else if (!file_exists("actions/$action.php")) {
	$error = "ACTION_INVALID";
} else {
	include("actions/$action.php");
	$conn->close();
}
$outputArray = [
	"error" => $error,
	"data" => $data
];
echo json_encode($outputArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
?>
