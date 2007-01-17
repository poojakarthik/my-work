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
	
	// Start the Error String
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Store the UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	
	$oblstrFirstName	= $oblarrUIValues->Push (new dataString ('FirstName',	$empEmployee->Pull ('FirstName')->getValue ()));
	$oblstrLastName		= $oblarrUIValues->Push (new dataString ('LastName',	$empEmployee->Pull ('LastName')->getValue ()));
	$oblstrUserName		= $oblarrUIValues->Push (new dataString ('UserName',	$empEmployee->Pull ('UserName')->getValue ()));
	$oblbolArchive		= $oblarrUIValues->Push (new dataBoolean('Archived'));
	
	if ($_POST ['FirstName'])	$oblstrFirstName->setValue	($_POST ['FirstName']);
	if ($_POST ['LastName'])	$oblstrLastName->setValue	($_POST ['LastName']);
	if ($_POST ['UserName'])	$oblstrUserName->setValue 	($_POST ['UserName']);
	if ($_POST ['Archived'])	$oblbolArchive->setValue 	(isset ($_POST ['Archived']) ? TRUE : FALSE);
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_POST ['Id'])
	{
		if ($_POST ['PassWord']['0'] <> $_POST ['PassWord']['1'])
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
	
	$Style->Output ('xsl/content/employee/edit.xsl');
	
?>
