<?php
	// load framework
	require_once('require.php');
	
	// instanciate application
	$Application = Application::instance();
	
	// call ajax_load
	$Application->AjaxLoad();
?>
