<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */





// TEST

// define a database table
	// clean reused temporary array
	unset($arrDefine);
	
	// Define Table
	$arrDefine['Name']		= "TestTable";
	$arrDefine['Type']		= "MYISAM";
	$arrDefine['Id']		= "Id";
	//$arrDefine['Index'][] 		= "";
	//$arrDefine['Unique'][] 		= "";
	
	// Define Columns
	$strName = "TestColumn";
		$arrDefine['Column'][$strName]['Type'] 			= "string";
		$arrDefine['Column'][$strName]['SqlType'] 		= "s";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		//$arrDefine['Column'][$strName]['Default'] 		= "";
		//$arrDefine['Column'][$strName]['Attributes'] 	= "";
	
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$define['Name']] = $define;


// create the table
	$crqQuery = new QueryCreate();
	$crqQuery->Execute("TestTable");

// insert into the table
	$insInsertStatment = new StatementInsert("TestTable");
	if ($insInsertStatment->Execute("String of test data"))
	{
		echo("Insert Successful!");
	}
	else
	{
		echo("Insert Failed!");
	}

// select from the table
	$selSelectStatement = new StatementSelect("TestTable", "*");
	print_r($arrSelectTest = $selSelectStatement->Execute());

// update the table
	$updUpdateStatement = new StatementUpdate("TestTable", "TestColumn LIKE <testcol>");
	if ($updUpdateStatement->Execute(Array("TestColumn"), Array("testcol" => "Changed test text")))
	{
		echo("Update Successful!");
	}
	else
	{
		echo("Update Failed!");
	}
 
// oh, and say hello world while we are at it 
echo "hello world";


?>
