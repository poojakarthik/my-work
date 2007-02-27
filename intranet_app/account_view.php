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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_CONTACT | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_RATE_PLAN | MODULE_STATE | MODULE_BILLING | MODULE_CUSTOMER_GROUP;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Billing');
	$docDocumentation->Explain ('Payment');
	$docDocumentation->Explain ('CustomerGroup');
	
	try
	{
		// Get the Account
		$actAccount		= $Style->attachObject (new Account ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Get Overdue Amount
	$actAccount->OverdueAmount ();
	
	// Grab State
	$sstState		= $Style->attachObject (new ServiceStateType ($actAccount->Pull ('State')->getValue ()));
	
	// Billing Methods
	$bmeBillingMethods = $Style->attachObject (new BillingMethod ($actAccount->Pull ('BillingMethod')->getValue ()));
	
	// Billing Type
	// XPath: /Response/BillingType/Name
	$btyBillingType = $Style->attachObject (new BillingType ($actAccount->Pull ('BillingType')->getValue ()));
	
	// Billing Type
	// XPath: /Response/CustomerGroup/Name
	$cgrCustomerGroup = $Style->attachObject (new CustomerGroup ($actAccount->Pull ('CustomerGroup')->getValue ()));
	
	// Get Associated Services
	$svsServices	= $Style->attachObject (new Services);
	$svsServices->Constrain ('Account', '=', $_GET ['Id']);
	$svsServices->Order ('FNN', TRUE);
	$oblsamServices = $svsServices->Sample ();
	
	foreach ($oblsamServices as $srvService)
	{
		$srvService->Plan ();
	}
	
	// Get information about Note Types
	$ntsNoteTypes	= $Style->attachObject (new NoteTypes);
	
	// Get Associated Notes
	$nosNotes = $Style->attachObject (new Notes);
	$nosNotes->Constrain ('Account', '=', $_GET ['Id']);
	$nosNotes->Sample (1, 5);
	
	// Get all the Contacts
	$ctsContacts	= $Style->attachObject ($actAccount->Contacts ());
	
	// Account Balance
	$Style->attachObject (new dataFloat ('AccountBalance', $GLOBALS['fwkFramework']->GetAccountBalance ($actAccount->Pull ('Id')->getValue ())));
	
	// Output the Account View
	$Style->Output ('xsl/content/account/view.xsl');
	
?>
