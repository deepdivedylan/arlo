<?php
require_once('/usr/lib/php5/simpletest/autorun.php');
require_once ("../php/classes/profile.php");
require_once("../lib/encrypted-config.php");
/**
 *
 * Unit test for the profile class
 *
 * This is a SimpleTest test case for the CRUD methods of the profile class.
 *
 * @see profile
 * @author James Mistalski <james.mistalski@gmail.com>
 **/

// the ProfileTest is a container for all our tests

class ProfileTest extends UnitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 **/
	private $mysqli = null;

	/**
	 * first instance of the object we are testing with
	 **/
	private $profile1 = null;

	/**
	 * second instance of the object we are testing with
	 **/
	private $profile2 = null;

	/**
	 * users first name
	 **/
	private $email = "billy@billy.com";

	/**
	 * seller's image path
	 **/
	private $imagePath = "clown.jpg";

	/**
	 * sets up the mySQL connection for this test
	 **/
	public function setUp() {
		// get the credentials information from the server
		$configFile = "/etc/apache2/arlo.ini";
		$configArray = readConfig($configFile);
		// connection
		mysqli_report(MYSQLI_REPORT_STRICT);
		$this->mysqli = new mysqli($configArray["hostname"], $configArray["username"], $configArray["password"],
			$configArray["database"]);
		// create instance of first profile
		$this->profile1 = new Profile(null, $this->email, $this->imagePath);

		$this->profile2 = new Profile(null, 'john@john.com', $this->imagePath);
	}

	/**
	 * tears down the connection to mySQL and deletes the test instance object
	 **/
	public function tearDown() {
//	echo '<br>tearDown start<br>';
		// destroy the object if it was created
		if($this->profile1 !== null && $this->profile1->getProfileId() !== null) {
			$this->profile1->delete($this->mysqli);
		}
		$this->profile1 = null;

		if($this->profile2 !== null && $this->profile2->getProfileId() !== null) {
			$this->profile2->delete($this->mysqli);
		}
		$this->profile2 = null;

		// disconnect from mySQL
		if($this->mysqli !== null) {
			$this->mysqli->close();
			$this->mysqli = null;
		}
	}
	/**
	 * test inserting a valid Profile into mySQL
	 **/
	public function testInsertValidProfile() {
		// zeroth, ensure the Profile and mySQL class are sane
		$this->assertNotNull($this->profile1);
		$this->assertNotNull($this->mysqli);

		// first, insert the Profile into mySQL
		$this->profile1->insert($this->mysqli);

		// second, grab a Profile from mySQL
		$mysqlProfile = Profile::getProfileByProfileId($this->mysqli, $this->profile1->getProfileId());

		// third, assert the Profile we have created and mySQL's Profile are the same object
		$this->assertIdentical($this->profile1->getProfileId(), $mysqlProfile->getProfileId());
		$this->assertIdentical($this->profile1->getEmail(), $mysqlProfile->getEmail());
		$this->assertIdentical($this->profile1->getImagePath(), $mysqlProfile->getImagePath());
	}

	/**
	 * test inserting an invalid Profile into mySQL
	 **/
	public function testInsertInvalidProfile() {
		// zeroth, ensure the Profile and mySQL class are sane
		$this->assertNotNull($this->profile1);
		$this->assertNotNull($this->mysqli);

		// first, set the profile id to an invented value that should never insert in the first place
		$this->profile1->setProfileId(1042);

		// second, try to insert the Profile and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->profile1->insert($this->mysqli);

		// third, set the Profile to null to prevent tearDown() from deleting a Profile that never existed
		$this->profile1 = null;
	}

	/**
	 * test deleting a Profile from mySQL
	 **/
	public function testDeleteValidProfile() {
		// zeroth, ensure the Profile and mySQL class are sane
		$this->assertNotNull($this->profile1);
		$this->assertNotNull($this->mysqli);

		// first, assert the Profile is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->profile1->insert($this->mysqli);
		$mysqlProfile = Profile::getProfileByProfileId($this->mysqli, $this->profile1->getProfileId());
		$this->assertIdentical($this->profile1->getProfileId(), $mysqlProfile->getProfileId());

		// second, delete the Profile from mySQL and re-grab it from mySQL and assert it does not exist
		$this->profile1->delete($this->mysqli);
		$mysqlProfile = Profile::getProfileByProfileId($this->mysqli, $this->profile1->getProfileId());
		$this->assertNull($mysqlProfile);

		// third, set the Profile to null to prevent tearDown() from deleting a Profile that has already been deleted
		$this->profile1 = null;
	}

	/**
	 * test deleting a non existent Profile from mySQL
	 **/
	public function testDeleteInvalidProfile() {
		// zeroth, ensure the Profile and mySQL class are sane
		$this->assertNotNull($this->profile1);
		$this->assertNotNull($this->mysqli);

		// first, try to delete the Profile before inserting it and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->profile1->delete($this->mysqli);

		// second, set the Profile to null to prevent tearDown() from deleting a Profile that has already been deleted
		$this->profile1 = null;
	}

	/**
	 * test updating a Profile from mySQL
	 **/
	public function testUpdateValidProfile() {
		// zeroth, ensure the Profile and mySQL class are sane
		$this->assertNotNull($this->profile1);
		$this->assertNotNull($this->mysqli);

		// first, assert the Profile is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->profile1->insert($this->mysqli);
		$mysqlProfile = Profile::getProfileByProfileId($this->mysqli, $this->profile1->getProfileId());
		$this->assertIdentical($this->profile1->getProfileId(), $mysqlProfile->getProfileId());

		// second, change the Profile, update it mySQL
		$newImagePath = "joblo.jpg";
		$this->profile1->setImagePath($newImagePath);
		$this->profile1->update($this->mysqli);

		// third, re-grab the Profile from mySQL
		$mysqlProfile = Profile::getProfileByProfileId($this->mysqli, $this->profile1->getProfileId());
		$this->assertNotNull($mysqlProfile);

		// fourth, assert the Profile we have updated and mySQL's Profile are the same object
		$this->assertIdentical($this->profile1->getProfileId(), $mysqlProfile->getProfileId());
		$this->assertIdentical($this->profile1->getEmail(), $mysqlProfile->getEmail());
		$this->assertIdentical($this->profile1->getImagePath(), $mysqlProfile->getImagePath());
	}

	/**
	 * test updating a non existent Profile from mySQL
	 **/
	public function testUpdateInvalidProfile() {
		// zeroth, ensure the Profile and mySQL class are sane
		$this->assertNotNull($this->profile1);
		$this->assertNotNull($this->mysqli);

		// first, try to update the Profile before inserting it and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->profile1->update($this->mysqli);

		// second, set the Profile to null to prevent tearDown() from deleting a Profile that has already been deleted
		$this->profile1 = null;
	}
	/**
	 *test getting a valid profile by profileId
	 **/
	public function testGetValidProfileByProfileId() {
		$this->assertNotNull($this->profile1);
		$this->assertNotNull($this->mysqli);

		// first, assert the Profile is inserted into mySQL by grabbing it and asserting the primary key
		$this->profile1->insert($this->mysqli);
		$mysqlProfile = Profile::getProfileByProfileId($this->mysqli, $this->profile1->getProfileId());
		$this->assertIdentical($this->profile1->getProfileId(), $mysqlProfile->getProfileId());
	}

	/**
	 * test getting a valid profile by using an invalid profileId
	 **/
	public function testGetInvalidProfileByProfileId() {
		// first, assert the mySQL class is sane
		$this->assertNotNull($this->mysqli);

		// grab a Profile that could never exist
		$mysqlProfile = Profile::getProfileByProfileId($this->mysqli, 12);
		$this->assertNull($mysqlProfile);
	}
}
?>