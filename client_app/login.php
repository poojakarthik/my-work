<?php

	require ("application_loader.php");
	
	if ($athAuthentication->getAuthentication ())
	{
		header ("Location: console.php");
		exit;
	}
	
	if (
	isset ($_POST ['UserName']) &&
	isset ($_POST ['PassWord']) &&
	!empty ($_POST ['UserName']) &&
	!empty ($_POST ['PassWord'])
	)
	{
		if ($athAuthentication->contactLogin ($_POST ['UserName'], $_POST ['PassWord']))
		{
			header ("Location: console.php");
			exit;
		}
	}
	
	$Style->Output ("xsl/content/login.xsl");
	
?>
