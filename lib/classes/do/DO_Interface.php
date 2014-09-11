<?php

interface DO_Interface
{
	static function getPropertyNames();
	
	public function set($propertyName, $value);
	public function get($propertyName);
	public function __set($propertyName, $value);
	public function __get($propertyName);
	public function __isset($propertyName);
	public function __unset($propertyName);
	
	public function __construct($arrInitialProperties=NULL);
	public function save();
	public function delete();
}

?>
