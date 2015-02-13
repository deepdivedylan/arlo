/**
 * takes in JSON results and returns formatted HTML
 *
 * @param jsonString string containing JSON results
 * @return string HTML formatted data
 **/
function displayResults(jsonString) {
	var htmlResults = "";
	var allResults = JSON.parse(jsonString);
	// FIXME: foreach
	for(result in allResults) {
		htmlResults = htmlResults + "<tr><td>";
		htmlResults = htmlResults + result.name + "</td>";
		htmlResults = htmlResults + result.banners + "</td>";
		htmlResults = htmlResults + result.actors + "</td>";
		htmlResults = htmlResults + result.plot + "</td>";
		htmlResults = htmlResults + result.stream + "</td>";

	}

	return(htmlResults);
}

var json = "[{\"id\":\"16974\",\"name\":\"Team America: World Police\",\"plot\":\"\",\"imdbId\":\"0372588\",\"actors\":[\"Trey Parker\",\"Matt Stone\",\"Kristen Miller\",\"Masasa\",\"Daran Norris\"],\"banners\":[\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\",\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\",\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\",\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\"],\"stream\":\"http://www.canistream.it/external/imdb/0372588?l=default\"},{\"id\":3989,\"name\":\"Team America: World Police\",\"imdbId\":\"tt0372588\",\"plot\":\"Team America World Police follows an international police force dedicated to maintaining global stability. Learning that dictator Kim Jong il is out to destroy the world, the team recruits Broadway star Gary Johnston to go undercover. With the help of Team America, Gary manages to uncover the plan to destroy the world. Will Team America be able to save it in time? It stars? Samuel L Jackson, Tim Robbins, Sean Penn, Michael Moore, Helen Hunt, Matt Damon, Susan Sarandon, George Clooney, Danny Glover, " + "Ethan Hawke, Alec Baldwin? or does it?\",\"banners\":[\"/crkPGNm0M19LHp8I5mhs75EqhSH.jpg\"],\"stream\":\"http://www.canistream.it/external/imdb/tt0372588?l=default\",\"actors\":[\"Trey Parker\",\"Matt Stone\",\"Kristen Miller\",\"Masasa Moyo\",\"Daran Norris\",\"Maurice LaMarche\"]}]";

document.getElementById("resultsData").innerHTML = displayResults(json);
