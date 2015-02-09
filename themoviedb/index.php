<?php
// use filter_input to sanitize video search
$search = (filter_input(INPUT_GET, "search", FILTER_SANITIZE_STRING));
if(empty($search) === true) {
	echo "<span class=\"alert alert-danger\">Invalid search parameters. Please re-enter the search query and try again.</span>";
	exit;
}

require_once("../lib/encrypted-config.php");
$config = readConfig("/etc/apache2/arlo.ini");
$apiKey = $config["theMovieDbApiKey"];
$q = urlencode($search);

// build query with apikey and video search query
$endpoint = "https://api.themoviedb.org/3/search/movie?api_key=$apiKey&append_to_response=releases,trailers&query=$q";

if(($jsonData = file_get_contents($endpoint)) === false) {
	echo "<span class=\"alert alert-danger\">Unable to download Movie DB search results.</span>";
	exit;
}

// decode the json data to make it easier to parse the php
$searchResults = json_decode($jsonData);
if ($searchResults === null){
	echo "<span class=\"alert alert-danger\">Unable to read Movie DB search results.</span>";
	exit;
}

// display the data
$results = array();
$movies = $searchResults->results;
foreach($movies as $movie) {
	// get the core data about the movie
	$result["id"] = $movie->id;
	$result["name"] = $movie->title;

	// download additional data about the movie
	$endpoint = "https://api.themoviedb.org/3/movie/" . $movie->id . "?api_key=$apiKey";
	if(($jsonData = file_get_contents($endpoint)) === false) {
		echo "<span class=\"alert alert-danger\">Unable to download Movie DB search results.</span>";
		exit;
	}

	// decode the supplemental data
	$movieData = json_decode($jsonData);
	if($movieData === null) {
		echo "<span class=\"alert alert-danger\">Unable to read Movie DB search results.</span>";
		exit;
	}

	// save the supplemental data
	$result["imdbId"] = $movieData->imdb_id;
	$result["plot"] = $movieData->overview;
	$result["banners"] = array($movieData->poster_path);

	// download additional cast data
	$endpoint = "https://api.themoviedb.org/3/movie/" . $movie->id . "/credits?api_key=$apiKey";
	if(($jsonData = file_get_contents($endpoint)) === false) {
		echo "<span class=\"alert alert-danger\">Unable to download Movie DB search results.</span>";
		exit;
	}

	// decode the supplemental data
	$castData = json_decode($jsonData);
	if($castData === null) {
		echo "<span class=\"alert alert-danger\">Unable to read Movie DB search results.</span>";
		exit;
	}

	// save the actor data
	$actors = array();
	foreach($castData->cast as $cast) {
		if(@isset($cast->cast_id) === true) {
			$actors[] = $cast->name;
		}
	}

	// get the available stream and digital purchase options
	$imdbid = $movieData->imdb_id;
	$canIStreamItURL = "http://www.canistream.it/external/imdb/$imdbid?l=default";
	$result = $canIStreamItURL;

	// save the result
	$result["actors"] = $actors;
	$results[] = $result;
}

// send the result
header("Content-type: text/json");
echo json_encode($results);
?>