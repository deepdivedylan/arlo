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
	private $email	= null;
	private $imagePath = null;

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