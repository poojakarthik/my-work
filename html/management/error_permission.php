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
	$arrPage['Permission']	= PERMISSION_PUBLIC;
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require ('config/application.php');
	
	$strMessage  = "This page is currently unavailable<br>";
	$strMessage .= "Please contact your system administrator for assistance.";
	
	$Style->attachObject (new DataString("Error", $strMessage));
	
	$Style->Output ("xsl/content/error/error.xsl");
	
?>
