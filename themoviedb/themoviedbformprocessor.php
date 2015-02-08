<?php
/**
 * Form processor for themoviedb API search
 */
// use filter_input to sanitize video search
$videoSearch = (filter_input(INPUT_GET, "videoSearch", FILTER_SANITIZE_STRING));

require_once("../thetvdb/encrypted-config.php");
$config = readConfig("/etc/apache2/arlo.ini");
$apiKey = $config["theMovieDbApiKey"];
$q = urlencode($videoSearch);

// build query with apikey and video search query
$endpoint = "https://api.themoviedb.org/3/search/movie?api_key=$apiKey&append_to_response=releases,trailers&query=$q";

if (($jsonData = file_get_contents($endpoint)) === false) {
	throw(new RuntimeException("unable to query the Movie DB"));
}

// decode the json data to make it easier to parse the php
$search_results = json_decode($jsonData);
if ($search_results === null) die('Error parsing json');
var_dump($search_results);

// display the data
$movies = $search_results->movies;
echo '<ul>';
foreach ($movies as $movie) {
	echo '<li><a href="' . $movie->links->alternate . '">' . $movie->title . " (" . $movie->year . ")</a></li>";
}
echo '</ul>';

?>