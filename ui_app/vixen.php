<?php
// Work out the application template and method from the URL
// takes a URL like : http://.../vixen.php/ApplicationTemplate/Method/?Object.Property=Value
$arrScript 		= explode('.php', $_SERVER['PHP_SELF'], 2);
$strScript 		= ltrim($arrScript[1], '/'); 
$arrScript 		= explode('/', $strScript);
$strTemplate 	= $arrScript[0];
$strMethod 		= $arrScript[1];

// load stuff here
//TODO!!!!

require_once('application_loader.php');
$TemplateName = $strTemplate .".". $strMethod;
$Application->Load($TemplateName);


?>
