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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_CONTACT;
	
	// call application
	require ('config/application.php');
	
	
	// Explain the Fundamentals
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('Archive');
	
	if (isset ($_GET ['constraint']))
	{
		// Start a new Account Search
		$ctsContacts = $Style->attachObject (new Contacts);
		
		if (isset ($_GET ['constraint']))
		{
			foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
			{
				if ($arrConstraintRules ['Value'] != "")
				{
					$ctsContacts->Constrain (
						$strConstraintName,
						$arrConstraintRules ['Operator'],
						$arrConstraintRules ['Value']
					);
				}
			}
		}
		
		if (isset ($_GET ['Order']['Column']))
		{
			$ctsContacts->Order (
				$_GET ['Order']['Column'],
				isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
			);
		}
		
		$ctsContacts->Sample (
			($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
			($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 20
		);
	}
	
	$Style->Output ("xsl/content/contact/list.xsl");
	
?>
