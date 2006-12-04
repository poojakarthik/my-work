<?php
	
	require ("config/application_loader.php");
	
	// Delete Session Information
	$athAuthentication->Logout ();
	
	// Forward to Login Page
	header ("Location: login.php"); exit;
	
?>
