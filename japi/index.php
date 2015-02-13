<?php
// example to lock out unregistered users - but search shouldn't be locked down hehe
/* $headers = getallheaders();
if(@isset($headers["X-Bowtie-User-Id"]) === false) {
	echo "<span class=\"alert alert-danger\">You are not logged in. Please sign up or login and try again.</span>";
	exit;
} */

// use filter_input to sanitize video search
$search = (filter_input(INPUT_POST, "search", FILTER_SANITIZE_STRING));
if(empty($search) === true) {
	echo "<span class=\"alert alert-danger\">Invalid search parameters. Please re-enter the search query and try again.</span>";
	exit;
}

// sanitize the search and perform a multi-threaded search
header("Content-type: text/json");
$search = escapeshellarg($search);
$command = "java -jar japi.jar $search";
$command = escapeshellcmd($command);
passthru($command);
?>