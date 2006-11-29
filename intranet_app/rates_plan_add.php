<?php
	
	require ("config/application_loader.php");
	
	$docDocumentation->Explain ("Rate Plan");
	$docDocumentation->Explain ("Service");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	$Style->attachObject (new NamedServiceType ("NamedServiceType"));
	
	if (isset ($_POST ['Name']) && isset ($_POST ['ServiceType']))
	{
		if (!empty ($_POST ['Name']) && !empty ($_POST ['ServiceType']))
		{
		
		}
		
		// If we're reaching here, there is a problem with either our service type or with the Name we want to use for the plan
	}
	
	$Style->Output ("xsl/content/rates/plans/add.xsl");
	
?>
