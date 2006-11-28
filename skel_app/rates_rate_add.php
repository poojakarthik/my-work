<?php
	
	require ("config/application_loader.php");
	
	$docDocumentation->Explain ("Rate");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	$Style->Output ("xsl/content/rates/rates/add.xsl");
	
?>
