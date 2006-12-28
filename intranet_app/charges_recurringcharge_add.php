<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	$rctRecurringChargesTypes = new RecurringChargeTypes ();
	
	$docDocumentation->Explain ('Recurring Charge Type');
	
	$oblstrError				= $Style->attachObject (new dataString ('Error', ''));
	$oblarrRecurringChargeType	= $Style->attachObject (new dataArray ('RecurringChargeType'));
	
	$oblstrChargeType		= $oblarrRecurringChargeType->Push (new dataString ('ChargeType', ''));
	$oblstrDescription		= $oblarrRecurringChargeType->Push (new dataString ('Description', ''));
	$oblstrRecursionCharge	= $oblarrRecurringChargeType->Push (new dataString ('RecursionCharge', ''));
	$natNature				= $oblarrRecurringChargeType->Push (new Natures ());
	$oblintRecurringDate	= $oblarrRecurringChargeType->Push (new dataInteger ('RecurringDate', ''));
	$brqRecurringFreq		= $oblarrRecurringChargeType->Push (new BillingFreqTypes ());
	$oblstrMinCharge		= $oblarrRecurringChargeType->Push (new dataString ('MinCharge', ''));
	$oblstrCancellationFee	= $oblarrRecurringChargeType->Push (new dataString ('CancellationFee', ''));
	$oblbolContinuable		= $oblarrRecurringChargeType->Push (new dataBoolean ('Continuable', FALSE));
	$oblbolFixed			= $oblarrRecurringChargeType->Push (new dataBoolean ('Fixed', FALSE));
	
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$oblstrChargeType		->setValue ($_POST ['ChargeType']);
		$oblstrDescription		->setValue ($_POST ['Description']);
		$oblstrRecursionCharge	->setValue ($_POST ['RecursionCharge']);
		$natNature				->setValue ($_POST ['Nature']);
		$oblintRecurringDate	->setValue ($_POST ['RecurringDate']);
		$brqRecurringFreq		->setValue ($_POST ['RecurringFreqType']);
		$oblstrMinCharge		->setValue ($_POST ['MinCharge']);
		$oblstrCancellationFee	->setValue ($_POST ['CancellationFee']);
		$oblbolContinuable		->setValue ($_POST ['Continuable']);
		$oblbolFixed			->setValue ($_POST ['Fixed']);
		
		if (!$brqRecurringFreq->setValue ($_POST ['RecurringFreqType']))
		{
			// error here
			$oblstrError->setValue ('Frequency');
		}
		else if (!$natNature->setValue ($_POST ['Nature']))
		{
			// error here
			$oblstrError->setValue ('Nature');
		}
		else if (empty ($oblstrChargeType))
		{
			// The Charge Type cannot be Empty
			$oblstrError->setValue ('CType-Blank');
		}
		else if (empty ($oblstrDescription))
		{
			// The Description cannot be Empty
			$oblstrError->setValue ('Descr-Blank');
		}
		else if ($rctRecurringChargesTypes->UnarchivedChargeType ($_POST ['ChargeType']) !== false)
		{
			// A unarchived Charge Type must be unique
			$oblstrError->setValue ('CType-Exists');
		}
		else
		{
			try
			{
				// Add it to the Database
				$intRecurringChargeType = $rctRecurringChargesTypes->Add (
					Array (
						"ChargeType"			=> $_POST ['ChargeType'],
						"Description"			=> $_POST ['Description'],
						"RecursionCharge"		=> $_POST ['RecursionCharge'],
						"Nature"				=> $_POST ['Nature'],
						"RecurringDate"			=> $_POST ['RecurringDate'],
						"MinCharge"				=> $_POST ['MinCharge'],
						"CancellationFee"		=> $_POST ['CancellationFee'],
						"Continuable"			=> $_POST ['Continuable'],
						"Fixed"					=> $_POST ['Fixed'],
						"RecurringFreqType"		=> $_POST ['RecurringFreqType']
					)
				);
				
				header ("Location: charges_recurringcharge_added.php"); exit;
			}
			catch (Exception $e)
			{
				$oblstrError->setValue ($e->getMessage ());
			}
		}
	}
	
	$Style->Output ('xsl/content/charges/recurringcharges/add.xsl');
	
?>
