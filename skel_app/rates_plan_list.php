<?php
	
	require ("config/application_loader.php");
	
	$docDocumentation->Explain ("Rate Plan");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	// Start a new Account Search
	$rplRatePlans = new RatePlans ();
	
	if (isset ($_GET ['constraint']))
	{
		foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
		{
			if ($arrConstraintRules ['Value'] != "")
			{
				$rplRatePlans->Constrain (
					$strConstraintName,
					$arrConstraintRules ['Operator'],
					$arrConstraintRules ['Value']
				);
			}
		}
	}
	
	if (isset ($_GET ['Order']['Column']))
	{
		$rplRatePlans->Order (
			$_GET ['Order']['Column'],
			isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
		);
	}
	
	$rplRatePlans->Sample ();
	
	$Style->attachObject ($rplRatePlans);
	
	$Style->Output ("xsl/content/rates/plans/list.xsl");
	
?>
