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
class ProfileTest extends unitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 **/
	private $mysqli	= null;

	/**
	 * variable to hold the test database row
	 **/
	private $profile	= null;

	/**
	 * instance of the objects we are testing with
	 **/
	private $email	= "unit-test@example.net";
	private $imagePath = "http://placehold.it/350x150";

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
	}

	/**
	 * tears down the connection to mySQL and deletes the test instance object
	 **/
	public function tearDown() {
		// destroy the objects if they were created
		if($this->profile !== null) {
			$this->profile->delete($this->mysqli);
			$this->profile = null;

			// disconnect from mySQL
			if($this->mysqli !== null) {
				$this->mysqli->close();
				$this->mysqli = null;
			}
		}
	}
	/**
	 * test creating a new Profile and inserting it into mySQL
	 **/
	public function testInsertNewProfile() {
		// zeroth, test mySQL and Profile are sane
		$this->assertNotNull($this->mysqli);
		$this->assertNotNull($this->profile);
		// first, create a profile to post to mySQL
		$this->profile = new Profile(null, $this->email, $this->imagePath);
		// second, insert the profile to mySQL
		$this->profile->insert($this->mysqli);
		// finally, compare the fields
		$this->assertNotNull($this->profile->getProfileId());
		$this->assertTrue($this->profile->getProfileId() > 0);
		$this->assertIdentical($this->profile->getEmail(),			$this->email);
		$this->assertIdentical($this->profile->getImagePath(),	$this->imagePath);
	}

	/**
	 * test updating a Profile in mySQL
	 **/
	

}
?>