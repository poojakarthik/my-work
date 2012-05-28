<?php
class DBObjectBase extends DataAccessUI implements Iterator {
	protected $_arrProperties = array();
	
	public function rewind() {
		reset($this->_arrProperties);
	}
	
	public function current() {
		return PropertyToken()->_Property($this, key($this->_arrProperties));
	}
	
	public function key() {
		return key($this->_arrProperties);
	}
	
	public function next() {
		next($this->_arrProperties);
		return PropertyToken()->_Property($this, key($this->_arrProperties));
	}
	
	public function valid() {
		return !is_null($this->key());
	}
}
