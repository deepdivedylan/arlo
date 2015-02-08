<?php
// verify the search parameter
$search = urlencode(filter_input(INPUT_GET, "search", FILTER_SANITIZE_STRING));
if(empty($search) === false) {
	echo "<span class=\"alert alert-danger\">Invalid search parameters. Please re-enter the search query and try again.</span>";
	exit;
}

// read the config for the API key
require_once("../lib/encrypted-config.php");
$config = readConfig("/etc/apache2/arlo.ini");
$apiKey = $config["theTvDbiApiKey"];

if (($xmlData = file_get_contents("http://thetvdb.com/api/GetSeries.php?seriesname=$search")) === false) {
	echo "<span class=\"alert alert-danger\">Unable to connect to the TV DB.</span>";
	exit;
}

$xmlParser = new SimpleXMLElement($xmlData);
$results = array();
foreach($xmlParser->Series as $series) {
	// get the core data about the series
	// the explicit __toString() is necessary to convert from a SimpleXML object
	$result["id"] = intval($series->seriesid->__toString());
	$result["name"] = $series->SeriesName->__toString();
	$result["plot"] = $series->Overview->__toString();
	$result["imdbId"] = $series->IMDB_ID->__toString();

	// get the actors for the series
	$seriesid = intval($series->seriesid->__toString());
	$actors = array();
	$actorUrl = "http://thetvdb.com/api/$apiKey/series/$seriesid/actors.xml";
	if(($actorXml = file_get_contents($actorUrl)) === false) {
		echo "<span class=\"alert alert-danger\">Unable to retrieve actors.</span>";
		exit;
	}
	$actorParser = new SimpleXMLElement($actorXml);
	foreach($actorParser->Actor as $actor) {
		$actors[] = $actor->Name->__toString();
	}
	$result["actors"] = $actors;

	// get the series banners (skipping episode banners)
	$banners = array();
	$bannerUrl = "http://thetvdb.com/api/$apiKey/series/$seriesid/banners.xml";
	if(($bannerXml = file_get_contents($bannerUrl)) === false) {
		echo "<span class=\"alert alert-danger\">Unable to retrieve banners.</span>";
		exit;
	}
	$bannerParser = new SimpleXMLElement($bannerXml);
	foreach($bannerParser->Banner as $banner) {
		if($banner->BannerType->__toString() === "series") {
			$banners[] = $banner->BannerPath->__toString();
		}
	}
	$result["banners"] = $banners;

	// save this result
	$results[] = $result;
}

// send the result
header("Content-type: text/json");
echo json_encode($results);
?>