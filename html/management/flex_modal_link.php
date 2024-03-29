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

// load framework
require_once('../admin/require.php');

// instanciate application
$Application = Application::instance();

// load application
$TemplateName = $strTemplate .".". $strMethod;
$Application->LoadModal($TemplateName);


?>
