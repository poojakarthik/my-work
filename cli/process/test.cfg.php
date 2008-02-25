<?php

// Make sure that FLEX_BASE_PATH is defined
if (!defined('FLEX_BASE_PATH'))
{
	echo "\nERROR: This script should not be run directly!\n";
	die;
}

//----------------------------------------------------------------------------//
// BILLING MULTIPART SCRIPT
//----------------------------------------------------------------------------//
$arrConfig = Array();

// Collection
$arrSubscript = Array();
$arrSubscript['Command']	=       'php '.BACKEND_BASE_PATH.'test_loop.php';
$arrSubscript['Directory']	=       BACKEND_BASE_PATH.'process/';
$arrScript['Test']		= $arrSubscript;

?>