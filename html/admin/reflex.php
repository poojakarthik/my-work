<?php

// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// ******** AUTHENTICATION ********
if (!Flex::continueSession(Flex::FLEX_ADMIN_SESSION))
{
	// Redirect the user to the login page
	header("Location: " . Flex::getUrlBase() . "login.php");
	exit;
}

// Load the Flex framework and application
Flex::load();

// Work out the application template and method from the URL
$arrScript 		= Flex::getPathInfo();
$strHandler 	= array_shift($arrScript);
$strMethod 		= array_shift($arrScript);

// instanciate application & load application
Application::instance()->LoadPageHandler($strHandler, $strMethod, $arrScript);

?>
