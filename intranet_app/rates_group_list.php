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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_GROUP | MODULE_SERVICE_TYPE | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	$docDocumentation->Explain ("Rate Group");
	
	$rglRateGroups = $Style->attachObject (new RateGroups);
	
	if (isset ($_GET ['constraint']))
	{
		foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
		{
			if ($arrConstraintRules ['Value'] != "")
			{
				$rglRateGroups->Constrain (
					$strConstraintName,
					$arrConstraintRules ['Operator'],
					$arrConstraintRules ['Value']
				);
			}
		}
	}
	
	if (isset ($_GET ['Order']['Column']))
	{
		$rglRateGroups->Order (
			$_GET ['Order']['Column'],
			isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
		);
	}
	
	$rglRateGroups->Sample ();
	
	$Style->Output ("xsl/content/rates/groups/list.xsl");
	
?>
