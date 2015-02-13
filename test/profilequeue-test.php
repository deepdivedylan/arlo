<?php
require_once('/usr/lib/php5/simpletest/autorun.php');
require_once("../php/classes/profilequeue.php");
require_once ("../php/classes/profile.php");
require_once ("../php/classes/queue.php");
require_once("../lib/encrypted-config.php");
/**
 * Unit test for the profileQueue class
 *
 * This is a SimpleTest test case for the CRUD methods of the profileQueue class.
 *
 * @see profileQueue
 * @author Alonso Indacochea <alonso@hermesdevelopment.com>
 **/
class ProfileQueueTest extends UnitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 **/
	private $mysqli = null;
	/**
	 * instance of the objects we are testing with
	 **/
	private $profileQueue = null;
	private $profile = null;
	private $queue = null;

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
		$date = new DateTime();
		$this->profile = new Profile(null, "test@test.com", 'http://www.cats.com/cat.jpg', 10);
		$this->profile->insert($this->mysqli);
		$this->queue = new Queue(null, $date);
		$this->queue->insert($this->mysqli);
		$this->profileQueue = new ProfileQueue($this->profile->getProfileId(), $this->queue->getQueueId(), 'Horror movies');
	}

	/**
	 * tears down the connection to mySQL and deletes the test instance object
	 **/
	public function tearDown() {
		// destroy the objects if they were created
		if($this->profileQueue !== null) {
			$this->profileQueue->delete($this->mysqli);
			$this->profileQueue = null;
		}
		if($this->queue !== null) {
			$this->queue->delete($this->mysqli);
			$this->queue = null;
		}
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
	 * test inserting a valid ProfileQueue into mySQL
	 **/
	public function testInsertValidProfileQueue() {
		// zeroth, ensure the ProfileQueue and mySQL class are sane
		$this->assertNotNull($this->profileQueue);
		$this->assertNotNull($this->mysqli);
		// first, insert the ProfileQueue into mySQL
		$this->profileQueue->insert($this->mysqli);
		// second, grab a ProfileQueue from mySQL
		$mysqlProfileQueue = ProfileQueue::getProfileQueueByProfileIdAndQueueId($this->mysqli, $this->profileQueue->getProfileId(), $this->profileQueue->getQueueId());
		// third, assert the ProfileQueue we have created and mySQL's ProfileQueue are the same object
		$this->assertIdentical($this->profileQueue->getProfileId(), $mysqlProfileQueue->getProfileId());
		$this->assertIdentical($this->profileQueue->getQueueId(), $mysqlProfileQueue->getQueueId());
		$this->assertIdentical($this->profileQueue->getProfileQueueName(), $mysqlProfileQueue->getProfileQueueName());

	}

	/**
	 * test inserting an invalid ProfileQueue into mySQL
	 **/
	public function testInsertInvalidProfileQueue() {
		// zeroth, ensure the ProfileQueue and mySQL class are sane
		$this->assertNotNull($this->profileQueue);
		$this->assertNotNull($this->mysqli);
		// first, set the profile id and queue id to an invented value that should never insert in the first place
		$this->profileQueue->setProfileId(42);
		$this->profileQueue->setQueueId(42);
		// second, try to insert the ProfileQueue and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->profileQueue->insert($this->mysqli);
		// third, set the ProfileQueue to null to prevent tearDown() from deleting a ProfileQueue that never existed
		$this->profileQueue = null;
	}

	/**
	 * test deleting a ProfileQueue from mySQL
	 **/
	public function testDeleteValidProfileQueue() {
		// zeroth, ensure the ProfileQueue and mySQL class are sane
		$this->assertNotNull($this->profileQueue);
		$this->assertNotNull($this->mysqli);
		// first, assert the ProfileQueue is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->profileQueue->insert($this->mysqli);
		$mysqlProfileQueue = ProfileQueue::getProfileQueueByProfileIdAndQueueId($this->mysqli, $this->profileQueue->getProfileId(), $this->profileQueue->getQueueId());
		$this->assertIdentical($this->profileQueue->getProfileId(), $mysqlProfileQueue->getProfileId());
		$this->assertIdentical($this->profileQueue->getQueueId(), $mysqlProfileQueue->getQueueId());
		$this->assertIdentical($this->profileQueue->getProfileQueueName(), $mysqlProfileQueue->getProfileQueueName());
		// second, delete the ProfileQueue from mySQL and re-grab it from mySQL and assert it does not exist
		$this->profileQueue->delete($this->mysqli);
		$mysqlProfileQueue = ProfileQueue::getProfileQueueByProfileIdAndQueueId($this->mysqli, $this->profileQueue->getProfileId(), $this->profileQueue->getQueueId());
		$this->assertNull($mysqlProfileQueue);
		// third, set the ProfileQueue to null to prevent tearDown() from deleting a ProfileQueue that has already been deleted
		$this->profileQueue = null;
		}

	/**
	 * test get ProfileQueue by valid profile id and valid queue id
	 */
	public function testGetProfileQueueByValidProfileIdAndValidQueueId() {
		$this->assertNotNull($this->profileQueue);
		$this->assertNotNull($this->mysqli);
		// first, insert the ProfileQueue into mySQL
		$this->profileQueue->insert($this->mysqli);
		// second, grab the ProfileQueue from mySQL
		$mysqlProfileQueues = ProfileQueue::getProfileQueueByProfileIdAndQueueId($this->mysqli, $this->profileQueue->getProfileId(), $this->profileQueue->getQueueId());
		// third, assert the ProfileQueue we have created and mySQL's ProfileQueue are the same object
		foreach($mysqlProfileQueues as $mysqlProfileQueue) {
			$this->assertNotNull($mysqlProfileQueue->getProfileId());
			$this->assertTrue($mysqlProfileQueue->getProfileId() > 0);
			$this->assertNotNull($mysqlProfileQueue->getQueueId());
			$this->assertTrue($mysqlProfileQueue->getQueueId() > 0);
			$this->assertIdentical($this->profileQueue->getProfileId(), $mysqlProfileQueue->getProfileId());
			$this->assertIdentical($this->profileQueue->getQueueId(), $mysqlProfileQueue->getQueueId());
			$this->assertIdentical($this->profileQueue->getProfileQueueName(), $mysqlProfileQueue->getProfileQueueName());

		}
	}

	/**
	 * test get profile queue by invalid profile id or invalid queue id
	 */
	public function testGetProfileQueueByInvalidProfileIdAndInvalidQueueId() {
		$this->assertNotNull($this->profileQueue);
		$this->assertNotNull($this->mysqli);

		// first, insert the profileQueue into mySQL
		$this->profileQueue->insert($this->mysqli);

		// second, create an array grabbing a profileQueue that doesn't exist
		$mysqlProfileQueues = ProfileQueue::getProfileQueueByProfileIdAndQueueId($this->mysqli, 100, 101);

		// third, assert the array is null
		$this->assertNull($mysqlProfileQueues);
	}
}
?>