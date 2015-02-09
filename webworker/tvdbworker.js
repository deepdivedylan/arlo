/**
 * performs a GET AJAX query without the use of jQuery
 *
 * @param url URL to GET, including parameters
 **/
function nonjQueryAjax(url) {
	// create an AJAX object (incompatible with older versions of IE - oh well! :D)
	var ajax;
	ajax = new XMLHttpRequest();

	// when the data is received, send a message back to the caller
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4 ) {
			if(ajax.status == 200) {
				postMessage({"index": 2, "data": ajax.responseText});
			}
		}
	}

	// send the message and wait for the state to change
	ajax.open("GET", url, true);
	ajax.send();
}

// receive the search parameter and make the AJAX call
self.addEventListener("message", function(event) {
	var search = event.data;
	nonjQueryAjax("/thetvdb/?search=" + search);
}, false);