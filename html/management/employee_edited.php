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
	
	try
	{
		// Using GET
		$empEmployee = $Style->attachObject (new Employee ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		// If the account does not exist, an exception will be thrown
		$Style->Output ('xsl/content/employee/notfound.xsl');
		exit;
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Employee');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output (
		'xsl/content/employee/edited.xsl',
		Array (
			"Employees"		=> TRUE,
			"Employee"		=> $empEmployee->Pull ('Id')->getValue ()
		)
	);
	
?>
