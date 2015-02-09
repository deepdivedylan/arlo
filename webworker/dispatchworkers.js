var movieDbWorker;
var rottenTomatoWorker;
var tvDbWorker;
var jsonData = ["", "", ""];

function addToJsonData(event) {
	var index = event.data.index;
	var data  = event.data.data;
	jsonData[index] = data;
}

function dispatchWorkers() {
	if(typeof(Worker) !== "undefined") {
		movieDbWorker = new Worker("moviedbworker.js");
		rottenTomatoWorker = new Worker("rottentomatoworker.js");
		tvDbWorker = new Worker("tvdbworker.js");

		var encodedSearch = encodeURIComponent($("#search").val());
		movieDbWorker.postMessage(encodedSearch);
		rottenTomatoWorker.postMessage(encodedSearch);
		tvDbWorker.postMessage(encodedSearch);

		movieDbWorker.onmessage = addToJsonData;
		rottenTomatoWorker.onmessage = addToJsonData;
		tvDbWorker.onmessage = addToJsonData;
	}
}