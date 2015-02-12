package org.vsfs.arlo.japi;

import java.io.IOException;
import java.io.StringReader;
import java.io.StringWriter;
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.util.TreeMap;

import javax.json.Json;
import javax.json.JsonArray;
import javax.json.JsonArrayBuilder;
import javax.json.JsonObject;
import javax.json.JsonReader;
import javax.json.JsonString;
import javax.json.JsonValue;
import javax.json.JsonWriter;

public class Japi {
	public static void main(String[] argv) {
		JsonArray jsonArrays[] = new JsonArray[3];

		// operator error
		if(argv.length != 1) {
			System.err.println("Usage: Japi <keyword>");
			System.exit(1);
		}

		// URL encode the keyword
		String keyword = null;
		try {
			keyword = URLEncoder.encode(argv[0], "UTF-8");
		} catch(UnsupportedEncodingException unsupportedEncoding) {
			System.err.println("Unable to URLEncode: " + unsupportedEncoding.getMessage());
			System.exit(1);
		}

		// setup the runners & timeout
		Long timeout = 20000L;
		DownloadThread rottenTomatoesRunner = null;
		DownloadThread theMovieDbRunner = null;
		DownloadThread theTvDbRunner = null;
		try {
			// setup the URLs
			String rottenTomatoesUrl = "http://arlo.vsfs.org/rottentomatoes/?search=" + keyword;
			String theMovieDbUrl = "http://arlo.vsfs.org/themoviedb/?search=" + keyword;
			String theTvDbUrl = "http://arlo.vsfs.org/thetvdb/?search=" + keyword;

			// create the runners
			rottenTomatoesRunner = new DownloadThread(rottenTomatoesUrl);
			theMovieDbRunner = new DownloadThread(theMovieDbUrl);
			theTvDbRunner = new DownloadThread(theTvDbUrl);

			// create the threads
			Thread rottenTomatoesThread = new Thread(rottenTomatoesRunner);
			Thread theMovieDbThread = new Thread(theMovieDbRunner);
			Thread theTvDbThread = new Thread(theTvDbRunner);

			// start the threads
			Long startTime = System.currentTimeMillis();
			rottenTomatoesThread.start();
			theMovieDbThread.start();
			theTvDbThread.start();

			// wait on the threads to complete
			while(rottenTomatoesThread.isAlive() || theMovieDbThread.isAlive() || theTvDbThread.isAlive()) {
				if(rottenTomatoesThread.isAlive()) {
					rottenTomatoesThread.join(100);
				}
				if(theMovieDbThread.isAlive()) {
					theMovieDbThread.join(100);
				}
				if(theTvDbThread.isAlive()) {
					theTvDbThread.join(100);
				}

				// kill the threads if they've exceeded the timeout
				if(((System.currentTimeMillis() - startTime) > timeout) && (rottenTomatoesThread.isAlive() || theMovieDbThread.isAlive() || theTvDbThread.isAlive())) {
					if(rottenTomatoesThread.isAlive()) {
						rottenTomatoesThread.interrupt();
						rottenTomatoesThread.join();
					}
					if(theMovieDbThread.isAlive()) {
						theMovieDbThread.interrupt();
						theMovieDbThread.join();
					}
					if(theTvDbThread.isAlive()) {
						theTvDbThread.interrupt();
						theTvDbThread.join();
					}
				}
			}
		} catch(InterruptedException interrupted) {
			System.err.println("Thread interrupted: " + interrupted.getMessage());
			System.exit(1);
		} catch(IOException io) {
			System.err.println("Unable to download JSON: " + io.getMessage());
			System.exit(1);
		}

		// store the retrieved JSON into the array
		if(rottenTomatoesRunner.getJSON() != null) {
			JsonReader jsonReader = Json.createReader(new StringReader(rottenTomatoesRunner.getJSON()));
			jsonArrays[0] = jsonReader.readArray();
			jsonReader.close();
		}
		if(theMovieDbRunner.getJSON() != null) {
			JsonReader jsonReader = Json.createReader(new StringReader(theMovieDbRunner.getJSON()));
			jsonArrays[1] = jsonReader.readArray();
			jsonReader.close();
		}
		if(theTvDbRunner.getJSON() != null) {
			JsonReader jsonReader = Json.createReader(new StringReader(theTvDbRunner.getJSON()));
			jsonArrays[2] = jsonReader.readArray();
			jsonReader.close();
		}

		// harvest from all three arrays
		TreeMap<String, JsonObject> jsonObjectMap = new TreeMap<String, JsonObject>();
		for(JsonArray jsonArray : jsonArrays) {
			// for each array...
			for(JsonValue jsonValue : jsonArray) {
				// grab the video object
				if(jsonValue.getValueType() == JsonValue.ValueType.OBJECT) {
					JsonObject jsonObject = (JsonObject) jsonValue;
					try {
						JsonString jsonString = jsonObject.getJsonString("imdbId");
						String imdbId = jsonString.getString();
						if(!imdbId.isEmpty() && !jsonObjectMap.containsKey(imdbId)) {
							jsonObjectMap.put(imdbId, jsonObject);
						}
					} catch(ClassCastException classCast) {
						// ignore it; this only happens when the imdbId does not exist
					}
				}
			}
		}

		// convert the TreeMap to a JSON array
		JsonArrayBuilder arrayBuilder = Json.createArrayBuilder();
		for(JsonObject jsonObject : jsonObjectMap.values()) {
			arrayBuilder.add(jsonObject);
		}
		JsonArray finalArray = arrayBuilder.build();

		// write the array and return the result
		StringWriter stringWriter = new StringWriter();
		JsonWriter jsonWriter = Json.createWriter(stringWriter);
		jsonWriter.writeArray(finalArray);
		jsonWriter.close();
		System.out.println(stringWriter.toString());
	}
}
