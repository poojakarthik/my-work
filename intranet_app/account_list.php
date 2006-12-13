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
	
	
	// Explain the Fundamentals
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ('Archive');
	
	// Start a new Account Search
	$acsAccounts = new Accounts ();
	
	if (isset ($_GET ['constraint']))
	{
		foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
		{
			if ($arrConstraintRules ['Value'] != "")
			{
				$acsAccounts->Constrain (
					$strConstraintName,
					$arrConstraintRules ['Operator'],
					$arrConstraintRules ['Value']
				);
			}
		}
	}
	
	if (isset ($_GET ['Order']['Column']))
	{
		$acsAccounts->Order (
			$_GET ['Order']['Column'],
			isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
		);
	}
	
	$acsAccounts->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 20
	);
	
	$Style->attachObject ($acsAccounts);
	
	$Style->Output ("xsl/content/account/list.xsl");
	
?>
