<?php
// 1st require the SimpleTest framework
require_once("/usr/lib/php5/simpletest/autorun.php");

class ArloSuite extends TestSuite {

	/**
	 * mysqli object shared amongst all tests
	 */
	private $mysqli = null;

	public function setUp() {
		// get the credentials information from the server
		$configFile = "/etc/apache2/arlo.ini";
		$configArray = readConfig($configFile);
		// connection
		mysqli_report(MYSQLI_REPORT_STRICT);
		$this->mysqli = new mysqli($configArray["hostname"], $configArray["username"], $configArray["password"],
			$configArray["database"]);
	}

	// the constructor for a TestSuite just sets up all the file names
	public function __construct() {
		// run the parent constructor
		parent::__construct();

		// stuff the test files into an array
		// TODO: add the files in the "forward" order
		$testFiles = array("profile.php", "queue.php", "video.php", "profilequeue.php", "videoqueue.php");

		// run them forward
		foreach($testFiles as $testFile) {
			$this->addFile($testFile);
		}

		// run them backward
		$testFiles = array_reverse($testFiles, false);
		foreach($testFiles as $testFile) {
			$this->addFile($testFile);
		}

		// run them randomly
		shuffle($testFiles);
		foreach($testFiles as $testFile) {
			$this->addFile($testFile);
		}
	}
}