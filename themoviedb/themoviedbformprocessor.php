<?php
/**
 * Form processor for themoviedb API search
 */
// use filter_input to sanitize video search
$videoSearch = (filter_input(INPUT_GET, "videoSearch", FILTER_SANITIZE_STRING));

$apikey = '250128b10ba2537fe90185c29765e913';
$q = urlencode($videoSearch);

// build query with apikey and video search query
$endpoint = 'https://api.themoviedb.org/3/' . $q . '/550?api_key=' . $apikey . '&append_to_response=releases,trailers';

// setup curl to make call to endpoint
$session = curl_init($endpoint);

// indicates that we want the response back
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// exec curl and get the data back
$data = curl_exec($session);

// close the curl session once we are done retrieving the data
curl_close($session);

// decode the json data to make it easier to parse the php
$search_results = json_decode($data);
if ($search_results === null) die('Error parsing json');

// display the data
$movies = $search_results->movies;
echo '<ul>';
foreach ($movies as $movie) {
	echo '<li><a href="' . $movie->links->alternate . '">' . $movie->title . " (" . $movie->year . ")</a></li>";
}
echo '</ul';

?>