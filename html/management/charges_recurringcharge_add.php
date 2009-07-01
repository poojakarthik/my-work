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
	$arrPage['Permission']	= PERMISSION_CREDIT_MANAGEMENT;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	$rctRecurringChargesTypes = new RecurringChargeTypes ();
	
	$docDocumentation->Explain ('Recurring Charge Type');
	
	$oblstrError				= $Style->attachObject (new dataString ('Error', ''));
	$oblarrRecurringChargeType	= $Style->attachObject (new dataArray ('RecurringChargeType'));
	
	$oblstrChargeType		= $oblarrRecurringChargeType->Push (new dataString	('ChargeType', ''));
	$oblstrDescription		= $oblarrRecurringChargeType->Push (new dataString	('Description', ''));
	$oblfltRecursionCharge	= $oblarrRecurringChargeType->Push (new dataFloat	('RecursionCharge', ''));
	$natNature				= $oblarrRecurringChargeType->Push (new Natures);
	$oblintRecurringDate	= $oblarrRecurringChargeType->Push (new dataInteger	('RecurringDate', ''));
	$brqRecurringFreq		= $oblarrRecurringChargeType->Push (new BillingFreqTypes);
	$oblfltMinCharge		= $oblarrRecurringChargeType->Push (new dataFloat	('MinCharge', ''));
	$oblfltCancellationFee	= $oblarrRecurringChargeType->Push (new dataFloat	('CancellationFee', ''));
	$oblbolContinuable		= $oblarrRecurringChargeType->Push (new dataBoolean	('Continuable', FALSE));
	$oblbolFixed			= $oblarrRecurringChargeType->Push (new dataBoolean	('Fixed', FALSE));
	$oblbolPlanCharge		= $oblarrRecurringChargeType->Push (new dataBoolean	('PlanCharge', FALSE));
	$oblbolUniqueCharge		= $oblarrRecurringChargeType->Push (new dataBoolean	('UniqueCharge', FALSE));
	$oblbolApprovalRequired	= $oblarrRecurringChargeType->Push(new dataBoolean('approval_required', FALSE));
	
	//Debug($_POST);die;
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$oblstrChargeType		->setValue ($_POST ['ChargeType']);
		$oblstrDescription		->setValue ($_POST ['Description']);
		$natNature				->setValue ($_POST ['Nature']);
		$oblintRecurringDate	->setValue ($_POST ['RecurringFreq']);
		$brqRecurringFreq		->setValue ($_POST ['RecurringFreqType']);
		$oblbolContinuable		->setValue ($_POST ['Continuable']);
		$oblbolFixed			->setValue ($_POST ['Fixed']);
		$oblbolPlanCharge		->setValue ($_POST ['PlanCharge']);
		$oblbolUniqueCharge		->setValue ($_POST ['UniqueCharge']);
		$oblbolApprovalRequired->setValue($_POST['approval_required']);
		
		$bolRecursionCharge	= $oblfltRecursionCharge	->setValue ($_POST ['RecursionCharge']);
		$bolMinCharge		= $oblfltMinCharge			->setValue ($_POST ['MinCharge']);
		$bolCancellationFee	= $oblfltCancellationFee	->setValue ($_POST ['CancellationFee']);
		
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
		else if (!$_POST ['ChargeType'])
		{
			// The Charge Type cannot be Empty
			$oblstrError->setValue ('CType-Blank');
		}
		else if (!$_POST ['Description'])
		{
			// The Description cannot be Empty
			$oblstrError->setValue ('Descr-Blank');
		}
		else if (!$bolRecursionCharge)
		{
			// The Recursion Charge must be an Amount
			$oblstrError->setValue ('RecursionCharge Invalid');
		}
		else if ($_POST ['RecurringFreq'] == 0)
		{
			// The Recursion Charge must be an Amount
			$oblstrError->setValue ('Zero Freq');
		}
		else if (!$bolMinCharge && $oblfltMinCharge->getValue () <> 0)
		{
			// The Minimum Charge must be an Amount
			$oblstrError->setValue ('MinCharge Invalid');
		}
		else if (!$bolCancellationFee && $oblfltCancellationFee->getValue () <> 0)
		{
			// The Cancellation Fee must be an Amount
			$oblstrError->setValue ('CancellationFee Invalid');
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
						"Nature"				=> $_POST ['Nature'],
						"RecurringFreq"			=> $_POST ['RecurringFreq'],
						"RecurringFreqType"		=> $_POST ['RecurringFreqType'],
						"MinCharge"				=> $_POST ['MinCharge'],
						"RecursionCharge"		=> $_POST ['RecursionCharge'],
						"CancellationFee"		=> $_POST ['CancellationFee'],
						"Continuable"			=> $_POST ['Continuable'],
						"Fixed"					=> $_POST ['Fixed'],
						"PlanCharge"			=> $_POST ['PlanCharge'],
						"UniqueCharge"			=> $_POST ['UniqueCharge'],
						"approval_required"		=> intval($_POST ['approval_required'])
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
