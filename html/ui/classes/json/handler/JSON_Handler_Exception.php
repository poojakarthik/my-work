<?php

interface JSON_Handler_Exception {
	public function getFriendlyMessage();
	
	public function getDetailedMessage();
	
	public function getData();
}

?>