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
	
	
	// Start the Error String
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Store the UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	
	$oblstrFirstName	= $oblarrUIValues->Push (new dataString ('FirstName'));
	$oblstrLastName		= $oblarrUIValues->Push (new dataString ('LastName'));
	$oblstrEmail		= $oblarrUIValues->Push (new dataString ('Email'));
	$oblstrExtension	= $oblarrUIValues->Push (new dataString ('Extension'));
	$oblstrPhone		= $oblarrUIValues->Push (new dataString ('Phone'));
	$oblstrMobile		= $oblarrUIValues->Push (new dataString ('Mobile'));
	$oblstrUserName		= $oblarrUIValues->Push (new dataString ('UserName'));
	$oblintDOBYear		= $oblarrUIValues->Push (new dataInteger('DOB-year'));
	$oblintDOBMonth		= $oblarrUIValues->Push (new dataInteger('DOB-month'));
	$oblintDOBDay		= $oblarrUIValues->Push (new dataInteger('DOB-day'));
	
	if ($_POST ['FirstName'])	$oblstrFirstName->setValue	($_POST ['FirstName']);
	if ($_POST ['LastName'])	$oblstrLastName->setValue	($_POST ['LastName']);
	if ($_POST ['Email'])		$oblstrEmail->setValue		($_POST ['Email']);
	if ($_POST ['Extension'])	$oblstrExtension->setValue	($_POST ['Extension']);
	if ($_POST ['Phone'])		$oblstrPhone->setValue		($_POST ['Phone']);
	if ($_POST ['Mobile'])		$oblstrMobile->setValue		($_POST ['Mobile']);
	if ($_POST ['UserName'])	$oblstrUserName->setValue 	($_POST ['UserName']);
	if ($_POST ['DOB-year'])	$oblintDOBYear->setValue	($_POST ['DOB-year']);
	if ($_POST ['DOB-month'])	$oblintDOBMonth->setValue	($_POST ['DOB-month']);
	if ($_POST ['DOB-day'])		$oblintDOBDay->setValue 	($_POST ['DOB-day']);
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_SERVER ['REQUEST_METHOD'] == 'POST')
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
		else if ($_POST ['PassWord']['0'] <> $_POST ['PassWord']['1'])
		{
			$oblstrError->setValue ('Password Mismatch');
		}
		else if (!$_POST ['PassWord']['0'])
		{
			$oblstrError->setValue ('Password Empty');
		}
		else
		{
			try
			{
				$empEmployee = Employees::Add (
					$athAuthentication->AuthenticatedEmployee (),
					Array (
						"FirstName"		=> $_POST ['FirstName'],
						"LastName"		=> $_POST ['LastName'],
						"Email"			=> $_POST ['Email'],
						"Extension"		=> $_POST ['Extension'],
						"Phone"			=> $_POST ['Phone'],
						"Mobile"		=> $_POST ['Mobile'],
						"UserName"		=> $_POST ['UserName'],
						"DOB-year"		=> $_POST ['DOB-year'],
						"DOB-month"		=> $_POST ['DOB-month'],
						"DOB-day"		=> $_POST ['DOB-day'],
						"PassWord"		=> $_POST ['PassWord']['0']
					)
				);
				
				header ("Location: employee_added.php?Id=" . $empEmployee->Pull ('Id')->getValue ());
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
		'xsl/content/employee/add.xsl',
		Array (
			"Employees"		=> TRUE
		)
	);
	
?>
