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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_MOBILE_DETAIL | MODULE_SERVICE_ADDRESS | MODULE_STATE;
	
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
	
	// Try getting the Original Service Address Information
	try
	{
		$mdeMobileDetail = $Style->attachObject ($srvService->MobileDetail ());
	}
	catch (Exception $e)
	{
		// It's ok - Mobile Detail Information can be Blank
	}
	
	$arrUIValues 		= $Style->attachObject (new dataArray ('ui-values'));
	$oblstrSimPUK		= $arrUIValues->Push (new dataString ('SimPUK',		($mdeMobileDetail) ? $mdeMobileDetail->Pull ('SimPUK')->getValue ()		: ""));
	$oblstrSimESN		= $arrUIValues->Push (new dataString ('SimESN',		($mdeMobileDetail) ? $mdeMobileDetail->Pull ('SimESN')->getValue ()		: ""));
	$oblstrSimState		= $arrUIValues->Push (new dataString ('SimState',	($mdeMobileDetail) ? $mdeMobileDetail->Pull ('SimState')->getValue ()	: ""));
	$oblstrComments		= $arrUIValues->Push (new dataString ('Comments',	($mdeMobileDetail) ? $mdeMobileDetail->Pull ('Comments')->getValue ()	: ""));
	
	$oblstrDOBday		= $arrUIValues->Push (new dataString ('DOB-day',
								$mdeMobileDetail && $mdeMobileDetail->Pull ('DOB')->Pull ('day') 
								? $mdeMobileDetail->Pull ('DOB')->Pull ('day')->getValue ()
								: 0));
								
	$oblstrDOBmonth		= $arrUIValues->Push (new dataString ('DOB-month',
								$mdeMobileDetail && $mdeMobileDetail->Pull ('DOB')->Pull ('month')
								? $mdeMobileDetail->Pull ('DOB')->Pull ('month')->getValue ()
								: 0));
								
	$oblstrDOByear		= $arrUIValues->Push (new dataString ('DOB-year',
								$mdeMobileDetail && $mdeMobileDetail->Pull ('DOB')->Pull ('year')
								? $mdeMobileDetail->Pull ('DOB')->Pull ('year')->getValue ()
								: 0));
	
	if (isset ($_POST ['SimPUK']))			$oblstrSimPUK->setValue		($_POST ['SimPUK']);
	if (isset ($_POST ['SimESN']))			$oblstrSimESN->setValue		($_POST ['SimESN']);
	if (isset ($_POST ['SimState']))		$oblstrSimState->setValue	($_POST ['SimState']);
	if (isset ($_POST ['DOB']['day']))		$oblstrDOBday->setValue		($_POST ['DOB']['day']);
	if (isset ($_POST ['DOB']['month']))	$oblstrDOBmonth->setValue	($_POST ['DOB']['month']);
	if (isset ($_POST ['DOB']['year']))		$oblstrDOByear->setValue	($_POST ['DOB']['year']);
	if (isset ($_POST ['Comments']))		$oblstrComments->setValue	($_POST ['Comments']);
	
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
		
		$Style->Output (
			'xsl/content/service/mobiledetail/updated.xsl',
			Array (
				'Account'		=> $actAccount->Pull ('Id')->getValue (),
				'Service'		=> $srvService->Pull ('Id')->getValue ()
			)
		);
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
	$Style->Output (
		'xsl/content/service/mobiledetail/update.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>
