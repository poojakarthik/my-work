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
		$cntContact = $Style->attachObject (new Contact (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
		$actAccount = $cntContact->PrimaryAccount ();
	}
	catch (Exception $e)
	{
		// If the Contact does not exist, an exception will be thrown
		$Style->Output ('xsl/content/contact/notfound.xsl');
		exit;
	}
	
	// Error Handling
	$oblstrError = $Style->attachObject (new dataString ('Error', ''));
	
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
	$oblstrUserName			= $oblarrUIValues->Push (new dataString ('UserName'			, $cntContact->Pull ('UserName')->getValue ()));
	$oblstrPassWord			= $oblarrUIValues->Push (new dataString ('PassWord'			, ''));
	$oblbolCustomerContact	= $oblarrUIValues->Push (new dataBoolean('CustomerContact'	, $cntContact->Pull ('CustomerContact')->getValue ()));
	$oblbolArchived			= $oblarrUIValues->Push (new dataBoolean('Archived'));
	
	if ($cntContact->Pull ('DOB')->Pull ('year'))
	{
		$oblstrDOB_Year->setValue	($cntContact->Pull ('DOB')->Pull ('year')->getValue ());
		$oblstrDOB_Month->setValue	($cntContact->Pull ('DOB')->Pull ('month')->getValue ());
		$oblstrDOB_Day->setValue	($cntContact->Pull ('DOB')->Pull ('day')->getValue ());
	}
	
	// Set UI Values
	if (isset ($_POST ['Title']))			$oblstrTitle->setValue			($_POST ['Title']);
	if (isset ($_POST ['FirstName']))		$oblstrFirstName->setValue		($_POST ['FirstName']);
	if (isset ($_POST ['LastName']))		$oblstrLastName->setValue		($_POST ['LastName']);
	if (isset ($_POST ['JobTitle']))		$oblstrJobTitle->setValue		($_POST ['JobTitle']);
	if (isset ($_POST ['DOB']['year']))		$oblstrDOB_Year->setValue		($_POST ['DOB']['year']);
	if (isset ($_POST ['DOB']['month']))	$oblstrDOB_Month->setValue		($_POST ['DOB']['month']);
	if (isset ($_POST ['DOB']['day']))		$oblstrDOB_Day->setValue		($_POST ['DOB']['day']);
	if (isset ($_POST ['Email']))			$oblstrEmail->setValue			($_POST ['Email']);
	if (isset ($_POST ['Phone']))			$oblstrPhone->setValue			($_POST ['Phone']);
	if (isset ($_POST ['Mobile']))			$oblstrMobile->setValue			($_POST ['Mobile']);
	if (isset ($_POST ['Fax']))				$oblstrFax->setValue			($_POST ['Fax']);
	if (isset ($_POST ['UserName']))		$oblstrUserName->setValue		($_POST ['UserName']);
	if (isset ($_POST ['PassWord']))		$oblstrPassWord->setValue		($_POST ['PassWord']);
	if (isset ($_POST ['CustomerContact']))	$oblbolCustomerContact->setValue($_POST ['CustomerContact']);
	if (isset ($_POST ['Archived']))		$oblbolArchived->setValue(TRUE);
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		if ($cntContact->Pull ('Archived')->getValue () == 0)
		{
			$selUserNames = new StatementSelect ('Contact', 'Id', 'UserName = <UserName> AND Archived = 0 AND Id != <Id>', null, 1);
			$selUserNames->Execute (Array ('UserName' => $_POST ['UserName'], 'Id' => $cntContact->Pull ('Id')->getValue ()));
		}
		
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
		else if ($cntContact->Pull ('Archived')->getValue () == 0 && $selUserNames->Count () <> 0)
		{
			$oblstrError->setValue ('UserName');
		}
		else
		{
			try
			{
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
						'UserName'			=> $_POST ['UserName'],
						'PassWord'			=> $_POST ['PassWord']
					)
				);
				
				try
				{
					if (isset ($_POST ['Archived']))
					{
						// TODO!bash! based on your error msg (that's all there is to go on due to lack of comments here) it looks like
						// TODO!bash! you are failing here if an archived username is the same as another existing username...
						// TODO!bash! this should not fail, the archived user should have their username automatically changed (or removed)
						// TODO!bash! and a notice should be displayed to inform the opperator of this change
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
			catch (Exception $e)
			{
				$oblstrError->setValue ($e->getMessage ());
			}
		}
	}
	
	// Pull documentation information for a Contact
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output (
		'xsl/content/contact/edit.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue (),
			'Contact'	=> $cntContact->Pull ('Id')->getValue ()
		)
	);
	
?>
