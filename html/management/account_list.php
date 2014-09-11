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
	$arrPage['Permission']	= Array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS);
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT;
	
	// call application
	require ('config/application.php');
	
	
	// Explain the Fundamentals
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ('Archive');
	
	if (isset ($_GET ['constraint']))
	{
		// Start a new Account Search
		$acsAccounts = $Style->attachObject (new Accounts);
		
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
			($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
			($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 20
		);
	}
	$Style->Output ("xsl/content/account/list.xsl");
	
?>
