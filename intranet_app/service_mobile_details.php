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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_MOBILE_DETAIL | MODULE_SERVICE_ADDRESS;
	
	// call application
	require ('config/application.php');
	
	
	// Check the Service Exists
	try
	{
		$srvService = $Style->attachObject (new Service (($_POST ['Service']) ? $_POST ['Service'] : $_GET ['Service']));
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
	
	$arrUIValues 		= $Style->attachObject (new dataArray ('ui-values'));
	$oblstrSimPUK		= $arrUIValues->Push (new dataString ('SimPUK'));
	$oblstrSimESN		= $arrUIValues->Push (new dataString ('SimESN'));
	$oblstrSimState		= $arrUIValues->Push (new dataString ('SimState'));
	$oblstrDOBday		= $arrUIValues->Push (new dataString ('DOB-day'));
	$oblstrDOBmonth		= $arrUIValues->Push (new dataString ('DOB-month'));
	$oblstrDOByear		= $arrUIValues->Push (new dataString ('DOB-year'));
	$oblstrComments		= $arrUIValues->Push (new dataString ('Comments'));
	
	// Try getting the Original Service Address Information
	try
	{
		$mdeMobileDetail = $Style->attachObject ($srvService->MobileDetail ());
		
		$oblstrSimPUK->setValue		(isset ($_POST ['SimPUK'])		? $_POST ['SimPUK']			: $mdeMobileDetail->Pull ('SimPUK')->getValue ());
		$oblstrSimESN->setValue		(isset ($_POST ['SimESN'])		? $_POST ['SimESN']			: $mdeMobileDetail->Pull ('SimESN')->getValue ());
		$oblstrSimState->setValue	(isset ($_POST ['SimState'])	? $_POST ['SimState']		: $mdeMobileDetail->Pull ('SimState')->getValue ());
		$oblstrDOBday->setValue		(isset ($_POST ['DOB']['day'])	? $_POST ['DOB']['day']		: $mdeMobileDetail->Pull ('DOB')->Pull ('day')->getValue ());
		$oblstrDOBmonth->setValue	(isset ($_POST ['DOB']['month'])? $_POST ['DOB']['month']	: $mdeMobileDetail->Pull ('DOB')->Pull ('month')->getValue ());
		$oblstrDOByear->setValue	(isset ($_POST ['DOB']['year'])	? $_POST ['DOB']['year']	: $mdeMobileDetail->Pull ('DOB')->Pull ('year')->getValue ());
		$oblstrComments->setValue	(isset ($_POST ['Comments'])	? $_POST ['Comments']		: $mdeMobileDetail->Pull ('Comments')->getValue ());
	}
	catch (Exception $e)
	{
		// It's ok - Mobile Detail Information can be Blank
	}
	
	if ($_POST ['Service'])
	{
		// Save Information
		
		$srvService->MobileDetailUpdate (
			Array (
				'SimPUK'	=> $_POST ['SimPUK'],
				'SimESN'	=> $_POST ['SimESN'],
				'SimState'	=> $_POST ['SimState'],
				'DOB'		=> Array (
					'year'		=> $_POST ['DOB']['year'],
					'month'		=> $_POST ['DOB']['month'],
					'day'		=> $_POST ['DOB']['day']
				),
				'Comments'	=> $_POST ['Comments']
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
	
	// States List
	$sstStates = $Style->attachObject (new ServiceStateTypes);
	
	// Documentation
	$docDocumentation->Explain ('Service Mobile');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	
	// Output the Request Page
	$Style->Output ('xsl/content/service/mobiledetail/update.xsl');
	
?>
