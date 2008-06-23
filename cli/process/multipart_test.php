<?php

// Make sure that BACKEND_BASE_PATH is defined
if (!defined('BACKEND_BASE_PATH'))
{
	echo "\nERROR: This script should not be run directly!\n";
	die;
}

//----------------------------------------------------------------------------//
// MULTIPART TEST SCRIPT
//----------------------------------------------------------------------------//
$arrConfig = Array();

// Normal
$arrSubscript = Array();
$arrSubscript['Command']			= 'php test_loop.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'process/';
$arrConfig['Normal']				= $arrSubscript;

// Passed Parameters
$arrSubscript = Array();
$arrSubscript['Command']			= 'php test_loop.php <Loops>';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'process/';
$arrConfig['Parameters']			= $arrSubscript;

// Child Die Test
$arrSubscript = Array();
$arrSubscript['Command']			= 'php test_loop.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'process/';
$arrSubscript['ChildDie']			= TRUE;
$arrConfig['ChildDie']				= $arrSubscript;

// Normal 2 (to make sure Child Die is working correctly)
$arrSubscript = Array();
$arrSubscript['Command']			= 'php test_loop.php';
$arrSubscript['Directory']			= BACKEND_BASE_PATH.'process/';
$arrConfig['Normal']				= $arrSubscript;

?>