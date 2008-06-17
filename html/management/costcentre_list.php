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
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW;
	$arrPage['Modules']		= MODULE_BASE | MODULE_COST_CENTRE;
	
	// call application
	require ('config/application.php');
	
	// Try to get the Account we are searching for Cost Centres
	try
	{
		$actAccount = $Style->attachObject (new Account ($_GET ['Account']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Pull Cost Centres for the Account and order by Name
	$csrCostCentres = $Style->attachObject (new CostCentres);
	$csrCostCentres->Constrain ('AccountGroup',		'=',	$actAccount->Pull ('AccountGroup')->getValue ());
	$csrCostCentres->Constrain ('Account',			'=',	$actAccount->Pull ('Id')->getValue ());
	$csrCostCentres->Order ("Name", TRUE);
	
	$csrCostCentres->Sample (
		($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 15
	);
	
	// Documentation
	$docDocumentation->Explain ('Account');
	
	// Output
	$Style->Output (
		'xsl/content/account/costcentre/list.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
