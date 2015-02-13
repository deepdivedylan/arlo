<?php
require_once('/usr/lib/php5/simpletest/autorun.php');
require_once("../php/classes/videoqueue.php");
require_once("../php/classes/video.php");
require_once("../php/classes/queue.php");
require_once("../lib/encrypted-config.php");
/**
 *
 * Unit test for the videoqueue class
 *
 * This is a SimpleTest test case for the CRUD methods of the videoqueue class.
 *
 * @see videoqueue
 * @author James Mistalski <james.mistalski@gmail.com>
 **/

// the VideoQueueTest is a container for all our tests
class VideoQueueTest extends unitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 **/
	private $mysqli	= null;

	/**
	 * instance of the object we are testing with
	 */
	private $videoQueueNumber = null;
	private $profile = null;
	private $video = null;

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
		// instance of objects under scrutiny

	}

}

?>