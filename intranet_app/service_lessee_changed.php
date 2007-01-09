<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	$oblarrServices = $Style->attachObject (new dataArray ('Services'));
	
	$oblarrServiceOld = $oblarrServices->Push (new dataArray ('Old'));
	$oblarrServiceNew = $oblarrServices->Push (new dataArray ('New'));
	
	try
	{
		$oblarrServiceOld->Push (new Service ($_GET ['Old']));
		$oblarrServiceNew->Push (new Service ($_GET ['New']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/windowclose.xsl');
		exit;
	}
	
	$Style->Output ('xsl/content/service/lessee_changed.xsl');
	
?>
