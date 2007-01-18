<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_BUG;
	
	// call application
	require ('config/application.php');
	
	if (!$_POST ['Comment'])
	{
		$Style->Output ('xsl/content/bug/report_empty.xsl');
	}
	
	Bugs::Report (
		$athAuthentication->AuthenticatedEmployee (),
		$_POST ['PageDetails'],
		$_POST ['Comment']
	);
	
	header ("Location: bug_reported.php");
	exit;
	
?>
