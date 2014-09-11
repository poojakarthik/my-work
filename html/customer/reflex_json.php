<?php

// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// ******** AUTHENTICATION ********
if (!Flex::continueSession(Flex::FLEX_CUSTOMER_SESSION))
{
	// Prompt the user to extend their session or logout
	$response = array('ERROR'=>'Your session has expired.  Please log in again.');
	require_once dirname(__FILE__) . '/../../lib/classes/json/JSON_Services.php';
	echo JSON_Services::encode($response);
	exit;
}

// Load the Flex framework and application
Flex::load();

// Work out the application template and method from the URL
$arrScript 		= Flex::getPathInfo();
$strHandler 	= array_shift($arrScript);
$strMethod 		= array_shift($arrScript);

// instanciate application & load application
Application::instance()->LoadJsonHandler($strHandler, $strMethod, $arrScript);

?>
