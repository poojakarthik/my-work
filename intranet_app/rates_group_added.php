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
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require ('config/application.php');
	
	$docDocumentation->Explain ("Rate Plan");
	$docDocumentation->Explain ("Service");
	
	$Style->Output ("xsl/content/rates/groups/confirm.xsl");
	
?>
