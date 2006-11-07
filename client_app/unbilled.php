<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$actAccount = $athAuthentication->getAuthenticatedUser ()->getAccount ($_GET ['Account']);
	$Style->attachObject ($actAccount);
	
	$ubcServices = $actAccount->getServices ();
	$Style->attachObject ($ubcServices);
	
	$Style->Output ("xsl/content/unbilled/services.xsl");
	
?>
