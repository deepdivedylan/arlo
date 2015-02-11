<?php
require_once('/usr/lib/php5/simpletest/autorun.php');
require_once ("../php/classes/queue.php");
require_once("../lib/encrypted-config.php");
/**
 *
 * Unit test for the queue class
 *
 * This is a SimpleTest test case for the CRUD methods of the queue class.
 *
 * @see queue
 * @author James Mistalski <james.mistalski@gmail.com>
 **/

// the QueueTest is a container for all our tests
class QueueTest extends unitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 */
	private $mysqli = null;

	/**
	 * variable to hold the test database row
	 */
	private $queue = null;

	/**
	 * instance of the objects we are testing with
	 */
	private $creationDate = "2015-02-10 12:01:00";

	/**
	 * sets up the mySQL connection
	 */
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
	 */
	public function tearDown() {
		// destroy the objects if they were created
		if($this->queue !== null) {
			$this->queue->delete($this->mysqli);
			$this->queue = null;

			// disconnect from mySQL
			if($this->mysqli !== null) {
				$this->mysqli->close();
				$this->mysqli = null;
			}
		}
	}

	/**
	 * test creating a new Queue and inserting it into mySQL
	 */
	public function testInsertNewQueue() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a queue to post to mySQL
		$this->queue = new Queue(null, $this->creationDate);
		// second, insert the queue to mySQL
		$this->queue->insert($this->mysqli);
		// finally, compare the fields
		$this->assertNotNull($this->queue->getQueueId());
		$this->assertTrue($this->queue->getQueueId() > 0);
		$this->assertIdentical($this->queue->getCreationDate(),	$this->creationDate);
	}

	/**
	 * test updating a Queue in mySQL
	 */
	public function testUpdateQueue() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a queue and post to mySQL
		$this->queue = new Queue(null, $this->creationDate);
		// second, insert the queue to mySQL
		$this->queue->insert($this->mysqli);
		// third, update the queue and post the changes to mySQL
		$newCreationDate = "2015-02-13 09:30:23";
		$this->queue->setCreationDate($newCreationDate);
		$tempDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->$newCreationDate);
		$tempDate->setD


	}
}

?>