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
	$arrPage['Modules']		= MODULE_BASE | MODULE_CONTACT | MODULE_ACCOUNT;
	
	// call application
	require ('config/application.php');
	
	// Start the Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error', ''));
	
	
	// Try getting the account
	try
	{
		$actAccount = $Style->attachObject (
			new Account (
				isset ($_GET ['Account']) ? $_GET ['Account'] : $_POST ['Account']
			)
		);
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	if (isset ($_POST ['CustomerContact']))
	{
		$oblarrContact = $Style->attachObject (new dataArray ('Contact'));
		
		if (!$_POST ['Title'])
		{
			$oblstrError->setValue ('Title');
		}
		else if (!$_POST ['FirstName'])
		{
			$oblstrError->setValue ('FirstName');
		}
		else if (!$_POST ['LastName'])
		{
			$oblstrError->setValue ('LastName');
		}
		else if (!@checkdate ((int) $_POST ['DOB']['month'], (int) $_POST ['DOB']['day'], (int) $_POST ['DOB']['year']))
		{
			$oblstrError->setValue ('DOB');
		}
		else if (!$_POST ['Email'])
		{
			$oblstrError->setValue ('Email');
		}
		else if (!$_POST ['Phone'] && !$_POST ['Mobile'])
		{
			$oblstrError->setValue ('Phones Empty');
		}
		else if (!$_POST ['UserName'])
		{
			$oblstrError->setValue ('UserName Empty');
		}
		else if (!$_POST ['PassWord'])
		{
			$oblstrError->setValue ('PassWord');
		}
		else
		{
			try
			{
				$cntContact = Contacts::UnarchivedUsername ($_POST ['UserName']);
			}
			catch (Exception $e)
			{
			}
			
			if ($cntContact)
			{
				$oblstrError->setValue ('UserName Exists');
			}
			else
			{
				$ctsContacts = new Contacts ();
				$intContact = $ctsContacts->Add (
					$actAccount,
					Array (
						"Title"				=> $_POST ['Title'],
						"FirstName"			=> $_POST ['FirstName'],
						"LastName"			=> $_POST ['LastName'],
						"JobTitle"			=> $_POST ['JobTitle'],
						"DOB:day"			=> $_POST ['DOB']['day'],
						"DOB:month"			=> $_POST ['DOB']['month'],
						"DOB:year"			=> $_POST ['DOB']['year'],
						"Email"				=> $_POST ['Email'],
						"Phone"				=> $_POST ['Phone'],
						"Mobile"			=> $_POST ['Mobile'],
						"Fax"				=> $_POST ['Fax'],
						"UserName"			=> $_POST ['UserName'],
						"PassWord"			=> $_POST ['PassWord'],
						"CustomerContact"	=> $_POST ['CustomerContact'] == 1
					)
				);
				
				header ("Location: contact_view.php?Id=" . $intContact);
				exit;
			}
		}
		
		$oblarrContact->Push (new dataString	('Title'			, $_POST ['Title']));
		$oblarrContact->Push (new dataString	('FirstName'		, $_POST ['FirstName']));
		$oblarrContact->Push (new dataString	('LastName'			, $_POST ['LastName']));
		$oblarrContact->Push (new dataString	('JobTitle'			, $_POST ['JobTitle']));
		$oblarrContact->Push (new dataInteger	('DOB-day'			, $_POST ['DOB']['day']));
		$oblarrContact->Push (new dataInteger	('DOB-month'		, $_POST ['DOB']['month']));
		$oblarrContact->Push (new dataInteger	('DOB-year'			, $_POST ['DOB']['year']));
		$oblarrContact->Push (new dataString	('Email'			, $_POST ['Email']));
		$oblarrContact->Push (new dataString	('Phone'			, $_POST ['Phone']));
		$oblarrContact->Push (new dataString	('Mobile'			, $_POST ['Mobile']));
		$oblarrContact->Push (new dataString	('Fax'				, $_POST ['Fax']));
		$oblarrContact->Push (new dataString	('UserName'			, $_POST ['UserName']));
		$oblarrContact->Push (new dataString	('PassWord'			, $_POST ['PassWord']));
		$oblarrContact->Push (new dataBoolean	('CustomerContact'	, $_POST ['CustomerContact'] == 1));
	}
	
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Contact");
	
	$Style->Output ('xsl/content/contact/add.xsl');
	
?>
