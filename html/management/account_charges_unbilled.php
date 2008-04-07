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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_CHARGE | MODULE_CHARGE_TYPE | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	// Try to get the Account
	try
	{
		$actAccount = $Style->attachObject (new Account ($_GET ['Account']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Start a new Unbilled Charges Search
	$ubcUnbilledCharges = $Style->attachObject (new Charges_Unbilled);
	$ubcUnbilledCharges->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
	$ubcUnbilledCharges->Order ("CreatedOn", TRUE);
	$oblsamCharges = $ubcUnbilledCharges->Sample (
		($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 10
	);
	
	$oblarrEmployees = $Style->attachObject (new dataArray ('Employees', 'Employee'));
	$arrEmployees = Array ();
	
	foreach ($oblsamCharges as $crgCharge)
	{
		if (!isset ($arrEmployees [$crgCharge->Pull ('CreatedBy')->getValue ()]))
		{
			try
			{
				$arrEmployees [$crgCharge->Pull ('CreatedBy')->getValue ()] = $oblarrEmployees->Push (
					new Employee ($crgCharge->Pull ('CreatedBy')->getValue ())
				);
			}
			catch (Exception $e)
			{
			}
		}
	}
	
	// Get the Charge Types which can be put against this Account
	$octChargeTypes	= $Style->attachObject (new ChargeTypes);
	$octChargeTypes->Constrain ('Archived', '=', FALSE);
	$octChargeTypes->Sample ();
	
	// Explain the Fundamentals
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output (
		"xsl/content/account/charges_unbilled.xsl",
		Array (
			"Account"		=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
