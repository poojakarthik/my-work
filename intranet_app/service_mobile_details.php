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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_MOBILE_DETAIL;
	
	// call application
	require ('config/application.php');
	
	
	// Check the Service Exists
	try
	{
		$srvService = $Style->attachObject (new Service (isset ($_POST ['Service']) ? $_POST ['Service'] : $_GET ['Service']));
		$actAccount = $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// Make sure the Service is a Mobile
	if ($srvService->Pull ('ServiceType')->getValue () <> SERVICE_TYPE_MOBILE)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// Try getting the Original Service Address Information
	try
	{
		$mdeMobileDetail = $Style->attachObject ($srvService->MobileDetail ());
	}
	catch (Exception $e)
	{
		// It's ok - Mobile Detail Information can be Blank
	}
	
	if ($_POST ['Service'])
	{
		// Save Information
		
		$srvService->ServiceAddressUpdate (
			Array (
				
			)
		);
		
		$Style->Output ('xsl/content/service/mobiledetail/updated.xsl');
		exit;
	}
	
	// Get information about Note Types
	$ntsNoteTypes = $Style->attachObject (new NoteTypes);
	
	// Get Associated Notes
	$nosNotes = $Style->attachObject (new Notes);
	$nosNotes->Constrain ('Service', '=', $_GET ['Service']);
	$nosNotes->Sample (1, 5);
	
	$docDocumentation->Explain ('Service Mobile');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	
	// Output the Request Page
	$Style->Output ('xsl/content/service/mobiledetail/update.xsl');
	
?>
