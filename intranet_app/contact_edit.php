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
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CONTACT;
	
	// call application
	require ('config/application.php');
	
	try
	{
		if ($_SERVER ['REQUEST_METHOD'] == "GET")
		{
			// Using GET
			$cntContact = $Style->attachObject (new Contact ($_GET ['Id']));
		}
		else
		{
			// Using POST
			$cntContact = $Style->attachObject (new Contact ($_POST ['Id']));
		}
	}
	catch (Exception $e)
	{
		// If the account does not exist, an exception will be thrown
		$Style->Output ('xsl/content/contact/notfound.xsl');
		exit;
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('Archive');
	
	$oblstrError = $Style->attachObject (new dataString ('Error', ''));
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		if ($cntContact->Pull ('Archived')->getValue () == 0)
		{
			$selUserNames = new StatementSelect ('Contact', 'Id', 'UserName = <UserName> AND Archived = 0 AND Id != <Id>', null, 1);
			$selUserNames->Execute (Array ('UserName' => $_POST ['UserName'], 'Id' => $cntContact->Pull ('Id')->getValue ()));
		}
		
		if (!$_POST ['Email'])
		{
			$oblstrError->setValue ('Email');
		}
		else if ($cntContact->Pull ('Archived')->getValue () == 0 && $selUserNames->Count () <> 0)
		{
			$oblstrError->setValue ('UserName');
		}
		else
		{
			$cntContact->Update (
				Array (
					'Title'					=> $_POST ['Title'],
					'FirstName'			=> $_POST ['FirstName'],
					'LastName'				=> $_POST ['LastName'],
					'DOB-year'				=> $_POST ['DOB']['year'],
					'DOB-month'			=> $_POST ['DOB']['month'],
					'DOB-day'				=> $_POST ['DOB']['day'],
					'JobTitle'				=> $_POST ['JobTitle'],
					'Email'				=> $_POST ['Email'],
					'CustomerContact'		=> ($_POST ['CustomerContact'] == 1),
					'Phone'					=> $_POST ['Phone'],
					'Mobile'				=> $_POST ['Mobile'],
					'Fax'					=> $_POST ['Fax'],
					'UserName'				=> $_POST ['UserName'],
					'PassWord'				=> $_POST ['PassWord']
				)
			);
			
			try
			{
				if (isset ($_POST ['Archived']))
				{
					$cntContact->ArchiveStatus ($_POST ['Archived'] == 1);
				}
			}
			catch (Exception $e)
			{
				$Style->Output ('xsl/content/contact/edit_failed_archive_username.xsl');
				exit;
			}
			
			header ("Location: contact_view.php?Id=" . $cntContact->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	$Style->Output ('xsl/content/contact/edit.xsl');
	
?>
