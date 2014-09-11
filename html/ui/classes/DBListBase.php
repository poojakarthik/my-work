<?php
class DBListBase extends DataAccessUI implements Iterator {
	protected $_arrDataArray = array();
	
	public function rewind() {
		reset($this->_arrDataArray);
	}
	
	public function current() {
		return current($this->_arrDataArray);
	}
	
	public function key() {
		return key($this->_arrDataArray);
	}
	
	public function next() {
		return next($this->_arrDataArray);
	}
	
	public function valid() {
		return !is_null($this->key());
	}
}
