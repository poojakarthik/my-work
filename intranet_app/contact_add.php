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
	
	// Make sure an account is Defined
	if (!$_GET ['Account'] && !$_POST ['Account'])
	{
		header ('Location: console.php'); exit;
	}
	
	// Try getting the account
	try
	{
		if ($_GET ['Account'])
		{
			$actAccount = $Style->attachObject (new Account ($_GET ['Account']));
		}
		else if ($_POST ['Account'])
		{
			$actAccount = $Style->attachObject (new Account ($_POST ['Account']));
		}
	}
	catch (Exception $e)
	{
		header ('Location: console.php'); exit;
	}
	
	if (isset ($_POST ['CustomerContact']))
	{
		$oblarrContact = $Style->attachObject (new dataArray ('Contact'));
		
		if (!$_POST ['Email'])
		{
			$oblstrError->setValue ('Email');
		}
		else
		{
			$ctsContacts = new Contacts ();
			$intContact = $ctsContacts->Add (
				$actAccount,
				Array (
					"Title"				=> $_POST ['Title'],
					"FirstName"		=> $_POST ['FirstName'],
					"LastName"			=> $_POST ['LastName'],
					"JobTitle"			=> $_POST ['JobTitle'],
					"DOB:day"			=> $_POST ['DOB']['day'],
					"DOB:month"		=> $_POST ['DOB']['month'],
					"DOB:year"			=> $_POST ['DOB']['year'],
					"Email"			=> $_POST ['Email'],
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
		
		$oblarrContact->Push (new dataString	('Title'				, $_POST ['Title']));
		$oblarrContact->Push (new dataString	('FirstName'			, $_POST ['FirstName']));
		$oblarrContact->Push (new dataString	('LastName'			, $_POST ['LastName']));
		$oblarrContact->Push (new dataString	('JobTitle'				, $_POST ['JobTitle']));
		$oblarrContact->Push (new dataInteger	('DOB-day'				, $_POST ['DOB']['day']));
		$oblarrContact->Push (new dataInteger	('DOB-month'			, $_POST ['DOB']['month']));
		$oblarrContact->Push (new dataInteger	('DOB-year'				, $_POST ['DOB']['year']));
		$oblarrContact->Push (new dataString	('Email'				, $_POST ['Email']));
		$oblarrContact->Push (new dataString	('Phone'				, $_POST ['Phone']));
		$oblarrContact->Push (new dataString	('Mobile'				, $_POST ['Mobile']));
		$oblarrContact->Push (new dataString	('Fax'					, $_POST ['Fax']));
		$oblarrContact->Push (new dataString	('UserName'			, $_POST ['UserName']));
		$oblarrContact->Push (new dataString	('PassWord'				, $_POST ['PassWord']));
		$oblarrContact->Push (new dataBoolean	('CustomerContact'		, $_POST ['CustomerContact'] == 1));
	}
	
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Contact");
	
	$Style->Output ('xsl/content/contact/add.xsl');
	
?>
