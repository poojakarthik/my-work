<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	require_once('../framework/functions.php');
	LoadFramework();
	// call application loader
	// Check if the person is logged in because we don't want unauthenticated users
	// to try logging authenticated users out

	// Updating information
	$Update = Array ("SessionExpire" => new MySQLFunction ("NOW()"), "SessionId" => "");
	
	// update the table
	$updUpdateStatement = new StatementUpdate("Employee", "Id = <Id> AND SessionId = <SessionId> AND Archived = 0", $Update);
	
	// If we successfully update the database table
	$updUpdateStatement->Execute($Update, Array("Id" => $_COOKIE ['Id'], "SessionId" => $_COOKIE ['SessionId']));
	
	// Unset the cookies so we don't have to bother checking them
	setCookie ("Id", "", time () - 3600);
	setCookie ("SessionId", "", time () - 3600);

	header('Location: ' . $_SERVER['HTTP_REFERER']);
	exit;
	
?>
