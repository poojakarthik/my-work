<?php

// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// Work out the application template and method from the URL
$arrScript 		= Flex::getPathInfo();
$strHandler 	= array_shift($arrScript);
$strMethod 		= array_shift($arrScript);
$bIsLogin		= (($strHandler == 'Login') && ($strMethod == ''));

// ******** AUTHENTICATION ********
if (!Flex::continueSession(Flex::FLEX_ADMIN_SESSION) && !$bIsLogin)
{
	require_once dirname(__FILE__) . '/../../lib/classes/json/JSON_Services.php';
	
	// Prompt the user to extend their session or logout
	echo 	JSON_Services::encode(
				array(
					'ERROR'			=>'LOGIN',
					'sHandler'		=> $strHandler,
					'sMethod'		=> $strMethod,
					'aParameters'	=> JSON_Services::decode(isset($_POST['json']) ? $_POST['json'] : array())
				)
			);
	exit;
}

// Load the Flex framework and application
Flex::load();

if ($bIsLogin && isset($_POST['json']))
{
	require_once dirname(__FILE__) . '/../../lib/classes/json/JSON_Services.php';
	
	// Attempt to authenticate given the passed username and password
	$aParameters	= JSON_Services::decode($_POST['json']);
	echo	JSON_Services::encode(
				array('Success' => AuthenticatedUser()->CheckAuth($aParameters[0], $aParameters[1]))
			);
	exit;
}

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
