<?php
require_once('/usr/lib/php5/simpletest/autorun.php');
require_once("../php/classes/videoqueue.php");
require_once ("../php/classes/video.php");
require_once ("../php/classes/queue.php");
require_once("../lib/encrypted-config.php");
/**
 * Unit test for the videoQueue class
 *
 * This is a SimpleTest test case for the CRUD methods of the videoQueue class.
 *
 * @see videoQueue
 * @author Alonso Indacochea <alonso@hermesdevelopment.com>
 **/
class VideoQueueTest extends UnitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 **/
	private $mysqli = null;
	/**
	 * instance of the objects we are testing with
	 **/
	private $videoQueue = null;
	private $video = null;
	private $queue = null;

	private $videoQueueNumber = 1;

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
		$this->video = new Video(null, 'cool');
		$this->video->insert($this->mysqli);
		$this->queue = new Queue(null, $date);
		$this->queue->insert($this->mysqli);
		$this->videoQueue = new VideoQueue($this->video->getVideoId(), $this->queue->getQueueId(), $this->videoQueueNumber);
	}

	/**
	 * tears down the connection to mySQL and deletes the test instance object
	 **/
	public function tearDown() {
		// destroy the objects if they were created
		if($this->videoQueue !== null) {
			$this->videoQueue->delete($this->mysqli);
			$this->videoQueue = null;
		}
		if($this->queue !== null) {
			$this->queue->delete($this->mysqli);
			$this->queue = null;
		}
		if($this->video !== null) {
			$this->video->delete($this->mysqli);
			$this->video = null;

			// disconnect from mySQL
			if($this->mysqli !== null) {
				$this->mysqli->close();
				$this->mysqli = null;
			}
		}
	}
	/**
	 * test inserting a valid VideoQueue into mySQL
	 **/
	public function testInsertValidVideoQueue() {
		// zeroth, ensure the VideoQueue and mySQL class are sane
		$this->assertNotNull($this->videoQueue);
		$this->assertNotNull($this->mysqli);
		// first, insert the VideoQueue into mySQL
		$this->videoQueue->insert($this->mysqli);
		// second, grab a VideoQueue from mySQL
		$mysqlVideoQueue = VideoQueue::getVideoQueueByVideoIdAndQueueId($this->mysqli, $this->videoQueue->getVideoId(), $this->videoQueue->getQueueId());
		// third, assert the VideoQueue we have created and mySQL's VideoQueue are the same object
		$this->assertIdentical($this->videoQueue->getVideoId(), $mysqlVideoQueue->getVideoId());
		$this->assertIdentical($this->videoQueue->getQueueId(), $mysqlVideoQueue->getQueueId());
	}

	/**
	 * test inserting an invalid VideoQueue into mySQL
	 **/
	public function testInsertInvalidVideoQueue() {
		// zeroth, ensure the VideoQueue and mySQL class are sane
		$this->assertNotNull($this->videoQueue);
		$this->assertNotNull($this->mysqli);
		// first, set the video id and queue id to an invented value that should never insert in the first place
		$this->videoQueue->setVideoId(42);
		$this->videoQueue->setQueueId(42);
		// second, try to insert the VideoQueue and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->videoQueue->insert($this->mysqli);
		// third, set the VideoQueue to null to prevent tearDown() from deleting a VideoQueue that never existed
		$this->videoQueue = null;
	}

	/**
	 * test deleting a VideoQueue from mySQL
	 **/
	public function testDeleteValidVideoQueue() {
		// zeroth, ensure the VideoQueue and mySQL class are sane
		$this->assertNotNull($this->videoQueue);
		$this->assertNotNull($this->mysqli);
		// first, assert the VideoQueue is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->videoQueue->insert($this->mysqli);
		$mysqlVideoQueue = VideoQueue::getVideoQueueByVideoIdAndQueueId($this->mysqli, $this->videoQueue->getVideoId(), $this->videoQueue->getQueueId());
		$this->assertIdentical($this->videoQueue->getVideoId(), $mysqlVideoQueue->getVideoId());
		$this->assertIdentical($this->videoQueue->getQueueId(), $mysqlVideoQueue->getQueueId());
		$this->assertIdentical($this->videoQueue->getVideoQueueNumber(), $mysqlVideoQueue->getVideoQueueNumber());

		// second, delete the VideoQueue from mySQL and re-grab it from mySQL and assert it does not exist
		$this->videoQueue->delete($this->mysqli);
		$mysqlVideoQueue = VideoQueue::getVideoQueueByVideoIdAndQueueId($this->mysqli, $this->videoQueue->getVideoId(), $this->videoQueue->getQueueId());
		$this->assertNull($mysqlVideoQueue);
		// third, set the VideoQueue to null to prevent tearDown() from deleting a VideoQueue that has already been deleted
		$this->videoQueue = null;
	}

	/**
	 * test get VideoQueue by valid video id and valid queue id
	 */
	public function testGetVideoQueueByValidVideoIdAndValidQueueId() {
		$this->assertNotNull($this->videoQueue);
		$this->assertNotNull($this->mysqli);
		// first, insert the VideoQueue into mySQL
		$this->videoQueue->insert($this->mysqli);
		// second, grab the VideoQueue from mySQL
		$mysqlVideoQueues = VideoQueue::getVideoQueueByVideoIdAndQueueId($this->mysqli, $this->videoQueue->getVideoId(), $this->videoQueue->getQueueId());
		// third, assert the VideoQueue we have created and mySQL's VideoQueue are the same object
		foreach($mysqlVideoQueues as $mysqlVideoQueue) {
			$this->assertNotNull($mysqlVideoQueue->getVideoId());
			$this->assertTrue($mysqlVideoQueue->getVideoId() > 0);
			$this->assertNotNull($mysqlVideoQueue->getQueueId());
			$this->assertTrue($mysqlVideoQueue->getQueueId() > 0);
			$this->assertIdentical($this->videoQueue->getVideoId(), $mysqlVideoQueue->getVideoId());
			$this->assertIdentical($this->videoQueue->getQueueId(), $mysqlVideoQueue->getQueueId());
			$this->assertIdentical($this->videoQueue->getVideoQueueNumber(), $mysqlVideoQueue->getVideoQueueNumber());

		}
	}

	/**
	 * test get video queue by invalid video id or invalid queue id
	 */
	public function testGetVideoQueueByInvalidVideoIdAndInvalidQueueId() {
		$this->assertNotNull($this->videoQueue);
		$this->assertNotNull($this->mysqli);

		// first, insert the videoQueue into mySQL
		$this->videoQueue->insert($this->mysqli);

		// second, create an array grabbing a videoQueue that doesn't exist
		$mysqlVideoQueues = VideoQueue::getVideoQueueByVideoIdAndQueueId($this->mysqli, 100, 101);

		// third, assert the array is null
		$this->assertNull($mysqlVideoQueues);
	}
}
?>