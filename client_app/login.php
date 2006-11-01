<?php

	require ("application_loader.php");
	
/*
	if ($objAuthentication->getAuthentication ())
	{
		header ("Location: console.php");
		exit;
	}
*/
	

$Style->attachObject (new dataString ("test", "$400.00"));
$Style->attachObject (new dataString ("test", "$500.00"));
$Style->attachObject (new dataString ("test", "$600.00"));
$Style->attachObject (new dataString ("test", "$700.00"));

	$Style->Output ("xsl/content/login.xsl");
	
?>
