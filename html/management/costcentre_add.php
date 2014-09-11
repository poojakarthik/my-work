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
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_COST_CENTRE;
	
	// call application
	require ('config/application.php');
	
	// We must have an Account
	try
	{
		$actAccount = $Style->attachObject (new Account (($_GET ['Account']) ? $_GET ['Account'] : $_POST ['Account']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Start the Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Start Remembering
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrName = $oblarrUIValues->Push (new dataString ('Name', $_POST ['Name']));
	
	// If the Name has been passed through, add the cost centre to the Database.
	if (isset ($_POST ['Name']))
	{
		if (!$_POST ['Name'])
		{
			$oblstrError->setValue ('Name Empty');
		}
		else
		{
			$intCostCentre = $actAccount->AddCostCentre (
				Array (
					"Name"	=> $_POST ['Name']
				)
			);
			
			header ("Location: costcentre_added.php?Id=" . $intCostCentre);
			exit;
		}
	}
	
	// Pull Documentation Information about the Account and Cost Centre
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Cost Centre");
	
	// Output the Contact Add page to the browser
	$Style->Output (
		'xsl/content/account/costcentre/add.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
