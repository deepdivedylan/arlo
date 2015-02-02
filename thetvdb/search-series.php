<?php
// read the config for the API key
require_once("encrypted-config.php");
$config = readConfig("/etc/apache2/arlo.ini");
$apiKey = $config["theTvDbiApiKey"];

$searchKey = urlencode("Star Trek");
if (($xmlData = file_get_contents("http://thetvdb.com/api/GetSeries.php?seriesname=$searchKey")) === false) {
	throw(new RuntimeException("unable to query the TV DB"));
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
		throw(new RuntimeException("unable to retrieve actors"));
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
		throw(new RuntimeException("unable to retrieve banners"));
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