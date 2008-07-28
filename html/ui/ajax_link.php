<?php
	define('FLEX_SESSION_NAME', 'flex_admin_sess_id');

	// load framework
	require_once('require.php');
	
	// instanciate application
	$Application = Singleton::Instance('Application');
	
	// call ajax_load
	$Application->AjaxLoad();


?>
