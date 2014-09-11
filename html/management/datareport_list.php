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
	$arrPage['Permission']	= Array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS);
	$arrPage['Modules']		= MODULE_BASE | MODULE_DATA_REPORT;
	
	// call application
	require ('config/application.php');
	
	// Get all reports
	$rpsReports = $Style->attachObject (new DataReports);
	$rpsReports->Constrain('Priviledges', '&', $athAuthentication->AuthenticatedEmployee()->Pull('Privileges')->getValue());
	$rpsReports->Order('Name', TRUE);
	$rpsReports->Sample ();
	
	// Explain the Fundamentals
	$docDocumentation->Explain ('Report');
	
	$Style->Output ('xsl/content/datareport/list.xsl');
	
?>
