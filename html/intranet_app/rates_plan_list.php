<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	header("Location: flex.php/Plan/AvailablePlans/");

	/* Old Page
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_PLAN;
	
	// call application
	require ('config/application.php');
	
	$docDocumentation->Explain ('Rate Plan');
	$docDocumentation->Explain ('Service');
	
	// Attach a Service Types Listing for Searching
	$svtServiceTypes = $Style->attachObject (new ServiceTypes);
	
	// Start a new Rate Plans Search
	$rplRatePlans = $Style->attachObject (new RatePlans);
	
	if (isset ($_GET ['constraint']))
	{
		foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
		{
			if ($arrConstraintRules ['Value'] != '')
			{
				$rplRatePlans->Constrain (
					$strConstraintName,
					$arrConstraintRules ['Operator'],
					$arrConstraintRules ['Value']
				);
			}
		}
	}
	
	$rplRatePlans->Constrain ('Archived', '=', 0);
	
	if (isset ($_GET ['Order']['Column']))
	{
		$rplRatePlans->Order (
			$_GET ['Order']['Column'],
			isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
		);
	}
	else
	{
		$rplRatePlans->Order (
			'ServiceType',
			TRUE
		);
	}
	
	$rplRatePlans->Sample ();
	
	$Style->Output ('xsl/content/rates/plans/list.xsl');
	*/
?>
