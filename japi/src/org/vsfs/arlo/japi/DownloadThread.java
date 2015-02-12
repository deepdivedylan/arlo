package org.vsfs.arlo.japi;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.IOException;

import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;

import javax.net.ssl.HttpsURLConnection;

/**
 * Single Thread to download from an HTTP/HTTPS Connection
 *
 * This is a generic thread to retrieve data from an HTTP/HTTPS GET connection. If an error occurs, it is stored.
 **/
public class DownloadThread implements Runnable {

	private String json = null;
	private String error = null;
	private URL url = null;

	/**
	 * constructor for a thread to download JSON data
	 *
	 * @param newUrl URL to download from
	 * @throws IOException if the URL cannot be parsed
	 **/
	public DownloadThread(String newUrl) throws IOException {
		try {
			setUrl(newUrl);
		} catch(IOException io) {
			throw(new IOException(io.getMessage(), io));
		}
	}

	/**
	 * constructor for a thread to download JSON data
	 *
	 * @param newUrl pre-parsed URL to download from
	 **/
	public DownloadThread(URL newUrl) {
		setUrl(newUrl);
	}

	/**
	 * accessor method for JSON data
	 *
	 * @return value of JSON data
	 **/
	public String getJSON() {
		return(json);
	}

	/**
	 * accessor method for error message
	 *
	 * @return value of error message
	 **/
	public String getError() {
		return(error);
	}

	/**
	 * accessor method for URL
	 *
	 * @return value of URL
	 **/
	public URL getUrl() {
		return(url);
	}

	/**
	 * mutator method for URL
	 *
	 * @param newUrl new value of URL
	 * @throws IOException if the URL cannot be parsed
	 **/
	public void setUrl(String newUrl) throws IOException {
		try {
			url = new URL(newUrl);
		} catch(MalformedURLException malformedUrl) {
			throw(new IOException(malformedUrl.getMessage(), malformedUrl));
		}
	}

	/**
	 * mutator method for pre-parsed URL
	 *
	 * @param newUrl new value of URL
	 **/
	public void setUrl(URL newUrl) {
		url = newUrl;
	}

	/**
	 * main method for the DownloadThread
	 **/
	@Override
	public void run() {
		HttpURLConnection connection = null;
		try {
			if(url.getProtocol() == "https") {
				connection = (HttpsURLConnection) url.openConnection();
			} else {
				connection = (HttpURLConnection) url.openConnection();
			}
		} catch(IOException io) {
			error = "Unable to open connection: " + io.getMessage();
		}

		BufferedReader input = null;
		try {
			input = new BufferedReader(new InputStreamReader(connection.getInputStream()));
		} catch(IOException io) {
			error = "Unable to open input stream: " + io.getMessage();
		}

		String inputLine;
		StringBuffer response = new StringBuffer();
		try {
			while ((inputLine = input.readLine()) != null) {
				response.append(inputLine);
			}
			input.close();
		} catch(IOException io) {
			error = "Unable to read JSON data: " + io.getMessage();
		}

		json = response.toString();
	}
}