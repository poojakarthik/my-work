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
		$Style->Output (
			'xsl/content/employee/notfound.xsl',
			Array (
				"Employees"		=> TRUE
			)
		);
		exit;
	}
	
	//HACK! HACK! HACK!
	// If the employee to be editted is a GOD user, then redirect back to the Employee Listing page
	if ($empEmployee->Pull('Privileges')->getValue() == USER_PERMISSION_GOD)
	{
		header("Location: ../admin/flex.php/Employee/EmployeeList/");
	}
	//HACK! HACK! HACK! 
	
	//HACK! HACK! HACK!
	// Currently there is no way to define what privileges are required to grant particular privileges to others.
	// So long as you have admin privileges, you can give yourself SuperAdmin privileges, so for now, just remove it
	// from the list
	// Remove the SuperAdmin permission from the list of permissions
	unset($GLOBALS['Permissions'][PERMISSION_SUPER_ADMIN]);
	//HACK! HACK! HACK!
	
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
	
	$Style->Output (
		'xsl/content/employee/permissions.xsl',
		Array (
			"Employees"		=> TRUE,
			"Employee"		=> $empEmployee->Pull ('Id')->getValue ()
		)
	);
	
?>
