<?php
	Define ('FLEX_SESSION_NAME', 'flex_admin_sess_id');
	
	// Get the Flex class...
	require_once '../../lib/classes/Flex.php';
	Flex::load();
	Flex::continueSession(Flex::FLEX_ADMIN_SESSION);
	
	// Load framework
	require_once('require.php');
	
	$oApplication	= Application::instance();
	$oApplication->CheckAuth();
	
	header ("Location: ../admin/reflex.php/Console/View/");
	die;
?>
