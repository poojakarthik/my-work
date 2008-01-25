<?php
	
	require ("config/application_loader.php");
	
	// Check the person is authenticated. We do not want people
	// to fake their Id cookie because it could force legitimate
	// people to logout
	if ($athAuthentication->getAuthentication ()) {
		// Delete the cookies, expire the session
		$athAuthentication->Logout ();
	}
	
	// Redirect back to the login page
	header ("Location: login.php"); exit;
	
?>
