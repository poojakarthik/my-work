<?php
	// load framework
	require_once('../ui/require.php');
	
	// instanciate application
	$Application = Singleton::Instance('Application');
	
	// call ajax_load
	$Application->AjaxLoad();
?>
