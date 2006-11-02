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

// start a report

$rptReport = New Report('flame', 'flame@telcoblue.com.au', 'bash');
$rptReport->AddMessage('this is a test message');
$rptReport->Finish();

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
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(5)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		//$arrDefine['Column'][$strName]['Default'] 		= "";
		//$arrDefine['Column'][$strName]['Attributes'] 	= "";
	
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;

echo "<pre>";
print_r($GLOBALS['arrDatabaseTableDefine']);
echo "</pre>";

// create the table
	$crqQuery = new QueryCreate();
	$bolReturn = $crqQuery->Execute("TestTable");
	if ($bolReturn === FALSE)
	{
		echo "Create Table Failed<br>\n";
	}
	elseif ($bolReturn === TRUE)
	{
		echo "Creat Table Successful<br>\n";
	}

// insert into the table
	$data['TestColumn'] = "String of test data";
	$insInsertStatment = new StatementInsert("TestTable");
	if ($insInsertStatment->Execute($data))
	{
		echo("Insert Successful!<br>\n");
	}
	else
	{
		echo("Insert Failed!<br>\n");
	}

// select * from the table
	$selSelectStatement = new StatementSelect("TestTable", "*");
	$selSelectStatement->Execute();
	print_r($selSelectStatement->FetchAll());
	
// select * with where from the table
	$selSelectStatement = new StatementSelect("TestTable", "*", "TestColumn LIKE <testalias>");
	$selSelectStatement->Execute(Array("testalias" => "%r%"));
	print_r($selSelectStatement->FetchAll());

// update the table
	$updUpdateStatement = new StatementUpdate("TestTable", "TestColumn LIKE <testcol>");
	if ($updUpdateStatement->Execute(Array("Changed test text"), Array("testcol" => "%tr%")))
	{
		echo("Update Successful!<br>\n");
	}
	else
	{
		echo("Update Failed!<br>\n");
	}
	
	$errErrorHandler = new ErrorHandler();
	
	// Non-fatal ExceptionVixen
	try
	{
		throw new ExceptionVixen("Just an exception test ;)!~~~", $errErrorHandler, NON_FATAL_TEST_EXCEPTION);
	}
	catch(ExceptionVixen $exvException)
	{
		echo("Caught Exception: " . $exvException->getMessage() . "\n");
	}
	/*
	// Fatal ExceptionVixen
	try
	{
		throw new ExceptionVixen("Fatal exception test ;)!~~~", $errErrorHandler, FATAL_TEST_EXCEPTION);
	}
	catch(ExceptionVixen $exvException)
	{
		// Should this run?
		echo("Caught Exception: " . $exvException->getMessage() . "\n");
	}*/
	
	// Fatal exception
	throw new Exception("Fatal exception test", FATAL_TEST_EXCEPTION);
 
// oh, and say hello world while we are at it 
//echo "hello world";


?>
