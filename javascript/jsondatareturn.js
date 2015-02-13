/**
 * takes in JSON results and returns formatted HTML
 *
 * @param jsonString string containing JSON results
 * @return string HTML formatted data
 **/
function displayResults(jsonString) {
	var htmlResults = "";
	var allResults = JSON.parse(jsonString);
	for(var i = 0; i < allResults.length; i++) {
		htmlResults = htmlResults + "<tr><td>";
		htmlResults = htmlResults + allResults[i].name + "</td>";
		// htmlResults = htmlResults + "<td>" + allResults[i].banners + "</td>";
		htmlResults = htmlResults + "<td>" + allResults[i].actors + "</td>";
		htmlResults = htmlResults + "<td>" + allResults[i].plot + "</td>";
		htmlResults = htmlResults + "<td><a href=\"" + allResults[i].stream + "\">Stream It!</a></td></tr>";

	}

	return(htmlResults);
}


//var json = "[{\"id\":\"16974\",\"name\":\"Team America: World Police\",\"plot\":\"\",\"imdbId\":\"0372588\",\"actors\":[\"Trey Parker\",\"Matt Stone\",\"Kristen Miller\",\"Masasa\",\"Daran Norris\"],\"banners\":[\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\",\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\",\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\",\"http://content6.flixster.com/movie/10/92/10/10921012_tmb.jpg\"],\"stream\":\"http://www.canistream.it/external/imdb/0372588?l=default\"},{\"id\":3989,\"name\":\"Team America: World Police\",\"imdbId\":\"tt0372588\",\"plot\":\"Team America World Police follows an international police force dedicated to maintaining global stability. Learning that dictator Kim Jong il is out to destroy the world, the team recruits Broadway star Gary Johnston to go undercover. With the help of Team America, Gary manages to uncover the plan to destroy the world. Will Team America be able to save it in time? It stars? Samuel L Jackson, Tim Robbins, Sean Penn, Michael Moore, Helen Hunt, Matt Damon, Susan Sarandon, George Clooney, Danny Glover, " + "Ethan Hawke, Alec Baldwin? or does it?\",\"banners\":[\"/crkPGNm0M19LHp8I5mhs75EqhSH.jpg\"],\"stream\":\"http://www.canistream.it/external/imdb/tt0372588?l=default\",\"actors\":[\"Trey Parker\",\"Matt Stone\",\"Kristen Miller\",\"Masasa Moyo\",\"Daran Norris\",\"Maurice LaMarche\"]}]";
//
//function sinaloa() {
//	document.getElementById("resultsData").innerHTML = displayResults(json);
//}