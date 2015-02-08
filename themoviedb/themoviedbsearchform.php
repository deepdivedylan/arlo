<?php
/**
 * This is a starting point for returning results from the themoviedb api for search queries
 */

?>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet" />
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="rtapijavascript.js"></script>
		<title>ARLO theMovieDb API Search</title>
	</head>

	<body>
		<form id="tmdbapisearch" method="get" action="themoviedbformprocessor.php">
			<label for="videoSearch">Enter Search</label>
			<input type="text" id="videoSearch" name="videoSearch"/><br/>
			<button id="search" type="submit">Search</button>
		</form>
		<p id="outputVideoSearch"></p>
	</body>

</html>