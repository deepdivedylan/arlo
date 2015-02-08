<?php
/**
 * This class represents a profile's queues. This is a weak entity (the strong entities are profile
 * and queue).
 *
 * @author <alonso@hermesdevelopment.com>
 */
class ProfileQueue {

	/**
	 * @var int $profileId the id of the profile. Foreign Key to the profile entity
	 */
	private $profileId;

	/**
	 * @var int $queueId the id of the queue. Foreign Key to the queue entity
	 */
	private $queueId;
	/**
	 * constructor of this profileQueue
	 *
	 * @param int $newProfileId id of the profile
	 * @param int $newQueueId id of the queue
	 * @throws InvalidArgumentException if data types are not valid
	 * @throws RangeException if data values are out of bounds
	 */
	public function __construct($newProfileId, $newQueueId) {
		try {
			$this->setProfileId($newProfileId);
			$this->setQueueId($newQueueId);
		} catch(InvalidArgumentException $invalidArgument) {
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			throw(new RangeException($range->getMessage(), 0, $range));
		}
	}

	/**
	 * accessor for the profile id
	 *
	 * @return int value for the profile id
	 */
	public function getProfileId() {
		return $this->profileId;
	}

	/**
	 * mutator for the profile id
	 *
	 * @param int $newProfileId for the profile id
	 * @throws InvalidArgumentException if data types are not valid
	 * @throws RangeException if $newProfileId is less than 0
	 */
	public function setProfileId($newProfileId) {
		$newProfileId = filter_var($newProfileId, FILTER_VALIDATE_INT);
		if($newProfileId === false) {
			throw(new InvalidArgumentException("profile id is not a valid integer"));
		}

		if($newProfileId <= 0) {
			throw(new RangeException("profile id must be positive"));
		}

		$this->profileId = intval($newProfileId);
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
	 * insert this profileQueue into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 */
	public function insert(&$mysqli) {
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		$query	 = "INSERT INTO profileQueue(profileId, queueId) VALUES(?, ?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		$wasClean	  = $statement->bind_param("ii", $this->profileId, $this->queueId);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}

		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement: " . $statement->error));
		}

		$statement->close();
	}
	/**
	 * deletes this profileQueue from mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// enforce that the profile id and queue id is not null (i.e., don't delete a profileQueue that hasn't been inserted)
		if($this->profileId === null || $this->queueId === null) {
			throw(new mysqli_sql_exception("unable to delete a profile queue that does not exist"));
		}

		// create query template
		$query	 = "DELETE FROM profileQueue WHERE profileId = ? AND queueId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("ii", $this->profileId, $this->queueId);
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
	 * get the profile queue by the profile id and queue id
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 */
	public function getProfileQueueByProfileIdAndQueueId(&$mysqli, $profileId, $queueId) {
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		$profileId = filter_var($profileId, FILTER_VALIDATE_INT);
		if($profileId === false) {
			throw(new mysqli_sql_exception("profile id is not an integer"));
		}
		if($profileId <= 0) {
			throw(new mysqli_sql_exception("profile id is not positive"));
		}

		$queueId = filter_var($queueId, FILTER_VALIDATE_INT);
		if($queueId === false) {
			throw(new mysqli_sql_exception("queue id is not an integer"));
		}
		if($queueId <= 0) {
			throw(new mysqli_sql_exception("queue id is not positive"));
		}

		$query	 = "SELECT profileId, queueId FROM profileQueue WHERE profileId = ? AND queueId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		$wasClean = $statement->bind_param("ii", $profileId, $queueId);
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
			$profileQueue = null;
			$row   = $result->fetch_assoc();
			if($row !== null) {
				$profileQueue	= new ProfileQueue($row["profileId"], $row["queueId"]);
			}
		} catch(Exception $exception) {
			throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
		}

		$result->free();
		$statement->close();
		return($profileQueue);
	}
	/**
	 * gets all Profile Queues
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @return mixed array of Profile Queues found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getAllProfileQueues(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// create query template
		$query	 = "SELECT profileId, queueId FROM profileQueue";
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

		// build an array of profileQueue
		$profileQueues = array();
		while(($row = $result->fetch_assoc()) !== null) {
			try {
				$profileQueue	= new ProfileQueue($row["profileId"], $row["queueId"]);
				$profileQueues[] = $profileQueue;
			}
			catch(Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
			}
		}

		// count the results in the array and return:
		// 1) null if 0 results
		// 2) the entire array if >= 1 result
		$numberOfProfileQueues = count($profileQueues);
		if($numberOfProfileQueues === 0) {
			return(null);
		} else {
			return($profileQueues);
		}
	}
}
?>