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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_STATE | MODULE_CUSTOMER_GROUP | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$actAccount = $Style->attachObject (new Account (isset ($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		// If the account does not exist, an exception will be thrown
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Define States
	// XPath: /Response/States/State ... 
	$sstStates				= $Style->attachObject (new ServiceStateTypes);
	
	// Define CustomerGroups
	// XPath: /Response/CustomerGroups/CustomerGroup ... 
	$cgrCustomerGroups		= $Style->attachObject (new CustomerGroups);
	
	// Define Billing Methods
	// XPath: /Response/BillingMethods/BillingMethod ... 
	$bmeBillingMethods		= $Style->attachObject (new BillingMethods);
	
	// Error Handling
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Start UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrBusinessName			= $oblarrUIValues->Push (new dataString ('BusinessName'			, $actAccount->Pull ('BusinessName')->getValue ()));
	$oblstrTradingName			= $oblarrUIValues->Push (new dataString ('TradingName'			, $actAccount->Pull ('TradingName')->getValue ()));
	$oblstrABN					= $oblarrUIValues->Push (new dataString ('ABN'					, $actAccount->Pull ('ABN')->getValue ()));
	$oblstrACN					= $oblarrUIValues->Push (new dataString ('ACN'					, $actAccount->Pull ('ACN')->getValue ()));
	$oblstrAddress1				= $oblarrUIValues->Push (new dataString ('Address1'				, $actAccount->Pull ('Address1')->getValue ()));
	$oblstrAddress2				= $oblarrUIValues->Push (new dataString ('Address2'				, $actAccount->Pull ('Address2')->getValue ()));
	$oblstrSuburb				= $oblarrUIValues->Push (new dataString ('Suburb'				, $actAccount->Pull ('Suburb')->getValue ()));
	$oblstrPostcode				= $oblarrUIValues->Push (new dataString ('Postcode'				, $actAccount->Pull ('Postcode')->getValue ()));
	$oblstrState				= $oblarrUIValues->Push (new dataString ('State'				, $actAccount->Pull ('State')->getValue ()));
	$oblbolDisableDDR			= $oblarrUIValues->Push (new dataString ('DisableDDR'			, $actAccount->Pull ('DisableDDR')->getValue ()));
	$oblintDisableLatePayment	= $oblarrUIValues->Push (new dataInteger('DisableLatePayment'	, $actAccount->Pull ('DisableLatePayment')->getValue ()));
	$oblintBillingMethod		= $oblarrUIValues->Push (new dataInteger('BillingMethod'		, $actAccount->Pull ('BillingMethod')->getValue ()));
	$oblintCustomerGroup		= $oblarrUIValues->Push (new dataInteger('CustomerGroup'		, $actAccount->Pull ('CustomerGroup')->getValue ()));
	$oblbolArchived				= $oblarrUIValues->Push (new dataBoolean('Archived'));
	
	// Set UI Values
	if (isset ($_POST ['BusinessName']))		$oblstrBusinessName->setValue		($_POST ['BusinessName']);
	if (isset ($_POST ['TradingName']))			$oblstrTradingName->setValue		($_POST ['TradingName']);
	if (isset ($_POST ['ABN']))					$oblstrABN->setValue				($_POST ['ABN']);
	if (isset ($_POST ['ACN']))					$oblstrACN->setValue				($_POST ['ACN']);
	if (isset ($_POST ['Address1']))			$oblstrAddress1->setValue			($_POST ['Address1']);
	if (isset ($_POST ['Address2']))			$oblstrAddress2->setValue			($_POST ['Address2']);
	if (isset ($_POST ['Suburb']))				$oblstrSuburb->setValue				($_POST ['Suburb']);
	if (isset ($_POST ['Postcode']))			$oblstrPostcode->setValue			($_POST ['Postcode']);
	if (isset ($_POST ['State']))				$oblstrState->setValue				($_POST ['State']);
	if (isset ($_POST ['DisableDDR']))			$oblbolDisableDDR->setValue			($_POST ['DisableDDR']);
	if (isset ($_POST ['DisableLatePayment']))	$oblintDisableLatePayment->setValue	($_POST ['DisableLatePayment']);
	if (isset ($_POST ['BillingMethod']))		$oblintBillingMethod->setValue		($_POST ['BillingMethod']);
	if (isset ($_POST ['Archived']))			$oblbolArchived->setValue			(TRUE);
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if (isset ($_POST ['BusinessName']))
	{
		$abnABN = new ABN ('ABN', '');
		$acnACN = new ACN ('ACN', '');
		
		if (!$_POST ['BusinessName'])
		{
			// Check the Business Name is not Empty
			$oblstrError->setValue ('BusinessName');
		}
		/*else if (!$_POST ['ABN'] &&  !$_POST ['ACN'])
		{
			// Check either an ABN or ACN exists
			$oblstrError->setValue ('ABN-ACN');
		}*/
		else if ($_POST ['ABN'] && !$abnABN->setValue ($_POST ['ABN']))
		{
			// If the ABN is set, make sure it's valid
			$oblstrError->setValue ('ABN Invalid');
		}
		else if ($_POST ['ACN'] && !$acnACN->setValue ($_POST ['ACN']))
		{
			// If the ACN is set, make sure it's valid
			$oblstrError->setValue ('ACN Invalid');
		}
		else if (!$_POST ['Address1'])
		{
			// Check the Address is not Empty
			$oblstrError->setValue ('Address');
		}
		else if (!$_POST ['Suburb'])
		{
			// Check the Address (Suburb) is not Empty
			$oblstrError->setValue ('Suburb');
		}
		else if (!$_POST ['Postcode'])
		{
			// Check the Address (Postcode) is not Empty
			$oblstrError->setValue ('Postcode');
		}
		else if (!PostcodeValid ($_POST ['Postcode']))
		{
			// Check the Address (Postcode) is 4 digits long
			$oblstrError->setValue ('Postcode');
		}
		else if (!$_POST ['State'])
		{
			// Check the Address (State) is not Empty
			$oblstrError->setValue ('State');
		}
		else if (!$cgrCustomerGroups->setValue ($_POST ['CustomerGroup']))
		{
			// Check the Customer Group Exists
			$oblstrError->setValue ('Customer Group');
		}
		else if (!$bmeBillingMethods->setValue ($_POST ['BillingMethod']))
		{
			// Check the Billing Method Exists
			$oblstrError->setValue ('Billing Method');
		}
		else
		{
			$actAccount->Update (
				Array (
					"BusinessName"			=> $_POST ['BusinessName'],
					"TradingName"			=> $_POST ['TradingName'],
					"ABN"					=> $_POST ['ABN'],
					"ACN"					=> $_POST ['ACN'],
					"Address1"				=> $_POST ['Address1'],
					"Address2"				=> $_POST ['Address2'],
					"Suburb"				=> $_POST ['Suburb'],
					"Postcode"				=> $_POST ['Postcode'],
					"State"					=> $_POST ['State'],
					"DisableDDR"			=> $_POST ['DisableDDR'],
					"DisableLatePayment"	=> $_POST ['DisableLatePayment'],
					"CustomerGroup"			=> $_POST ['CustomerGroup'],
					"BillingMethod"			=> $_POST ['BillingMethod']
				)
			);
			
			// We're using ISSET here for a reason:
			// If they want to unarchive the Account, $_POST ['Archived'] will be set to 0
			if (isset ($_POST ['Archived']))
			{
				$actAccount->ArchiveStatus ($_POST ['Archived']);
			}
			
			header ('Location: ../admin/flex.php/Account/Overview/?Account.Id=' . $actAccount->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Billing');
	$docDocumentation->Explain ('CustomerGroup');
	
	$Style->Output (
		'xsl/content/account/edit.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
