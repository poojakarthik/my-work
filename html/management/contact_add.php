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
	$arrPage['Modules']		= MODULE_BASE | MODULE_CONTACT | MODULE_ACCOUNT | MODULE_TITLE;
	
	// call application
	require ('config/application.php');
	
	// Start the Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Attempt to get the Account that we are associating this Contact with.
	// If the Account cannot be found, then an Error needs to be Shown.
	
	try
	{
		// Retrieve the Account
		$actAccount = $Style->attachObject (
			new Account (
				isset ($_GET ['Account']) ? $_GET ['Account'] : $_POST ['Account']
			)
		);
	}
	catch (Exception $e)
	{
		// Display an error if no Account is Found
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Retrieve the TitleTypes list
	$ttyTitleTypes = $Style->attachObject (new TitleTypes);
	
	// If we are at a point where we want to save Contact Information
	if (isset ($_POST ['CustomerContact']))
	{
		if (!$_POST ['FirstName'])
		{
			// Check that a First Name was passed through
			$oblstrError->setValue ('FirstName');
		}
		else if (!$_POST ['LastName'])
		{
			// Check that a Last Name was passed through
			$oblstrError->setValue ('LastName');
		}
		else if (!@checkdate ((int) $_POST ['DOB']['month'], (int) $_POST ['DOB']['day'], (int) $_POST ['DOB']['year']))
		{
			// Check the DOB passed through was Valid
			$oblstrError->setValue ('DOB');
		}
		else if (!$_POST ['Email'])
		{
			// Check that an Email Address was passed through
			$oblstrError->setValue ('Email');
		}
		else if (!EmailAddressValid ($_POST ['Email']))
		{
			// Check that the Email Address that was passed through is valid
			$oblstrError->setValue ('Email Invalid');
		}
		else if (!$_POST ['Phone'] && !$_POST ['Mobile'])
		{
			// Check that either a Phone or Mobile was passed through
			$oblstrError->setValue ('Phones Empty');
		}
		else if ($_POST ['Phone'] && !PhoneNumberValid ($_POST ['Phone']))
		{
			// If a Phone number is Entered, check that it is valid
			$oblstrError->setValue ('Phone Invalid');
		}
		else if ($_POST ['Mobile'] && !PhoneNumberValid ($_POST ['Mobile']))
		{
			// If a Mobile number is Entered, check that it is valid
			$oblstrError->setValue ('Mobile Invalid');
		}
		else if ($_POST ['Fax'] && !PhoneNumberValid ($_POST ['Fax']))
		{
			// If a Fax number is Entered, check that it is valid
			$oblstrError->setValue ('Fax Invalid');
		}
		else if (!$_POST ['PassWord'])
		{
			// Check that a Pass Word was passed through
			$oblstrError->setValue ('PassWord');
		}
		else
		{
			// The following TRY block ensures that the Username
			// requested does not currently exist within the system.
			
			// If it doesn't exist, the TRY block will catch an error
			// and the value of $cntContact will be undefined.
			
			// If it does exist, the TRY block will execute appropriately
			// and the value of $cntContact will be an Object of the 
			// Username
			
			try
			{
				$cntContact = Contacts::UnarchivedUsername ($_POST ['Email']);
			}
			catch (Exception $e)
			{
			}
			
			// Using the Information from the TRY block above, if $cntContact
			// is defined, then we have a duplicate User Name. In this scenario,
			// we want to display an error to the screen.
			
			if ($cntContact)
			{
				// Display the "User Name Exists" error
				$oblstrError->setValue ('Email Not Unique');
			}
			else
			{
				// Attempt to add the Contact into the Database. In this situation, 
				// the value returned by Contacts::Add () will be an Integer 
				// representing the Id of the new Contact
				$intContact = Contacts::Add (
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
						"PassWord"			=> $_POST ['PassWord'],
						"CustomerContact"	=> $_POST ['CustomerContact'] == 1
					)
				);
				
				// Forward to the Contact View page for the Contact
				// we've just created
				header ("Location: contact_view.php?Id=" . $intContact);
				exit;
			}
		}
		
		// If we Failed in our ability to add Contact Information, then start
		// a ui-values array so we can recall the information that was sent
		// in the POST.
		
		$oblarrContact = $Style->attachObject (new dataArray ('ui-values'));
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
		$oblarrContact->Push (new dataString	('PassWord'			, $_POST ['PassWord']));
		$oblarrContact->Push (new dataBoolean	('CustomerContact'	, $_POST ['CustomerContact'] == 1));
	}
	
	// Pull Documentation Information about the Account and Contact
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Contact");
	
	// Output the Contact Add page to the browser
	$Style->Output (
		'xsl/content/contact/add.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
