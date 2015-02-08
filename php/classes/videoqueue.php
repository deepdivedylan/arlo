<?php
/**
 * This class represents a queue's videos. This is a weak entity (the strong entities are video
 * and queue).
 *
 * @author <alonso@hermesdevelopment.com>
 */
class VideoQueue {

	/**
	 * @var int $videoId the id of the video. Foreign Key to the video entity
	 */
	private $videoId;

	/**
	 * @var int $queueId the id of the queue. Foreign Key to the queue entity
	 */
	private $queueId;
	/**
	 * constructor of this videoQueue
	 *
	 * @param int $newVideoId id of the video
	 * @param int $newQueueId id of the queue
	 * @throws InvalidArgumentException if data types are not valid
	 * @throws RangeException if data values are out of bounds
	 */
	public function __construct($newVideoId, $newQueueId) {
		try {
			$this->setVideoId($newVideoId);
			$this->setQueueId($newQueueId);
		} catch(InvalidArgumentException $invalidArgument) {
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			throw(new RangeException($range->getMessage(), 0, $range));
		}
	}

	/**
	 * accessor for the video id
	 *
	 * @return int value for the video id
	 */
	public function getVideoId() {
		return $this->videoId;
	}

	/**
	 * mutator for the video id
	 *
	 * @param int $newVideoId for the video id
	 * @throws InvalidArgumentException if data types are not valid
	 * @throws RangeException if $newVideoId is less than 0
	 */
	public function setVideoId($newVideoId) {
		$newVideoId = filter_var($newVideoId, FILTER_VALIDATE_INT);
		if($newVideoId === false) {
			throw(new InvalidArgumentException("video id is not a valid integer"));
		}

		if($newVideoId <= 0) {
			throw(new RangeException("video id must be positive"));
		}

		$this->videoId = intval($newVideoId);
	}

	/**
	 * accessor for the queue id
	 *
	 * @return int value for the queue id
	 */
	public function getQueueId() {
		return $this->queueId;
	}

	/**
	 * mutator for the queue id
	 *
	 * @param int $newQueueId for the queue id
	 * @throws InvalidArgumentException if data types are not valid
	 * @throws RangeException if $newQueueId is less than 0
	 */
	public function setQueueId($newQueueId) {
		$newQueueId = filter_var($newQueueId, FILTER_VALIDATE_INT);
		if($newQueueId === false) {
			throw(new InvalidArgumentException("queue id is not a valid integer"));
		}

		if($newQueueId <= 0) {
			throw(new RangeException("queue id must be positive"));
		}

		$this->queueId = intval($newQueueId);
	}

	/**
	 * insert this videoQueue into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 */
	public function insert(&$mysqli) {
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		$query	 = "INSERT INTO videoQueue(videoId, queueId) VALUES(?, ?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		$wasClean	  = $statement->bind_param("ii", $this->videoId, $this->queueId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement: " . $statement->error));
		}

		$statement->close();
	}
	/**
	 * deletes this videoQueue from mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce that the video id and queue id is not null (i.e., don't delete a videoQueue that hasn't been inserted)
		if($this->videoId === null || $this->queueId === null) {
			throw(new mysqli_sql_exception("unable to delete a queue video that does not exist"));
		}

		// create query template
		$query	 = "DELETE FROM videoQueue WHERE videoId = ? AND queueId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("ii", $this->videoId, $this->queueId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement: " . $statement->error));
		}

		// clean up the statement
		$statement->close();
	}
	/**
	 * get the video queue by the video id and queue id
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 */
	public function getVideoQueueByVideoIdAndQueueId(&$mysqli, $videoId, $queueId) {
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		$videoId = filter_var($videoId, FILTER_VALIDATE_INT);
		if($videoId === false) {
			throw(new mysqli_sql_exception("video id is not an integer"));
		}
		if($videoId <= 0) {
			throw(new mysqli_sql_exception("video id is not positive"));
		}

		$queueId = filter_var($queueId, FILTER_VALIDATE_INT);
		if($queueId === false) {
			throw(new mysqli_sql_exception("queue id is not an integer"));
		}
		if($queueId <= 0) {
			throw(new mysqli_sql_exception("queue id is not positive"));
		}

		$query	 = "SELECT videoId, queueId FROM videoQueue WHERE videoId = ? AND queueId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		$wasClean = $statement->bind_param("ii", $videoId, $queueId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement: " . $statement->error));
		}

		$result = $statement->get_result();
		if($result === false) {
			throw(new mysqli_sql_exception("unable to get result set"));
		}

		try {
			$videoQueue = null;
			$row   = $result->fetch_assoc();
			if($row !== null) {
				$videoQueue	= new VideoQueue($row["videoId"], $row["queueId"]);
			}
		} catch(Exception $exception) {
			throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
		}

		$result->free();
		$statement->close();
		return($videoQueue);
	}
	/**
	 * gets all videoQueue
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @return mixed array of videoQueue found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getAllVideoQueues(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// create query template
		$query	 = "SELECT videoId, queueId FROM videoQueue";
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

		// build an array of videoQueue
		$videoQueues = array();
		while(($row = $result->fetch_assoc()) !== null) {
			try {
				$videoQueue	= new VideoQueue($row["videoId"], $row["queueId"]);
				$videoQueues[] = $videoQueue;
			}
			catch(Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
			}
		}

		// count the results in the array and return:
		// 1) null if 0 results
		// 2) the entire array if >= 1 result
		$numberOfVideoQueues = count($videoQueues);
		if($numberOfVideoQueues === 0) {
			return(null);
		} else {
			return($videoQueues);
		}
	}
}
?>