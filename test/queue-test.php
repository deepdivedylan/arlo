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
	private $creationDate = ""
}