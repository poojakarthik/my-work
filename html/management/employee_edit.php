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
	
	// Get the Employee
	try
	{
		$empEmployee = $Style->attachObject (new Employee (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
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
	// If the employee to be editted is a GOD user, and the current user isn't then redirect back to the Employee Listing page
	if ($empEmployee->Pull('Privileges')->getValue() == USER_PERMISSION_GOD && 
		$athAuthentication->AuthenticatedEmployee()->Pull('Privileges')->getValue() != USER_PERMISSION_GOD)
	{
		header("Location: ../admin/flex.php/Employee/EmployeeList/");
	}
	//HACK! HACK! HACK! 
	
	// Start the Error String
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Store the UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	
	$oblstrFirstName	= $oblarrUIValues->Push (new dataString ('FirstName',	$empEmployee->Pull ('FirstName')->getValue ()));
	$oblstrLastName		= $oblarrUIValues->Push (new dataString ('LastName',	$empEmployee->Pull ('LastName')->getValue ()));
	$oblstrEmail		= $oblarrUIValues->Push (new dataString ('Email',		$empEmployee->Pull ('Email')->getValue ()));
	$oblstrExtension	= $oblarrUIValues->Push (new dataString ('Extension',	$empEmployee->Pull ('Extension')->getValue ()));
	$oblstrPhone		= $oblarrUIValues->Push (new dataString ('Phone',		$empEmployee->Pull ('Phone')->getValue ()));
	$oblstrMobile		= $oblarrUIValues->Push (new dataString ('Mobile',		$empEmployee->Pull ('Mobile')->getValue ()));
	$oblstrUserName		= $oblarrUIValues->Push (new dataString ('UserName',	$empEmployee->Pull ('UserName')->getValue ()));
	$oblbolArchive		= $oblarrUIValues->Push (new dataBoolean('Archived'));
	
	if (isset ($_POST ['FirstName']))	$oblstrFirstName->setValue	($_POST ['FirstName']);
	if (isset ($_POST ['LastName']))	$oblstrLastName->setValue	($_POST ['LastName']);
	if (isset ($_POST ['Email']))		$oblstrEmail->setValue	($_POST ['Email']);
	if (isset ($_POST ['Extension']))	$oblstrExtension->setValue	($_POST ['Extension']);
	if (isset ($_POST ['Phone']))		$oblstrPhone->setValue	($_POST ['Phone']);
	if (isset ($_POST ['Mobile']))		$oblstrMobile->setValue	($_POST ['Mobile']);
	if (isset ($_POST ['UserName']))	$oblstrUserName->setValue 	($_POST ['UserName']);
	if (isset ($_POST ['Archived']))	$oblbolArchive->setValue 	(isset ($_POST ['Archived']) ? TRUE : FALSE);
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_POST ['Id'])
	{
		if ($_POST ['Email'] && !EmailAddressValid ($_POST ['Email']))
		{
			$oblstrError->setValue ('Email');
		}
		else if ($_POST ['Phone'] && !PhoneNumberValid ($_POST ['Phone']))
		{
			$oblstrError->setValue ('Phone');
		}
		else if ($_POST ['Mobile'] && !PhoneNumberValid ($_POST ['Mobile']))
		{
			$oblstrError->setValue ('Mobile');
		}
		else if (!$_POST ['UserName'])
		{
			$oblstrError->setValue ('Username Empty');
		}
		else if ($_POST ['PassWord']['0'] && $_POST ['PassWord']['0'] <> $_POST ['PassWord']['1'])
		{
			$oblstrError->setValue ('Password Mismatch');
		}
		else
		{
			try
			{
				$empEmployee->Update (
					$athAuthentication->AuthenticatedEmployee (),
					Array (
						"FirstName"		=> $_POST ['FirstName'],
						"LastName"		=> $_POST ['LastName'],
						"Email"			=> $_POST ['Email'],
						"Extension"		=> $_POST ['Extension'],
						"Phone"			=> $_POST ['Phone'],
						"Mobile"		=> $_POST ['Mobile'],
						"UserName"		=> $_POST ['UserName'],
						"PassWord"		=> $_POST ['PassWord']['0']
					)
				);
				
				if (isset ($_POST ['Archived']))
				{
					$empEmployee->ArchiveStatus ($_POST ['Archived']);
				}
				
				header ("Location: employee_edited.php?Id=" . $empEmployee->Pull ('Id')->getValue ());
				exit;
			}
			catch (Exception $e)
			{
				$oblstrError->setValue ($e->getMessage ());
			}
		}
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Employee');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output (
		'xsl/content/employee/edit.xsl',
		Array (
			"Employees"		=> TRUE
		)
	);
	
?>
