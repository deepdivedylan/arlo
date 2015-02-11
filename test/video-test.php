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

// the VideoTest is a container for all our tests
class VideoTest extends unitTestCase {
	/**
	 * mysqli object shared amongst all tests
	 */
	private $mysqli = null;

	/**
	 * variable to hold the test database row
	 */
	private $video = null;

	/**
	 * instance of the objects we are testing with
	 */
	private $videoComment = "Best movie I've seen in a while!";

	/**
	 * sets up the mySQL connection for this test
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
	 * tears down the connection to mySQl and deletes the test instance object
	 */
	public function tearDown() {
		// destroy the objects if they were created
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

	public function testInsertNewProfile() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a Video to post to mySQL
		$this->video = new Video(null, $this->videoComment);
		// second, insert the Video to mySQL
		$this->profile->insert($this->mysqli);
		// finally, compare the fields
		$this->assertNotNull($this->video->getVideoId());
		$this->assertTrue($this->video->getVideoId() > 0);
		$this->assertIdentical($this->video->getVideoComment(),	$this->videoComment);
	}

	/**
	 * test updating a Video in mySQl
	 */
	public function testUpdateVideo() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a video to post to mySQL
		$this->video = new Video(null, $this->videoComment);
		// second, insert the video to mySQL
		$this->video->insert($this->mysqli);
		// third, update the video and post the changes to mySQL
		$newVideoComment = "I take it back, this is the WORST movie I have EVER seen!!!";
		$this->video->setVideoComment($newVideoComment);
		$this->video->update($this->mysqli);
		// finally, compare the fields
		$this->assertNotNull($this->video->getVideoId());
		$this->assertTrue($this->video->getVideoId() > 0);
		$this->assertIdentical($this->video->getVideoComment(),	$this->$newVideoComment);
	}

	/**
	 * test deleting a Video in mySQL
	 */
	public function testDeleteVideo() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a video to post to mySQL
		$this->video = new Video(null, $this->videoComment);
		// second, insert the Video to mySQL
		$this->video->insert($this->mysqli);
		// third, verify the Video was inserted
		$this->assertNotNull($this->video->getVideoId());
		$this->assertTrue($this->video->getVideoId() > 0);
		// fourth, delete the Video
		$destroyVideoId = $this->video->getVideoId();
		$this->video->delete($this->mysqli);
		$this->video = null;
		// finally, try to get the video and assert we didn't get anything
		$staticVideo = Video::getVideoByVideoId($this->mysqli, $destroyVideoId);
		$this->assertNull($staticVideo);
	}

	/**
	 * test grabbing a Video from mySQL
	 */
	public function testGetVideoByVideoId() {
		// zeroth, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		// first, create a video to post to mySQL
		$this->video = new Video(null, $this->videoComment);
		// second, insert the video to mySQL
		$this->video->insert($this->mysqli);
		// third, get teh video using the static method
		$staticVideo = Video::getVideoByVideoId($this->mysqli, $this->video->getVideoId());
		// finally, compare the fields
		$this->assertNotNull($staticVideo->getVideoId());
		$this->assertTrue($staticVideo->getVideoId() > 0);
		$this->assertIdentical($staticVideo->getVideoComment(),	$this->videoComment);
	}

	// TODO: connect with Dylan and Alonso how to write up this static method get
//	public function testGetVideoByAllVideos() {
//
//	}

}

?>