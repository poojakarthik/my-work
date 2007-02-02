<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$actAccount		= $Style->attachObject ($athAuthentication->getAuthenticatedUser ()->getAccount ($_GET ['Account']));
	$srvServices	= $Style->attachObject ($actAccount->getService ($_GET ['Service']));
	
	$srvServices->getCalls (isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1);
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		$srvServices->getCharges ();
	}
	
	$RecordTypes		= $Style->attachObject (new RecordTypes ());
	$RecordDisplayTypes	= $Style->attachObject (new RecordDisplayTypes ());
	
	$Style->Output ("xsl/content/unbilled/service.xsl");
	
?>
