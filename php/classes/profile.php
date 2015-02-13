<?php
/**
 * This class represents a profile a user would sign in with. The profile will contain all the queues
 * the user generates.
 *
 * @author Alonso Indacochea <alonso@hermesdevelopment.com>
 **/

class Profile {
	/**
	 * id for the profile, this is the primary key
	 */
	private $profileId;

	/**
	 * email address of the profile
	 **/
	private $email;

	/**
	 * imagePath for an image user provides
	 **/
	private $imagePath;

	/**
	 * constructor for this profile class
	 *
	 * @param mixed $newProfileId id of the profile
	 * @param string $newEmail email address of the profile
	 * @param string $newImagePath imagePath for an image user provides or null if not provided
	 * @throws InvalidArgumentException it data types are not valid
	 * @throws RangeException if data values are out of bounds (e.g. strings too long, negative integers)
	 **/
	public function __construct($newProfileId, $newEmail, $newImagePath = null) {
		try {
			$this->setProfileId($newProfileId);
			$this->setEmail($newEmail);
			$this->setImagePath($newImagePath);
		} catch(InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(RangeException $range) {
			// rethrow the exception to the caller
			throw(new RangeException($range->getMessage(), 0, $range));
		}
	}
	/**
	 * accessor method for the profile Id
	 *
	 * @return mixed value of profileId
	 **/
	public function getProfileId() {
		return ($this->profileId);
	}

	/**
	 * mutator method for profileId
	 *
	 * @param mixed $newProfileId new value of $profileId
	 * @throws InvalidArgumentException if the $newProfileId is not an integer
	 * @throws RangeException if the $newProfileId is not positive
	 **/
	public function setProfileId($newProfileId) {
		// base case: if the profile id is null, this a new profile without a mySQL assigned id (yet)
		if($newProfileId === null) {
			$this->profileId = null;
			return;
		}
		// verify the profile id is valid
		$newProfileId = filter_var($newProfileId, FILTER_VALIDATE_INT);
		if($newProfileId === false) {
			throw(new InvalidArgumentException("profile id is not a valid integer"));
		}
		// verify the profile id is positive
		if($newProfileId <= 0) {
			throw(new RangeException("profile id is not positive"));
		}
		// convert and store the profile id
		$this->profileId = intval($newProfileId);
	}
	/**
	 * accessor method for profile email address
	 *
	 * @return string value of profile email address
	 **/
	public function getEmail() {
		return ($this->email);
	}

	/**
	 * mutator method for profile email address
	 *
	 * @param string $newEmail new value of profile email address
	 * @throws InvalidArgumentException if $newEmail is not a string or insecure
	 * @throws RangeException if $newEmail is > 100 characters
	 **/
	public function setEmail($newEmail) {
		// verify that the profile email address is secure
		$newEmail = trim($newEmail);
		$newEmail = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
		if(empty($newEmail) === true) {
			throw(new InvalidArgumentException("email address is empty or insecure"));
		}

		// verify the profile email address will fit in the database
		if(strlen($newEmail) > 100) {
			throw(new RangeException("email address too large"));
		}

		// store the profile email address
		$this->email = $newEmail;
	}
	/**
	 * accessor method for profile image path
	 *
	 * @return string value of profile image path
	 **/
	public function getImagePath() {
		return ($this->imagePath);
	}

	/**
	 * mutator method for profile image path
	 *
	 * @param string $newImagePath new value of profile image path
	 * @throws InvalidArgumentException if $newState is not a string or insecure
	 * @throws RangeException if $newState is > 255 characters
	 **/
	public function setImagePath($newImagePath) {

		// verify that the profile image path is secure
		$newImagePath = trim($newImagePath);
		$newImagePath = filter_var($newImagePath, FILTER_SANITIZE_STRING);
		if(empty($newImagePath) === true) {
			throw(new InvalidArgumentException("image path is empty or insecure"));
		}

		// verify the profile image path will fit in the database
		if(strlen($newImagePath) > 255) {
			throw(new RangeException("image path too large"));
		}

		// store the profile image path
		$this->imagePath = $newImagePath;
	}

	/**
	 * inserts this profile into mySQL
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function insert(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the profileId is null (i.e., don't insert a profile that already exists)
		if($this->profileId !== null) {
			throw(new mysqli_sql_exception("not a new profile"));
		}
		// create query template
		$query = "INSERT INTO profile(email, imagePath) VALUES(?, ?)";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("ss", $this->email, $this->imagePath);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("unable to bind parameters"));
		}
		// execute the statement
		if($statement->execute() === false) {
			throw(new mysqli_sql_exception("unable to execute mySQL statement: " . $statement->error));
		}
		// update the null profileId with what mySQL just gave us
		$this->profileId = $mysqli->insert_id;
		// clean up the statement
		$statement->close();
	}

	/**
	 * deletes this profile from mysql
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public function delete(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the profileId is not null (i.e., don't delete a profile that has not been inserted)
		if($this->profileId === null) {
			throw(new mysqli_sql_exception("unable to delete a profile that does not exist"));
		}
		// create query template
		$query = "DELETE FROM profile WHERE profileId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holder in the template
		$wasClean = $statement->bind_param("i", $this->profileId);
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
	 * updates the profile in mySQL
	 *
	 * @param resource $mysqli pointer to mysql connection, by reference
	 * @throws mysqli_sql_exception when mysql related errors occur
	 **/
	public function update(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// enforce the profileId is not null (i.e., don't update a profile that hasn't been inserted)
		if($this->profileId === null) {
			throw(new mysqli_sql_exception("unable to update a profile that does not exist"));
		}
		// create a query template
		$query = "UPDATE profile SET email = ?, imagePath = ? WHERE profileId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the member variables to the place holders in the template
		$wasClean = $statement->bind_param("ssi", $this->email, $this->imagePath, $this->profileId);
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
	 * gets the profile by profileId
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @param int $profileId profile id to search for
	 * @return mixed profile found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getProfileByProfileId(&$mysqli, $profileId) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}
		// sanitize the profileId before searching
		$profileId = filter_var($profileId, FILTER_VALIDATE_INT);
		if($profileId === false) {
			throw(new mysqli_sql_exception("profile id is not an integer"));
		}
		if($profileId <= 0) {
			throw(new mysqli_sql_exception("profile id is not positive"));
		}
		// create query template
		$query = "SELECT profileId, email, imagePath FROM profile WHERE profileId = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}
		// bind the profile content to the place holder in the template
		$wasClean = $statement->bind_param("i", $profileId);
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
		// grab the profile from mySQL
		try {
			$profile = null;
			$row = $result->fetch_assoc();
			if($row !== null) {
				$profile = new Profile($row["profileId"], $row["email"], $row["imagePath"]);
			}
		} catch(Exception $exception) {
			// if the row couldn't be converted, rethrow it
			throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
		}
		// free up memory and return the result
		$result->free();
		$statement->close();
		return($profile);
	}
	/**
	 * gets the profile by email
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @param string $email email address to search for
	 * @return mixed profile found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getProfileByEmail(&$mysqli, $email) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// sanitize the email before searching
		$email = trim($email);
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);

		// create query template
		$query = "SELECT profileId, email, imagePath FROM profile WHERE email = ?";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("unable to prepare statement"));
		}

		// bind the email to the place holder in the template
		$wasClean = $statement->bind_param("s", $email);
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

		// grab the profile from mySQL
		try {
			$profile = null;
			$row = $result->fetch_assoc();
			if($row !== null) {
				$profile = new Profile($row["profileId"], $row["email"], $row["imagePath"]);
			}
		} catch(Exception $exception) {
			// if the row couldn't be converted, rethrow it
			throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
		}

		// free up memory and return the result
		$result->free();
		$statement->close();
		return ($profile);
	}

	/**
	 * gets all profiles
	 *
	 * @param resource $mysqli pointer to mySQL connection, by reference
	 * @return mixed array of Profiles found or null if not found
	 * @throws mysqli_sql_exception when mySQL related errors occur
	 **/
	public static function getAllProfiles(&$mysqli) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("input is not a mysqli object"));
		}

		// create query template
		$query	 = "SELECT profileId, email, imagePath FROM profile";
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

		// build an array of profile
		$profiles = array();
		while(($row = $result->fetch_assoc()) !== null) {
			try {
				$profile	= new Profile($row["profileId"], $row["email"], $row["imagePath"]);
				$profiles[] = $profile;
			}
			catch(Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new mysqli_sql_exception($exception->getMessage(), 0, $exception));
			}
		}
		// count the results in the array and return:
		// 1) null if 0 results
		// 2) the entire array if >= 1 result
		$numberOfProfiles = count($profiles);
		if($numberOfProfiles === 0) {
			return(null);
		} else {
			return($profiles);
		}
	}
}
?>