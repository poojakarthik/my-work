<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	
	// Start a new Employee Search
	$emsEmployees = $Style->attachObject (new Employees(
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30));

	
	// Output the Result
	$Style->Output ('xsl/content/employee/list.xsl');
	
?>
