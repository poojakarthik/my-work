<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
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
	
	$uchUnapprovedCharges = $Style->attachObject (new Charges_Unapproved ());
	$uchUnapprovedCharges->Sample ();
	
	$Style->Output ('xsl/content/charges/approve.xsl');
	
?>
