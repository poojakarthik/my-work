<?php

// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// ******** AUTHENTICATION ********
if (!Flex::continueSession(Flex::FLEX_ADMIN_SESSION))
{
	// Redirect the user to the login page
	header('Location: login.php');
	exit;
}

// Load the Flex framework and application
Flex::load();

// Work out the application template and method from the URL
// takes a URL like : http://.../flex.php/ApplicationTemplate/Method/?Object.Property=Value
$arrScript 		= explode('.php', $_SERVER['PHP_SELF'], 2);
$strScript 		= ltrim($arrScript[1], '/'); 
$arrScript 		= explode('/', $strScript);
$strHandler 	= $arrScript[0];
$strMethod 		= $arrScript[1];

// instanciate application & load application
Application::instance()->LoadPageHandler($strHandler, $strMethod);

?>
