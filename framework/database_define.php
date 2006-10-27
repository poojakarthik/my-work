<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// database_define
//----------------------------------------------------------------------------//
/**
 * database_define
 *
 * Defines database tables for use in the application
 *
 * Defines database tables for use in the application
 *
 * @file		database_define.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */



//----------------------------------------------------------------------------//
// database table define format
//----------------------------------------------------------------------------//
 /*
	$arrDefine['Name']		= "";			// table name
	$arrDefine['Type']		= "MYISAM";		// defaults to	'MYISAM'
	$arrDefine['Id']		= "Id";			// defaults to	'Id'
	
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
	
	$arrDefine['Column'][$strName]['Type'] 			= "";			// Validation type: s, i etc
	$arrDefine['Column'][$strName]['SqlType'] 		= "";			// Sql Type: Char, Int etc
	$arrDefine['Column'][$strName]['Null'] 			= TRUE|FALSE;	// optional, defaults to FALSE (NOT NULL)
	$arrDefine['Column'][$strName]['Default'] 		= "";			// optional default value
	$arrDefine['Column'][$strName]['Attributes'] 	= "";			// optional attributes
 */



//----------------------------------------------------------------------------//
// skeleton table define
//----------------------------------------------------------------------------//
/*
	// clean reused temporary array
	unset($arrDefine);
	
	// Define Table
	$arrDefine['Name']		= "";
	$arrDefine['Type']		= "MYISAM";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
	
	// Define Columns
	$strName = "";
		$arrDefine['Column'][$strName]['Type'] 			= "";
		$arrDefine['Column'][$strName]['SqlType'] 		= "";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['Attributes'] 	= "";
	
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$define['Name']] = $define;
	
*/
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 ?>
