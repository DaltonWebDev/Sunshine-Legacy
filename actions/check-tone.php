<?php
$string = !empty($_REQUEST["string"]) ? $_REQUEST["string"] : false;
if ($string === false) {
    $error = "STRING_MISSING";
} else if (strlen($string) > 1000) {
    $error = "STRING_TOO_LONG";
} else {
    $error = false;
	$badWords = [
        "fuck",
        "bitch",
        "fag",
        "dyke",
        "nigger",
        "stupid"
    ];
    $sadWords = [
        "sad",
        "depressed",
        "kill myself",
        "killing myself",
        "give up on life",
        "suicide"
    ];
    $badMatches = [];
    $badMatchFound = preg_match_all(
        "/(" . implode($badWords,"|") . ")/i", 
        $string, 
        $badMatches
    );
    $sadMatches = [];
    $sadMatchFound = preg_match_all(
        "/(" . implode($sadWords,"|") . ")/i", 
        $string, 
        $sadMatches
    );
    if ($badMatchFound) {
        $data["tone"] = "angry";
        $words = array_unique($badMatches[0]);
        foreach($words as $word) {
            $data["matches"][] = $word;
        }
    } else if ($sadMatchFound) {
        $data["tone"] = "sad";
        $words = array_unique($sadMatches[0]);
        foreach($words as $word) {
            $data["matches"][] = $word;
        }
    } else {
        // ...
    }
}
?>