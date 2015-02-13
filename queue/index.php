<?php
require_once("../lib/encrypted-config.php");
require_once("../php/classes/profile.php");
require_once("../php/classes/profilequeue.php");
require_once("../php/classes/queue.php");
require_once("../php/classes/video.php");
require_once("../php/classes/videoqueue.php");

$headers = getallheaders();
if(@isset($headers["X-Bowtie-User-Id"]) === false) {
	echo "<span class=\"alert alert-danger\">You are not logged in. Please sign up or login and try again.</span>";
	exit;
}

try {
	mysqli_report(MYSQLI_REPORT_STRICT);
	$config = readConfig("/etc/apache2/arlo.ini");
	$mysqli = new mysqli($config["hostname"], $config["username"], $config["password"], $config["database"]);
	$profile = Profile::getProfileByBowtieUserId($mysqli, $headers["X-Bowtie-User-Id"]);

	if($profile === null) {
		$profile = new Profile(null, $headers["X-Bowtie-User-Email"], null, $headers["X-Bowtie-User-Id"]);
		$profile->insert($mysqli);
	}

	$profileQueues = ProfileQueue::getAllQueuesByProfileId($mysqli, $profile->getProfileId());
	if($profileQueues === null) {
		$queue = new Queue(null);
		$queue->insert($mysqli);
		$profileQueue = new ProfileQueue($profile->getProfileId(), $queue->getQueueId(), $headers["X-Bowtie-User-Name"] ."'s Queue");
		$profileQueue->insert($mysqli);
	} else {
		$profileQueue = $profileQueues[0];
	}

	if(empty($_POST) === true) {
		$videoQueues = VideoQueue::getAllVideosByQueueId($mysqli, $queue->getQueueId());
		if(empty($videoQueues) === true) {
			$videoQueues = array();
		}

		header("Content-type: text/json");
		json_encode($videoQueues);
	} else {
		$imdbId = (filter_input(INPUT_POST, "imdbId", FILTER_SANITIZE_STRING));
		if(empty($imdbId) === true) {
			echo "<span class=\"alert alert-danger\">Invalid imdb ID. Please re-enter the search query and try again.</span>";
			exit;
		}

		// FIXME: rename/refactor this method
		$video = Video::getAllVideosByImdbId($mysqli, $imdbId);
		if($video === null) {
			$now = new DateTime();
			$comment = "Video added at " . $now->format("Y-m-d H:i:s");
			$video = new Video(null, $comment, $imdbId);
			$video->insert($mysqli, $video);
		} else {
			$video = $video[0];
		}

		$videoQueue = new VideoQueue($video->getVideoId(), $queue->getQueueId(), 1);
		$videoQueue->insert($mysqli);
		echo "<span class=\"alert alert-success\">Video added to queue successfully.</span>";
		$mysqli->close();
	}
} catch(Exception $exception) {
	echo "<span class=\"alert alert-danger\">Unable to create/load queue: " . $exception->getMessage() . "</span>";
	exit;
}