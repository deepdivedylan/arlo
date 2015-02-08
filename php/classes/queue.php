<?php
/**
 * This class represents a queue a user would generate. A user can have many queues containing many videos.
 *
 * @author Alonso Indacochea <alonso@hermesdevelopment.com>
 **/

class Queue {
	/**
	 * id for the queue, this is the primary key
	 */
	private $queueId;

	/**
	 * creationDate of the queue
	 **/
	private $creationDate;

	/**
	 * constructor for this queue class
	 *
	 * @param mixed $newQueueId id of the queue
	 * @param string $newCreationDate creationDate of the queue
	 * @throws InvalidArgumentException it data types are not valid
	 * @throws RangeException if data values are out of bounds (e.g. strings too long, negative integers)
	 **/
	public function __construct($newQueueId, $newCreationDate = null) {
		try {
			$this->setQueueId($newQueueId);
			$this->setCreationDate($newCreationDate);
		} catch(InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			// rethrow the exception to the caller
			throw(new RangeException($range->getMessage(), 0, $range));
		}
	}
	/**
	 * accessor method for the queue Id
	 *
	 * @return mixed value of queueId
	 **/
	public function getQueueId() {
		return ($this->queueId);
	}

	/**
	 * mutator method for queueId
	 *
	 * @param mixed $newQueueId new value of $queueId
	 * @throws InvalidArgumentException if the $newQueueId is not an integer
	 * @throws RangeException if the $newQueueId is not positive
	 **/
	public function setQueueId($newQueueId) {
		// base case: if the queue id is null, this a new queue without a mySQL assigned id (yet)
		if($newQueueId === null) {
			$this->queueId = null;
			return;
		}
		// verify the queue id is valid
		$newQueueId = filter_var($newQueueId, FILTER_VALIDATE_INT);
		if($newQueueId === false) {
			throw(new InvalidArgumentException("queue id is not a valid integer"));
		}
		// verify the queue id is positive
		if($newQueueId <= 0) {
			throw(new RangeException("queue id is not positive"));
		}
		// convert and store the queue id
		$this->queueId = intval($newQueueId);
	}
	/**
	 * accessor method for creation date of queue
	 *
	 * @return DateTime value of creation date
	 **/
	public function getCreationDate() {
		return ($this->creationDate);
	}

	/**
	 * mutator method for creation date
	 *
	 * @param mixed $newCreationDate creation date as a DateTime object or string (or null to load the current time)
	 * @throws InvalidArgumentException if $newCreationDate is not a valid object or string
	 * @throws RangeException if $newCreationDate is a date that does not exist
	 **/
	public function setCreationDate($newCreationDate) {
		// base case: if the date is null, use the current date and time
		if($newCreationDate === null) {
			$this->creationDate = new DateTime();
			return;
		}

		// base case: if the date is a DateTime object, there's no work to be done
		if(is_object($newCreationDate) === true && get_class($newCreationDate) === "DateTime") {
			$this->creationDate = $newCreationDate;
			return;
		}

		// treat the date as a mySQL date string: Y-m-d H:i:s
		$newCreationDate = trim($newCreationDate);
		if((preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $newCreationDate, $matches)) !== 1) {
			throw(new InvalidArgumentException("creation date is not a valid date"));
		}

		// verify the date is really a valid calendar date
		$year = intval($matches[1]);
		$month = intval($matches[2]);
		$day = intval($matches[3]);
		$hour = intval($matches[4]);
		$minute = intval($matches[5]);
		$second = intval($matches[6]);
		if(checkdate($month, $day, $year) === false) {
			throw(new RangeException("creation date $newCreationDate is not a Gregorian date"));
		}

		// verify the time is really a valid wall clock time
		if($hour < 0 || $hour >= 24 || $minute < 0 || $minute >= 60 || $second < 0 || $second >= 60) {
			throw(new RangeException("creation date $newCreationDate is not a valid time"));
		}

		// store the creation date
		$newCreationDate = DateTime::createFromFormat("Y-m-d H:i:s", $newCreationDate);
		$this->creationDate = $newCreationDate;
	}

	/**
	 * inserts this queue into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function insert(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the queueId is null (i.e., don't insert a queue that already exists)
		if($this->queueId !== null) {
			throw(new mysqli_sql_exception("this queue already exists"));
		}
		// create query template
		$query = "INSERT INTO queue (creationDate) VALUES (?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holders in the template
		$formattedDate = $this->creationDate->format("Y-m-d H:i:s");
		$wasClean = $statement->bind_param("s", $formattedDate);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters:"));
		}
		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement"));
		}
		// update the null queueId with what mysql just gave us
		$this->queueId = $mysqli->insert_id;
		// clean up the statement
		$statement->close();
	}

	/**
	 * deletes this queue from mysql
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the queueId is not null (i.e., don't delete a queue that has not been inserted)
		if($this->queueId === null) {
			throw(new mysqli_sql_exception("unable to delete a queue that does not exist"));
		}
		// create query template
		$query = "DELETE FROM queue WHERE queueId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holder in the template
		$wasClean = $statement->bind_param("i", $this->queueId);
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
	 * updates the queue in mySQL
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public function update(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the queueId is not null (i.e., don't update a queue that hasn't been inserted)
		if($this->queueId === null) {
			throw(new mysqli_sql_exception("unable to update a queue that does not exist"));
		}
		// create a query template
		$query = "UPDATE queue SET creationDate = ? WHERE queueId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holders in the template
		$formattedDate = $this->creationDate->format("Y-m-d H:i:s");
		$wasClean = $statement->bind_param("si", $formattedDate, $this->queueId);
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
	 * gets the queue by queueId
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @param int $queueId queue id to search for
	 * @return mixed queue found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getQueueByQueueId (&$mysqli, $queueId) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// sanitize the queueId before searching
		$queueId = filter_var($queueId, FILTER_VALIDATE_INT);
		if($queueId === false) {
			throw(new mysqli_sql_exception("queue id is not an integer"));
		}
		if($queueId <= 0) {
			throw(new mysqli_sql_exception("queue id is not positive"));
		}

		// create query template
		$query = "SELECT queueId, creationDate FROM queue WHERE queueId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the queue id to the place holder in the template
		$wasClean = $statement->bind_param("i", $queueId);
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

		// grab the queue from mySQL
		try {
			$queue = null;
			$row = $result->fetch_assoc();
			if($row !== null) {
				$queue = new Queue($row["queueId"], $row["creationDate"]);
			}
		} catch(Exception $exception) {
			// if the row couldn't be converted, rethrow it
			throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
		}

		// free up memory and return the result
		$result->free();
		$statement->close();
		return ($queue);
	}
	/**
	 * gets all queues
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @return mixed array of Queues found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getAllQueues(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// create query template
		$query	 = "SELECT queueId, creationDate FROM queue";
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

		// build an array of queue
		$queues = array();
		while(($row = $result->fetch_assoc()) !== null) {
			try {
				$queue	= new Queue($row["queueId"], $row["creationDate"]);
				$queues[] = $queue;
			}
			catch(Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
			}
		}
		// count the results in the array and return:
		// 1) null if 0 results
		// 2) the entire array if >= 1 result
		$numberOfQueues = count($queues);
		if($numberOfQueues === 0) {
			return(null);
		} else {
			return($queues);
		}
	}
}
?>