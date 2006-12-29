<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	$docDocumentation->Explain ("Rate");
	$docDocumentation->Explain ("Service");
	$docDocumentation->Explain ("Archive");
	
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
	
	$Style->attachObject (new ServiceTypes);
	
	if (isset ($_GET ['constraint']['ServiceType']))
	{
		$rrlRates->Sample (
			$_GET ['rangePage']		&& is_numeric ($_GET ['rangePage'])	? $_GET ['rangePage']	: 1		, 
			$_GET ['rangeLength']	&& is_numeric ($_GET ['rangeLength'])	? $_GET ['rangeLength']	: 30
		);
		
		$Style->attachObject ($rrlRates);
	}
	
	$Style->Output ("xsl/content/rates/rates/list.xsl");
	
?>
