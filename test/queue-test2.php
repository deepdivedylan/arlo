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

// the queueTest is a container for all our tests

class QueueTest extends UnitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 **/
	private $mysqli = null;

	/**
	 * first instance of the object we are testing with
	 **/
	private $queue1 = null;

	/**
	 * second instance of the object we are testing with
	 **/
	private $queue2 = null;

	/**
	 * seller's image path
	 **/
	private $creationDate = null;

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
		// create instance of first queue
		$this->queue1 = new Queue(null, $this->creationDate);

		$this->queue2 = new Queue(null, $this->creationDate);
	}

	/**
	 * tears down the connection to mySQL and deletes the test instance object
	 **/
	public function tearDown() {
//	echo '<br>tearDown start<br>';
		// destroy the object if it was created
		if($this->queue1 !== null && $this->queue1->getQueueId() !== null) {
			$this->queue1->delete($this->mysqli);
		}
		$this->queue1 = null;

		if($this->queue2 !== null && $this->queue2->getQueueId() !== null) {
			$this->queue2->delete($this->mysqli);
		}
		$this->queue2 = null;

		// disconnect from mySQL
		if($this->mysqli !== null) {
			$this->mysqli->close();
			$this->mysqli = null;
		}
	}
	/**
	 * test inserting a valid queue into mySQL
	 **/
	public function testInsertValidQueue() {
		// zeroth, ensure the queue and mySQL class are sane
		$this->assertNotNull($this->queue1);
		$this->assertNotNull($this->mysqli);

		// first, insert the queue into mySQL
		$this->queue1->insert($this->mysqli);

		// second, grab a queue from mySQL
		$mysqlQueue = Queue::getQueueByQueueId($this->mysqli, $this->queue1->getQueueId());

		// third, assert the queue we have created and mySQL's queue are the same object
		$this->assertIdentical($this->queue1->getQueueId(), $mysqlQueue->getQueueId());
		$this->assertIdentical($this->queue1->getCreationDate(), $mysqlQueue->getCreationDate());
	}

	/**
	 * test inserting an invalid queue into mySQL
	 **/
	public function testInsertInvalidQueue() {
		// zeroth, ensure the queue and mySQL class are sane
		$this->assertNotNull($this->queue1);
		$this->assertNotNull($this->mysqli);

		// first, set the queue id to an invented value that should never insert in the first place
		$this->queue1->setQueueId(1042);

		// second, try to insert the queue and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->queue1->insert($this->mysqli);

		// third, set the queue to null to prevent tearDown() from deleting a queue that never existed
		$this->queue1 = null;
	}

	/**
	 * test deleting a queue from mySQL
	 **/
	public function testDeleteValidQueue() {
		// zeroth, ensure the queue and mySQL class are sane
		$this->assertNotNull($this->queue1);
		$this->assertNotNull($this->mysqli);

		// first, assert the queue is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->queue1->insert($this->mysqli);
		$mysqlQueue = Queue::getQueueByQueueId($this->mysqli, $this->queue1->getQueueId());
		$this->assertIdentical($this->queue1->getQueueId(), $mysqlQueue->getQueueId());

		// second, delete the queue from mySQL and re-grab it from mySQL and assert it does not exist
		$this->queue1->delete($this->mysqli);
		$mysqlQueue = Queue::getQueueByQueueId($this->mysqli, $this->queue1->getQueueId());
		$this->assertNull($mysqlQueue);

		// third, set the queue to null to prevent tearDown() from deleting a queue that has already been deleted
		$this->queue1 = null;
	}

	/**
	 * test deleting a non existent queue from mySQL
	 **/
	public function testDeleteInvalidQueue() {
		// zeroth, ensure the queue and mySQL class are sane
		$this->assertNotNull($this->queue1);
		$this->assertNotNull($this->mysqli);

		// first, try to delete the queue before inserting it and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->queue1->delete($this->mysqli);

		// second, set the queue to null to prevent tearDown() from deleting a queue that has already been deleted
		$this->queue1 = null;
	}

	/**
	 * test updating a queue from mySQL
	 **/
	public function testUpdateValidQueue() {
		// zeroth, ensure the queue and mySQL class are sane
		$this->assertNotNull($this->queue1);
		$this->assertNotNull($this->mysqli);

		// first, assert the queue is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->queue1->insert($this->mysqli);
		$mysqlQueue = Queue::getQueueByQueueId($this->mysqli, $this->queue1->getQueueId());
		$this->assertIdentical($this->queue1->getQueueId(), $mysqlQueue->getQueueId());

		// second, change the queue, update it mySQL
		$newCreationDate = null;
		$this->queue1->setCreationDate($newCreationDate);
		$this->queue1->update($this->mysqli);

		// third, re-grab the queue from mySQL
		$mysqlQueue = Queue::getQueueByQueueId($this->mysqli, $this->queue1->getQueueId());
		$this->assertNotNull($mysqlQueue);

		// fourth, assert the queue we have updated and mySQL's queue are the same object
		$this->assertIdentical($this->queue1->getQueueId(), $mysqlQueue->getQueueId());
		$this->assertIdentical($this->queue1->getCreationDate(), $mysqlQueue->getCreationDate());
	}

	/**
	 * test updating a non existent queue from mySQL
	 **/
	public function testUpdateInvalidQueue() {
		// zeroth, ensure the queue and mySQL class are sane
		$this->assertNotNull($this->queue1);
		$this->assertNotNull($this->mysqli);

		// first, try to update the queue before inserting it and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->queue1->update($this->mysqli);

		// second, set the queue to null to prevent tearDown() from deleting a queue that has already been deleted
		$this->queue1 = null;
	}
	/**
	 *test getting a valid queue by queueId
	 **/
	public function testGetValidQueueByQueueId() {
		$this->assertNotNull($this->queue1);
		$this->assertNotNull($this->mysqli);

		// first, assert the queue is inserted into mySQL by grabbing it and asserting the primary key
		$this->queue1->insert($this->mysqli);
		$mysqlQueue = Queue::getQueueByQueueId($this->mysqli, $this->queue1->getQueueId());
		$this->assertIdentical($this->queue1->getQueueId(), $mysqlQueue->getQueueId());
	}

	/**
	 * test getting a valid queue by using an invalid queueId
	 **/
	public function testGetInvalidQueueByQueueId() {
		// first, assert the mySQL class is sane
		$this->assertNotNull($this->mysqli);

		// grab a queue that could never exist
		$mysqlQueue = Queue::getQueueByQueueId($this->mysqli, 12);
		$this->assertNull($mysqlQueue);
	}
}
?>