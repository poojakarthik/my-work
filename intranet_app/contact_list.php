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
	
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Contact");
	
	// Start a new Contact Search
	$cosContacts = new Contacts ();
	
	if (isset ($_GET ['constraint']))
	{
		foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
		{
			if ($arrConstraintRules ['Value'] != "")
			{
				$cosContacts->Constrain (
					$strConstraintName,
					$arrConstraintRules ['Operator'],
					$arrConstraintRules ['Value']
				);
			}
		}
		
		if (isset ($_GET ['Order']['Column']))
		{
			$cosContacts->Order (
				$_GET ['Order']['Column'],
				isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
			);
		}
		
		$cosContacts->Sample (
			isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
			isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 20
		);
		
		$Style->attachObject ($cosContacts);
		
		$Style->Output ("xsl/content/contact/list_results.xsl");
	}
	else
	{
		$Style->Output ("xsl/content/contact/list_criteria.xsl");
	}
	
?>
