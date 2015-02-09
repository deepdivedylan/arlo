<?php
// use filter_input to sanitize video search
$search = (filter_input(INPUT_GET, "search", FILTER_SANITIZE_STRING));
if(empty($search) === true) {
	echo "<span class=\"alert alert-danger\">Invalid search parameters. Please re-enter the search query and try again.</span>";
	exit;
}

// load the API key and encode the search
require_once("../lib/encrypted-config.php");
$config = readConfig("/etc/apache2/arlo.ini");
$apiKey = $config["rottenTomatoesApiKey"];
$q = urlencode($search);

// build query with apikey and video search query
$endpoint = "http://api.rottentomatoes.com/api/public/v1.0/movies.json?apikey=$apiKey&q=$q";

if (($jsonData = file_get_contents($endpoint)) === false) {
	echo "<span class=\"alert alert-danger\">Unable to download Rotten Tomatoes search results.</span>";
	exit;
}

// decode the json data to make it easier to parse the php
$searchResults = json_decode($jsonData);
if ($searchResults === null){
	echo "<span class=\"alert alert-danger\">Unable to read Rotten Tomatoes search results.</span>";
	exit;
}

$results = array();
foreach($searchResults->movies as $movie) {
	// get the core data about the movie
	$result["id"] = $movie->id;
	$result["name"] = $movie->title;
	$result["plot"] = $movie->synopsis;
	$result["imdbId"] = $movie->alternate_ids->imdb;

	// get the actors for the movie
	$actors = array();
	foreach($movie->abridged_cast as $actor) {
		$actors[] = $actor->name;
	}
	$result["actors"] = $actors;

	// get the movie banners
	$banners = array();
	foreach($movie->posters as $banner) {
		$banners[] = $banner;
	}
	$result["banners"] = $banners;

	// get the available stream and digital purchase options
	$imdbid = $movie->alternate_ids->imdb;
	$canIStreamItURL = "http://www.canistream.it/external/imdb/$imdbid?l=default";
	$result = $canIStreamItURL;

	// save this result
	$results[] = $result;
}

// send the result
header("Content-type: text/json");
echo json_encode($results);
?>