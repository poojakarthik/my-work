<?php
	
	require ("config/application_loader.php");
	
	$docDocumentation->Explain ("Rate");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	$rrlRates = new Rates ();
	
	if (isset ($_GET ['constraint']))
	{
		foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
		{
			if ($arrConstraintRules ['Value'] != "")
			{
				$rrlRates->Constrain (
					$strConstraintName,
					$arrConstraintRules ['Operator'],
					$arrConstraintRules ['Value']
				);
			}
		}
	}
	
	if (isset ($_GET ['Order']['Column']))
	{
		$rrlRates->Order (
			$_GET ['Order']['Column'],
			isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
		);
	}
	
	$rrlRates->Sample ();
	
	$Style->attachObject ($rrlRates);
	
	$Style->Output ("xsl/content/rates/rates/list.xsl");
	
?>
