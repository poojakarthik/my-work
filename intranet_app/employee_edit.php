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
		if ($_SERVER ['REQUEST_METHOD'] == "GET")
		{
			// Using GET
			$empEmployee = $Style->attachObject (new Employee ($_GET ['Id']));
		}
		else
		{
			// Using POST
			$empEmployee = $Style->attachObject (new Employee ($_POST ['Id']));
		}
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
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_POST ['Id'])
	{
		$empEmployee->Update (
			Array (
				"FirstName"		=> $_POST ['FirstName'],
				"LastName"		=> $_POST ['LastName'],
				"UserName"		=> $_POST ['UserName'],
				"PassWord"		=> $_POST ['PassWord'],
				"Priviledges"	=> $_POST ['Priviledges']
			)
		);
		
		if (isset ($_POST ['Archived']))
		{
			$empEmployee->ArchiveStatus ($_POST ['Archived']);
		}
		
		header ("Location: employee_edited.php?Id=" . $empEmployee->Pull ('Id')->getValue ());
		exit;
	}
	
	$Style->Output ('xsl/content/employee/edit.xsl');
	
?>
