<?php

Define ('FLEX_SESSION_NAME',	'flex_admin_sess_id');


// Work out the application template and method from the URL
// takes a URL like : http://.../flex.php/ApplicationTemplate/Method/?Object.Property=Value
$arrScript 		= explode('.php', $_SERVER['PHP_SELF'], 2);
$strScript 		= ltrim($arrScript[1], '/'); 
$arrScript 		= explode('/', $strScript);
$strTemplate 	= $arrScript[0];
$strMethod 		= $arrScript[1];

// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// I've stuck this in so the session is named properly
// Hopefully it won't screw things up, as it calls several of the php session_ functions
Flex::continueSession(Flex::FLEX_ADMIN_SESSION);

// I added this, so that the new autoloading functionality is available, (which includes the old)
Flex::load();

// load framework
require_once('require.php');

// Never Cache
// FIXME: We should probably look into a better solution for this...
header( 'Expires: Mon, 20 Oct 1985 10:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Cache-Control: max-age=0', false );
header( 'Pragma: no-cache' );

// instanciate application
$Application = Application::instance();

// load application
$TemplateName = $strTemplate .".". $strMethod;
$Application->Load($TemplateName);


?>
