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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE;
	
	// call application
	require ('config/application.php');
	
	
	// Explain the Fundamentals
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Archive');
	
	if (isset ($_GET ['constraint']))
	{
		// Start a new Account Search
		$svsServices = $Style->attachObject (new Services);
		
		if (isset ($_GET ['constraint']))
		{
			foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
			{
				if ($arrConstraintRules ['Value'] != "")
				{
					$svsServices->Constrain (
						$strConstraintName,
						$arrConstraintRules ['Operator'],
						$arrConstraintRules ['Value']
					);
				}
			}
		}
		
		if (isset ($_GET ['Order']['Column']))
		{
			$svsServices->Order (
				$_GET ['Order']['Column'],
				isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
			);
		}
		
		$svsServices->Sample (
			($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
			($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 20
		);
	}
	
	// List of Service Types
	$styServiceTypes = $Style->attachObject (new ServiceTypes);
	
	$Style->Output ("xsl/content/service/list.xsl");
	
?>
