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
		// zeroth, verify mySQL connected OK
			// TODO: verify with Dylan and Alonso if wise to assertNotNull for profile as well as in profilequeue-test.php Alonso created
		$this->assertNotNull($this->mysqli);
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
	public function testUpdateProfile() {
		// zeroth, verify mySQL connected Ok
		$this->assertNotNull($this->mysqli);
		// first, create a profile to post to mySQL
		$this->profile = new Profile(null, $this->email, $this->imagePath);
		// second, insert the profile to mySQL
		$this->profile->insert($this->mysqli);
		// third, update the profile and post the changes to mySQL
		$newEmail = "johndoe@noemail.com";
		$this->profile->setEmail($newEmail);
		$this->profile->update($this->mysqli);
		// finally, compare the fields
		$this->assertNotNull($this->profile->getProfileId());
		$this->assertTrue($this->profile->getProfileId() > 0);
		$this->assertIdentical($this->profile->getEmail(),			$newEmail);
		$this->assertIdentical($this->profile->getImagePath(),	$this->imagePath);
	}

	/**
	 * test deleting a Profile in mySQL
	 **/
	public function testDeleteProfile() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a profile to post to mySQL
		$this->profile = new Profile(null, $this->email, $this->imagePath);
		// second, insert the profile to mySQL
		$this->profile->insert($this->mysqli);
		// third, verify the Profile was inserted
		$this->assertNotNull($this->profile->getProfileId());
		$this->assertTrue($this->profile->getProfileId() > 0);
		// fourth, delete the profile
		$destroyedProfileId = $this->profile->getProfileId();
		$this->profile->delete($this->mysqli);
		$this->profile = null;
		// finally, try to get the profile and assert we didn't get anything
		$staticProfile = Profile::getProfileByProfileId($this->mysqli, $destroyedProfileId);
		$this->assertNull($staticProfile);
	}

	/**
	 * test grabbing a Profile from mySQL
	 */
	public function testGetProfileByProfileId() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a profile to post to mySQL
		$this->profile = new Profile(null, $this->email, $this->imagePath);
		// second, insert the profile to mySQl
		$this->profile->insert($this->mysqli);
		// third, get the profile using the static method
		$staticProfile = Profile::getProfileByProfileId($this->mysqli, $this->profile->getProfileId());
		// finally, compare the fields
		$this->assertNotNull($staticProfile->getProfileId());
		$this->assertTrue($staticProfile->getProfileId() > 0);
		$this->assertIdentical($staticProfile->getEmail(),			$this->email);
		$this->assertIdentical($staticProfile->getImagePath(),	$this->imagePath);
	}

	public function testGetProfileByEmail() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a profile to post to mySQL
		$this->profile = new Profile(null, $this->email, $this->imagePath);
		// second, insert the profile to mySQl
		$this->profile->insert($this->mysqli);
		// third, get the profile using the static method
		$staticProfile = Profile::getProfileByEmail($this->mysqli, $this->profile->getEmail());
		// finally, compare the fields
		$this->assertNotNull($staticProfile->getProfileId());
		$this->assertTrue($staticProfile->getProfileId() > 0);
		$this->assertIdentical($staticProfile->getEmail(),			$this->email);
		$this->assertIdentical($staticProfile->getImagePath(),	$this->imagePath);
	}

	// TODO: connect with Dylan and Alonso how to write up this static method get
//	public function testGetProfileByAllProfiles() {
//
//	}

}
?>