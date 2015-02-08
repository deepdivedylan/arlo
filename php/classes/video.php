<?php
/**
 * This class represents a video a user could search for and add to a queue.
 *
 * @author Alonso Indacochea <alonso@hermesdevelopment.com>
 **/

class Video {
	/**
	 * id for the video, this is the primary key
	 */
	private $videoId;

	/**
	 * video comment of the video
	 **/
	private $videoComment;

	/**
	 * constructor for this video class
	 *
	 * @param mixed $newVideoId id of the video
	 * @param string $newVideoComment video comment of the video or null if none provided
	 * @throws InvalidArgumentException it data types are not valid
	 * @throws RangeException if data values are out of bounds (e.g. strings too long, negative integers)
	 **/
	public function __construct($newVideoId, $newVideoComment = null) {
		try {
			$this->setVideoId($newVideoId);
			$this->setVideoComment($newVideoComment);
		} catch(InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			// rethrow the exception to the caller
			throw(new RangeException($range->getMessage(), 0, $range));
		}
	}
	/**
	 * accessor method for the video Id
	 *
	 * @return mixed value of videoId
	 **/
	public function getVideoId() {
		return ($this->videoId);
	}

	/**
	 * mutator method for videoId
	 *
	 * @param mixed $newVideoId new value of $videoId
	 * @throws InvalidArgumentException if the $newVideoId is not an integer
	 * @throws RangeException if the $newVideoId is not positive
	 **/
	public function setVideoId($newVideoId) {
		// base case: if the video id is null, this a new video without a mySQL assigned id (yet)
		if($newVideoId === null) {
			$this->videoId = null;
			return;
		}
		// verify the video id is valid
		$newVideoId = filter_var($newVideoId, FILTER_VALIDATE_INT);
		if($newVideoId === false) {
			throw(new InvalidArgumentException("video id is not a valid integer"));
		}
		// verify the video id is positive
		if($newVideoId <= 0) {
			throw(new RangeException("video id is not positive"));
		}
		// convert and store the video id
		$this->videoId = intval($newVideoId);
	}
	/**
	 * accessor method for video comment of the video
	 *
	 * @return string value of video comment
	 **/
	public function getVideoComment() {
		return ($this->videoComment);
	}

	/**
	 * mutator method for video comment of the video
	 *
	 * @param string $newVideoComment new value of video comment
	 * @throws InvalidArgumentException if $newVideoComment is not a string or insecure
	 **/
	public function setVideoComment($newVideoComment) {
		// verify that the video comment is secure
		$newVideoComment = trim($newVideoComment);
		$newVideoComment = filter_var($newVideoComment, FILTER_SANITIZE_STRING);
		if(empty($newVideoComment) === true) {
			throw(new InvalidArgumentException("video comment is empty or insecure"));
		}

		// store the profile image path
		$this->videoComment = $newVideoComment;
	}

	/**
	 * inserts this video into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function insert(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the videoId is null (i.e., don't insert a video that already exists)
		if($this->videoId !== null) {
			throw(new mysqli_sql_exception("this video already exists"));
		}
		// create query template
		$query = "INSERT INTO video (videoComment) VALUES (?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("s", $this->videoComment);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters:"));
		}
		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}
		// update the null videoId with what mysql just gave us
		$this->videoId = $mysqli->insert_id;
		// clean up the statement
		$statement->close();
	}

	/**
	 * deletes this video from mysql
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the videoId is not null (i.e., don't delete a video that has not been inserted)
		if($this->videoId === null) {
			throw(new mysqli_sql_exception("unable to delete a video that does not exist"));
		}
		// create query template
		$query = "DELETE FROM video WHERE videoId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holder in the template
		$wasClean = $statement->bind_param("i", $this->videoId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}
		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}
		// clean up the statement
		$statement->close();
	}

	/**
	 * updates the video in mySQL
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public function update(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the videoId is not null (i.e., don't update a video that hasn't been inserted)
		if($this->videoId === null) {
			throw(new mysqli_sql_exception("unable to update a video that does not exist"));
		}
		// create a query template
		$query = "UPDATE video SET videoComment = ? WHERE videoId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("si", $this->videoComment, $this->videoId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}
		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mysql statement: " . $statement->error));
		}
		// clean up the statement
		$statement->close();
	}

	/**
	 * gets the video by videoId
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @param int $videoId video id to search for
	 * @return mixed video found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getVideoByVideoId (&$mysqli, $videoId) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// sanitize the videoId before searching
		$videoId = filter_var($videoId, FILTER_VALIDATE_INT);
		if($videoId === false) {
			throw(new mysqli_sql_exception("video id is not an integer"));
		}
		if($videoId <= 0) {
			throw(new mysqli_sql_exception("video id is not positive"));
		}

		// create query template
		$query = "SELECT videoId, videoComment FROM video WHERE videoId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the video id to the place holder in the template
		$wasClean = $statement->bind_param("i", $videoId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement: " . $statement->error));
		}

		// get result from the SELECT query
		$result = $statement->get_result();
		if($result === false) {
			throw(new mysqli_sql_exception("unable to get result set"));
		}

		// grab the video from mySQL
		try {
			$video = null;
			$row = $result->fetch_assoc();
			if($row !== null) {
				$video = new Video($row["videoId"], $row["videoComment"]);
			}
		} catch(Exception $exception) {
			// if the row couldn't be converted, rethrow it
			throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
		}

		// free up memory and return the result
		$result->free();
		$statement->close();
		return ($video);
	}
	/**
	 * gets all videos
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @return mixed array of videos found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getAllVideos(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// create query template
		$query	 = "SELECT videoId, videoComment FROM video";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement: " . $statement->error));
		}

		// get result from the SELECT query
		$result = $statement->get_result();
		if($result === false) {
			throw(new mysqli_sql_exception("unable to get result set"));
		}

		// build an array of video
		$videos = array();
		while(($row = $result->fetch_assoc()) !== null) {
			try {
				$video	= new Video($row["videoId"], $row["videoComment"]);
				$videos[] = $video;
			}
			catch(Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
			}
		}
		// count the results in the array and return:
		// 1) null if 0 results
		// 2) the entire array if >= 1 result
		$numberOfVideos = count($videos);
		if($numberOfVideos === 0) {
			return(null);
		} else {
			return($videos);
		}
	}
}
?>