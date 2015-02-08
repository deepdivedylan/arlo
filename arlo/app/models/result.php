<?php

/**
 * Normalized result container for Video records
 *
 * This is a Container for search results from API queries. It is designed to be directly accessed from a foreach loop.
 *
 * @author Dylan McDonald <sim@vsfs.org>
 * @see http://php.net/manual/en/class.countable.php
 * @see http://php.net/manual/en/class.iterator.php
 **/
class Result implements Countable, Iterator {
	/**
	 * @var int position in the array index
	 **/
	private $index;
	/**
	 * @var array actual result set
	 **/
	private $videos;

	/**
	 * constructor for the Result set
	 *
	 * @param array $videos actual data for the Result
	 **/
	public function __construct($videos = array()) {
		$this->index  = 0;
		$this->videos = $videos;
	}

	/**
	 * resets the index position back to the beginning (i.e., element 0)
	 **/
	public function rewind() {
		$this->index = 0;
	}

	/**
	 * returns the value of the current item in the Result set
	 *
	 * @return Video
	 **/
	public function current() {
		return($this->videos[$this->index]);
	}

	public function key() {
		return($this->index);
	}

	public function next() {
		$this->index++;
	}

	public function valid() {
		return(@isset($this->videos[$this->index]));
	}

	public function count() {
		return(count($this->videos));
	}
}
?>