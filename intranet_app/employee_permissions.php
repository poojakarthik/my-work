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
	$arrPage['Modules']		= MODULE_BASE | MODULE_EMPLOYEE | MODULE_PERMISSION;
	
	// call application
	require ('config/application.php');
	
	try
	{
		if ($_GET ['Id'])
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
	
	// We're checking here for Confirm because the SelectedPermissions selection
	// list may not exist. This would be caused by wishing to set No permissions
	// for a particular employee
	
	if ($_POST ['Confirm'])
	{
		$empEmployee->PermissionsSet (
			$athAuthentication->AuthenticatedEmployee (),
			$_POST ['SelectedPermissions']
		);
		
		header ("Location: employee_permissioned.php?Id=" . $empEmployee->Pull ('Id')->getValue ());
		exit;
	}
	
	// Start the Permission List
	$prmPermissions = $Style->attachObject (new Permissions);
	
	// Get a list of Permissions the Employee has Access to
	$prlPermissions = $Style->attachObject ($empEmployee->PermissionList ());
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Employee');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output ('xsl/content/employee/permissions.xsl');
	
?>
