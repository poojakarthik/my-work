<?php

// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// ******** AUTHENTICATION ********
if (!Flex::continueSession(Flex::FLEX_ADMIN_SESSION))
{
	// Prompt the user to extend their session or logout
	$response = array('ERROR'=>'LOGIN');
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

// We never want to cache AJAX
// FIXME: We should probably look into a better solution for this...
header( 'Expires: Mon, 20 Oct 1985 10:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Cache-Control: max-age=0', false );
header( 'Pragma: no-cache' );

// instanciate application & load application
Application::instance()->LoadJsonHandler($strHandler, $strMethod, $arrScript);

?>
