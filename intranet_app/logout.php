<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	// Delete Session Information
	$athAuthentication->Logout ();
	
	// Forward to Login Page
	header ("Location: login.php"); exit;
	
?>
