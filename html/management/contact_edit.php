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
	$arrPage['Modules']		= MODULE_BASE | MODULE_CONTACT | MODULE_TITLE;
	
	// call application
	require ('config/application.php');
	
	
	// Firstly, attempt to retrieve the Contact that we are trying to view.
	// From here, we also want to retireve the Primary Account that this
	// particular contact belongs to.
	
	// If any errors occur in this retrieval process, then we want to 
	// exit out of this Script displaying an Error Message
	try
	{
		// Retrieve the Contact (either by GET or POST)
		$cntContact = $Style->attachObject (new Contact (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
		
		// Get the Primary Account for this Contact
		$actAccount = $cntContact->PrimaryAccount ();
	}
	catch (Exception $e)
	{
		// If the Contact does not exist, an exception will be thrown.
		// Handle this Exception by displaying a Contact Not Found page.
		$Style->Output ('xsl/content/contact/notfound.xsl');
		exit;
	}
	
	// Error Handling
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	$ttsTitleTypes = $Style->attachObject (new TitleTypes);
	
	// Start UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrTitle			= $oblarrUIValues->Push (new dataString ('Title'			, $cntContact->Pull ('Title')->getValue ()));
	$oblstrFirstName		= $oblarrUIValues->Push (new dataString ('FirstName'		, $cntContact->Pull ('FirstName')->getValue ()));
	$oblstrLastName			= $oblarrUIValues->Push (new dataString ('LastName'			, $cntContact->Pull ('LastName')->getValue ()));
	$oblstrJobTitle			= $oblarrUIValues->Push (new dataString ('JobTitle'			, $cntContact->Pull ('JobTitle')->getValue ()));
	$oblstrDOB_Year			= $oblarrUIValues->Push (new dataString ('DOB-year'));
	$oblstrDOB_Month		= $oblarrUIValues->Push (new dataString ('DOB-month'));
	$oblstrDOB_Day			= $oblarrUIValues->Push (new dataString ('DOB-day'));
	$oblstrEmail			= $oblarrUIValues->Push (new dataString ('Email'			, $cntContact->Pull ('Email')->getValue ()));
	$oblstrPhone			= $oblarrUIValues->Push (new dataString ('Phone'			, $cntContact->Pull ('Phone')->getValue ()));
	$oblstrMobile			= $oblarrUIValues->Push (new dataString ('Mobile'			, $cntContact->Pull ('Mobile')->getValue ()));
	$oblstrFax				= $oblarrUIValues->Push (new dataString ('Fax'				, $cntContact->Pull ('Fax')->getValue ()));
	//$oblstrUserName			= $oblarrUIValues->Push (new dataString ('UserName'			, $cntContact->Pull ('UserName')->getValue ()));
	//$oblstrUserName			= $oblarrUIValues->Push (new dataString ('UserName'			, $cntContact->Pull ('Email')->getValue ()));
	$oblstrPassWord			= $oblarrUIValues->Push (new dataString ('PassWord'			, ''));
	$oblbolCustomerContact	= $oblarrUIValues->Push (new dataBoolean('CustomerContact'	, $cntContact->Pull ('CustomerContact')->getValue ()));
	
	// If there is a DOB currently associated with a Contact, then
	// store the DOB inforamtion. This check is done because if no
	// DOB exist, you cannot pull the 'year' value from DOB
	if ($cntContact->Pull ('DOB')->Pull ('year'))
	{
		$oblstrDOB_Year->setValue	($cntContact->Pull ('DOB')->Pull ('year')->getValue ());
		$oblstrDOB_Month->setValue	($cntContact->Pull ('DOB')->Pull ('month')->getValue ());
		$oblstrDOB_Day->setValue	($cntContact->Pull ('DOB')->Pull ('day')->getValue ());
	}
	
	// We can identify whether or not we're planning
	// to save information depending on whether
	// or not we are viewing this page through POST
	
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		// The following IF block deals with ensuring that Duplicate Usernames are not
		// entered into the System. It will be executed based on the following conditions:
		//		1. If we're dealing with an Active Contact
		//	or	2. We're dealing with an Unarchived Contact and we wish to Re-activate their account
		
		// To makes things easier down below when we're processing, we're going to be using
		// the following variable to remember Validity of a Username
		$bolUserNameTaken = FALSE;
		
		if (($cntContact->Pull ('Archived')->isTrue () && isset ($_POST ['Archived'])) || $cntContact->Pull ('Archived')->isFalse ())
		{
			// A username is not duplicated if no *other* Archive (Unarchived) 
			// Contact exists with the Same Username
			$selUserNames = new StatementSelect ('Contact', 'Id', 'Email = <UserName> AND Archived = 0 AND Id != <Id>', null, 1);
			$selUserNames->Execute (Array ('UserName' => $_POST ['Email'], 'Id' => $cntContact->Pull ('Id')->getValue ()));
			$bolUserNameTaken = ($selUserNames->Count () == 1);
		}
		if (!$_POST ['FirstName'])
		{
			// Ensure that the Contact has a First Name
			$oblstrError->setValue ('FirstName');
		}
		else if (!$_POST ['LastName'])
		{
			// Ensure that the Contact has a Last Name
			$oblstrError->setValue ('LastName');
		}
		else if (!@checkdate ((int) $_POST ['DOB']['month'], (int) $_POST ['DOB']['day'], (int) $_POST ['DOB']['year']))
		{
			// Ensure that the Contact has a Valid DOB
			$oblstrError->setValue ('DOB');
		}
		else if (!$_POST ['Email'])
		{
			// Ensure that the Contact has an Email
			$oblstrError->setValue ('Email');
		}
		else if (!EmailAddressValid ($_POST ['Email']))
		{
			// Ensure that the Contact has a valid Email
			$oblstrError->setValue ('Email Invalid');
		}
		else if (!$_POST ['Phone'] && !$_POST ['Mobile'])
		{
			// Ensure that the Contact has either a Phone or Mobile number
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
		//else if (!$_POST ['UserName'])
		//{
			// Ensure that the Contact has a User Name
			//$oblstrError->setValue ('UserName Empty');
		//}
		else if ($bolUserNameTaken)
		{
			// This error is Displayed when the Username being requested already
			// exists on another Contact in the Database. The value of $bolUserNameTaken
			// (above) decided in a previous block.
			$oblstrError->setValue ('Email Exists');
		}
		else
		{
			// Update the Contact Information
			$cntContact->Update (
				Array (
					'Title'				=> $_POST ['Title'],
					'FirstName'			=> $_POST ['FirstName'],
					'LastName'			=> $_POST ['LastName'],
					'DOB-year'			=> $_POST ['DOB']['year'],
					'DOB-month'			=> $_POST ['DOB']['month'],
					'DOB-day'			=> $_POST ['DOB']['day'],
					'JobTitle'			=> $_POST ['JobTitle'],
					'Email'				=> $_POST ['Email'],
					'CustomerContact'	=> ($_POST ['CustomerContact'] == 1),
					'Phone'				=> $_POST ['Phone'],
					'Mobile'			=> $_POST ['Mobile'],
					'Fax'				=> $_POST ['Fax'],
					'PassWord'			=> $_POST ['PassWord']
				)
			);
			
			// If we want to change the Archive Status of the Contact, then check that
			// the Archived variable is set. Notice that we are using ISSET here, this 
			// is because the value of $_POST ['Archived'] may be 0 (in which case 
			// means we want to Unarchive the Account).
			
			if (isset ($_POST ['Archived']))
			{
				$cntContact->ArchiveStatus ($_POST ['Archived'] == 1);
			}
			
			// Forward on to View the Contact's Information
			header ("Location: contact_view.php?Id=" . $cntContact->Pull ('Id')->getValue ());
			exit;
		}
		
		// Set the UI Values
		$oblstrTitle->setValue			($_POST ['Title']);
		$oblstrFirstName->setValue		($_POST ['FirstName']);
		$oblstrLastName->setValue		($_POST ['LastName']);
		$oblstrJobTitle->setValue		($_POST ['JobTitle']);
		$oblstrDOB_Year->setValue		($_POST ['DOB']['year']);
		$oblstrDOB_Month->setValue		($_POST ['DOB']['month']);
		$oblstrDOB_Day->setValue		($_POST ['DOB']['day']);
		$oblstrEmail->setValue			($_POST ['Email']);
		$oblstrPhone->setValue			($_POST ['Phone']);
		$oblstrMobile->setValue			($_POST ['Mobile']);
		$oblstrFax->setValue			($_POST ['Fax']);
		//$oblstrUserName->setValue		($_POST ['UserName']);
		$oblstrPassWord->setValue		($_POST ['PassWord']);
		$oblbolCustomerContact->setValue($_POST ['CustomerContact']);
	}
	
	// Pull documentation information for a Contact
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('Archive');
	
	// Output the Contact Edit page
	$Style->Output (
		'xsl/content/contact/edit.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue (),
			'Contact'	=> $cntContact->Pull ('Id')->getValue ()
		)
	);
	
?>
