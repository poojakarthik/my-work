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
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_SERVICE | MODULE_EMPLOYEE | MODULE_SERVICE_TYPE;
	
	// call application
	require ('config/application.php');
	
	// If we're Posting through Charges, we want to delegate
	if (isset ($_POST ['charge']))
	{
		// Go through each of the Charges
		foreach ($_POST ['charge'] as $intCharge => $intResponse)
		{
			// Check we haven't got a Donkey
			if (intval ($intResponse) != DONKEY)
			{
				// Get the Charge
				$crgCharge = new Charge ($intCharge);
				
				// Delegate the Charge
				switch ($intResponse)
				{
					case 1:
						$crgCharge->Approve ($athAuthentication->AuthenticatedEmployee ());
						break;
						
					case 0:
						$crgCharge->Decline ($athAuthentication->AuthenticatedEmployee ());
						break;
				}
			}
		}
		
		// Reload the page
		header ("Location: charges_approve.php"); exit;
	}
	
	$intPageLimit	= 20;
	$intPage		= ((int)$_GET['p'] > 0) ? (int)$_GET['p'] : 1;
	
	$uchUnapprovedCharges = $Style->attachObject (new Charges_Unapproved);
	$oblsamCharges = $uchUnapprovedCharges->Sample ((($intPage - 1) * $intPageLimit) + 1, $intPageLimit);
	
	$arrEmployees = Array ();
	$oblarrEmployees = $Style->attachObject (new dataArray ('Employees', 'Employee'));
	
	foreach ($oblsamCharges as $chgUnapprovedCharge)
	{
		if (!isset ($arrEmployees [$chgUnapprovedCharge->Pull ('CreatedBy')->getValue ()]))
		{
			$arrEmployees [$chgUnapprovedCharge->Pull ('CreatedBy')->getValue ()] = new Employee (
				$chgUnapprovedCharge->Pull ('CreatedBy')->getValue ()
			);
			
			$oblarrEmployees->Push (
				$arrEmployees [$chgUnapprovedCharge->Pull ('CreatedBy')->getValue ()]
			);
		}
	}
	
	$Style->Output ('xsl/content/charges/approve.xsl');
	
?>
