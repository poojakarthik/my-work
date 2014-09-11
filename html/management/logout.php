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
	
	// Delete Session Information
	$athAuthentication->Logout ();
	
	// Forward to Login Page
	header ("Location: login.php"); exit;
	
?>
