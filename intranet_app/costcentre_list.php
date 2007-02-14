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
	$arrPage['Modules']		= MODULE_BASE | MODULE_COST_CENTRE;
	
	// call application
	require ('config/application.php');
	
	$csrCostCentres = $Style->attachObject (new CostCentres);
	$csrCostCentres->Sample (
		($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 20
	);
	
	$Style->Output ("xsl/content/costcentre/list.xsl");
	
?>
