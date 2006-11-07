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
	
	$srvServices = $actAccount->getService ($_GET ['Service']);
	$Style->attachObject ($srvServices);
	
	$srvServices->getCalls (isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1);
	(!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1) ? $srvServices->getCharges () : null;
	
	$Style->Output ("xsl/content/unbilled/service.xsl");
	
?>
