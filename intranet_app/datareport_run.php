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
	$arrPage['Permission']	= PERMISSION_OPERATOR | PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_DATA_REPORT;
	
	// call application
	require ('config/application.php');
	
	// Get the Requested Report
	try
	{
		$rptReport = $Style->attachObject (new DataReport (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/datareport/notfound.xsl');
		exit;
	}
	
	if ($_POST ['Confirm'])
	{
		$selResult = $rptReport->Execute ($_POST ['input']);
		
		debug ($selResult->FetchAll ());
		exit;
	}
	
	$Style->attachObject ($rptReport->Inputs ());
	
	// In terms of Documentation, we want to show the 
	// Report documentation, along with any documentation
	// that is associated with the Report we are running
	$docDocumentation->Explain ('Report');
	
	// Explain the Fundamentals for the Report
	$arrDocumentation = $rptReport->Documentation ();
	foreach ($arrDocumentation as $strDocumentation)
	{
		$docDocumentation->Explain ($strDocumentation);
	}
	
	$Style->Output ('xsl/content/datareport/run_input.xsl');
	
?>
