<?php

	require ("config/application_loader.php");
	
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$Style->Output ("xsl/content/console.xsl");
	
?>
