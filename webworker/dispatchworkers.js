var joinCounter = 0;
var movieDbWorker;
var rottenTomatoWorker;
var tvDbWorker;
var jsonData = ["", "", ""];

/**
 * event to save JSON data into the JSON array
 *
 * @param event JavaScript event containing the JSON data
 **/
function addToJsonData(event) {
	var index = event.data.index;
	var data  = event.data.data;
	jsonData[index] = data;
}

/**
 * main event to dispatch threads
 **/
function dispatchWorkers() {
	if(typeof(Worker) !== "undefined") {
		// create all threads
		movieDbWorker = new Worker("moviedbworker.js");
		rottenTomatoWorker = new Worker("rottentomatoworker.js");
		tvDbWorker = new Worker("tvdbworker.js");

		// pass the search parameter to all threads
		var encodedSearch = encodeURIComponent($("#search").val());
		movieDbWorker.postMessage(encodedSearch);
		rottenTomatoWorker.postMessage(encodedSearch);
		tvDbWorker.postMessage(encodedSearch);

		// attach the addToJsonData() event to all threads
		movieDbWorker.onmessage = addToJsonData;
		rottenTomatoWorker.onmessage = addToJsonData;
		tvDbWorker.onmessage = addToJsonData;

		// wait (well, spin-lock) all threads
		joinThreads();
	}
}

/**
 * waits (OK, spin-locks) all threads until all are complete or have blocked for 20 seconds
 **/
function joinThreads() {
	if((jsonData[0] === "" || jsonData[1] === "" || jsonData[2] === "") && joinCounter < 20) {
		joinCounter++;
		setTimeout(joinThreads, 1000);
	} else {
		var allObjects = mergeArrays();
	}
}

/**
 * merges the JSON data into a single array, filtering duplicates
 *
 * @returns {Array} array of data results
 **/
function mergeArrays() {
	var allObjects = [];
	var imdbIds = [];

	// traverse the main JSON data
	for(var i = 0; i < jsonData.length; i++) {
		if(jsonData[i] !== "") {
			// convert JSON data into an object
			dataset = $.parseJSON(jsonData[i]);

			// search for the IMDB key as a normalized key and add it to the array
			for(var j = 0; j < dataset.length; j++) {
				var thisImdbId = dataset[j]["imdbId"];
				if(thisImdbId !== null && imdbIds.indexOf(thisImdbId) === -1) {
					allObjects.push(dataset[j]);
					imdbIds.push(thisImdbId);
				}
			}
		}
	}

	return(allObjects);
}