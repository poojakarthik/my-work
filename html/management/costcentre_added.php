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
	
	// Try to get the Cost Centre we are dealing with
	try
	{
		$ccrCostCentre = $Style->attachObject (new CostCentre (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ("xsl/content/account/costcentre/notfound.xsl");
		exit;
	}
	
	$Style->Output (
		"xsl/content/account/costcentre/added.xsl",
		Array (
			"Account"	=> $ccrCostCentre->Pull ('Account')->getValue ()
		)
	);
	
?>
