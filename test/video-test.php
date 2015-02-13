<?php
require_once('/usr/lib/php5/simpletest/autorun.php');
require_once ("../php/classes/video.php");
require_once("../lib/encrypted-config.php");
/**
 *
 * Unit test for the video class
 *
 * This is a SimpleTest test case for the CRUD methods of the video class.
 *
 * @see video
 * @author James Mistalski <james.mistalski@gmail.com>
 **/

// the videoTest is a container for all our tests

class VideoTest extends UnitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 **/
	private $mysqli = null;

	/**
	 * first instance of the object we are testing with
	 **/
	private $video1 = null;

	/**
	 * second instance of the object we are testing with
	 **/
	private $video2 = null;

	/**
	 * seller's image path
	 **/
	private $videoComment = "Cool movie!";

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
		// create instance of first video
		$this->video1 = new Video(null, $this->videoComment);

		$this->video2 = new Video(null, "Not cool!");
	}

	/**
	 * tears down the connection to mySQL and deletes the test instance object
	 **/
	public function tearDown() {
//	echo '<br>tearDown start<br>';
		// destroy the object if it was created
		if($this->video1 !== null && $this->video1->getVideoId() !== null) {
			$this->video1->delete($this->mysqli);
		}
		$this->video1 = null;

		if($this->video2 !== null && $this->video2->getVideoId() !== null) {
			$this->video2->delete($this->mysqli);
		}
		$this->video2 = null;

		// disconnect from mySQL
		if($this->mysqli !== null) {
			$this->mysqli->close();
			$this->mysqli = null;
		}
	}
	/**
	 * test inserting a valid video into mySQL
	 **/
	public function testInsertValidVideo() {
		// zeroth, ensure the video and mySQL class are sane
		$this->assertNotNull($this->video1);
		$this->assertNotNull($this->mysqli);

		// first, insert the video into mySQL
		$this->video1->insert($this->mysqli);

		// second, grab a video from mySQL
		$mysqlVideo = Video::getVideoByVideoId($this->mysqli, $this->video1->getVideoId());

		// third, assert the video we have created and mySQL's video are the same object
		$this->assertIdentical($this->video1->getVideoId(), $mysqlVideo->getVideoId());
		$this->assertIdentical($this->video1->getVideoComment(), $mysqlVideo->getVideoComment());
	}

	/**
	 * test inserting an invalid video into mySQL
	 **/
	public function testInsertInvalidVideo() {
		// zeroth, ensure the video and mySQL class are sane
		$this->assertNotNull($this->video1);
		$this->assertNotNull($this->mysqli);

		// first, set the video id to an invented value that should never insert in the first place
		$this->video1->setVideoId(1042);

		// second, try to insert the video and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->video1->insert($this->mysqli);

		// third, set the video to null to prevent tearDown() from deleting a video that never existed
		$this->video1 = null;
	}

	/**
	 * test deleting a video from mySQL
	 **/
	public function testDeleteValidVideo() {
		// zeroth, ensure the video and mySQL class are sane
		$this->assertNotNull($this->video1);
		$this->assertNotNull($this->mysqli);

		// first, assert the video is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->video1->insert($this->mysqli);
		$mysqlVideo = Video::getVideoByVideoId($this->mysqli, $this->video1->getVideoId());
		$this->assertIdentical($this->video1->getVideoId(), $mysqlVideo->getVideoId());

		// second, delete the video from mySQL and re-grab it from mySQL and assert it does not exist
		$this->video1->delete($this->mysqli);
		$mysqlVideo = Video::getVideoByVideoId($this->mysqli, $this->video1->getVideoId());
		$this->assertNull($mysqlVideo);

		// third, set the video to null to prevent tearDown() from deleting a video that has already been deleted
		$this->video1 = null;
	}

	/**
	 * test deleting a non existent video from mySQL
	 **/
	public function testDeleteInvalidVideo() {
		// zeroth, ensure the video and mySQL class are sane
		$this->assertNotNull($this->video1);
		$this->assertNotNull($this->mysqli);

		// first, try to delete the video before inserting it and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->video1->delete($this->mysqli);

		// second, set the video to null to prevent tearDown() from deleting a video that has already been deleted
		$this->video1 = null;
	}

	/**
	 * test updating a video from mySQL
	 **/
	public function testUpdateValidVideo() {
		// zeroth, ensure the video and mySQL class are sane
		$this->assertNotNull($this->video1);
		$this->assertNotNull($this->mysqli);

		// first, assert the video is inserted into mySQL by grabbing it from mySQL and asserting the primary key
		$this->video1->insert($this->mysqli);
		$mysqlVideo = Video::getVideoByVideoId($this->mysqli, $this->video1->getVideoId());
		$this->assertIdentical($this->video1->getVideoId(), $mysqlVideo->getVideoId());

		// second, change the video, update it mySQL
		$newVideoComment = "Awesome!";
		$this->video1->setVideoComment($newVideoComment);
		$this->video1->update($this->mysqli);

		// third, re-grab the video from mySQL
		$mysqlVideo = Video::getVideoByVideoId($this->mysqli, $this->video1->getVideoId());
		$this->assertNotNull($mysqlVideo);

		// fourth, assert the video we have updated and mySQL's video are the same object
		$this->assertIdentical($this->video1->getVideoId(), $mysqlVideo->getVideoId());
		$this->assertIdentical($this->video1->getVideoComment(), $mysqlVideo->getVideoComment());
	}

	/**
	 * test updating a non existent video from mySQL
	 **/
	public function testUpdateInvalidVideo() {
		// zeroth, ensure the video and mySQL class are sane
		$this->assertNotNull($this->video1);
		$this->assertNotNull($this->mysqli);

		// first, try to update the video before inserting it and ensure the exception is thrown
		$this->expectException("mysqli_sql_exception");
		$this->video1->update($this->mysqli);

		// second, set the video to null to prevent tearDown() from deleting a video that has already been deleted
		$this->video1 = null;
	}
	/**
	 *test getting a valid video by videoId
	 **/
	public function testGetValidVideoByVideoId() {
		$this->assertNotNull($this->video1);
		$this->assertNotNull($this->mysqli);

		// first, assert the video is inserted into mySQL by grabbing it and asserting the primary key
		$this->video1->insert($this->mysqli);
		$mysqlVideo = Video::getVideoByVideoId($this->mysqli, $this->video1->getVideoId());
		$this->assertIdentical($this->video1->getVideoId(), $mysqlVideo->getVideoId());
	}

	/**
	 * test getting a valid video by using an invalid videoId
	 **/
	public function testGetInvalidVideoByVideoId() {
		// first, assert the mySQL class is sane
		$this->assertNotNull($this->mysqli);

		// grab a video that could never exist
		$mysqlVideo = Video::getVideoByVideoId($this->mysqli, 12);
		$this->assertNull($mysqlVideo);
	}
}
?>