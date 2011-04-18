<?php

class Test {
	protected	$_sName = 'Test';
	
	public function __construct($sName) {
		$this->_sName = $sName;
	}
	
	public function getName() {
		return $this->_sName;
	}
	
	public static function isTestMethod($sMethod) {
		return !in_array($sMethod, array('__construct', 'getName', 'isTestMethod'));
	}
}

?>