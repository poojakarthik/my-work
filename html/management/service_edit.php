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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_COST_CENTRE | MODULE_EMPLOYEE | MODULE_MOBILE_DETAIL | MODULE_SERVICE_ADDRESS | MODULE_STATE | MODULE_INBOUND | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$srvService = $Style->attachObject (new Service (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
		$actAccount = $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		// If the service does not exist, an exception will be thrown
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
	
	//Try getting Inbound details
	try
	{
		//TODO!nathan! Make sure that the system pulls the data to display
		//for inbound numbers.		
		
		$inbInboundDetail = $Style->attachObject ($srvService->InboundDetail ());
		/*if ($srvService->Pull ('ServiceType')->getValue () == SERVICE_TYPE_INBOUND)
		{
			$inbInboundDetail = $Style->attachObject (new InboundDetail ($_GET ['Id']));
			
		}*/
	}
	catch (Exception $e)
	{
	
    }




	// Error String
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	

	$arrUIValues 		= $Style->attachObject (new dataArray ('ui-values'));
		
	    
		$oblstrSimPUK		= $arrUIValues->Push (new dataString ('SimPUK',		($mdeMobileDetail) ? $mdeMobileDetail->Pull ('SimPUK')->getValue ()		: ""));
		$oblstrSimESN		= $arrUIValues->Push (new dataString ('SimESN',		($mdeMobileDetail) ? $mdeMobileDetail->Pull ('SimESN')->getValue ()		: ""));
		$oblstrSimState		= $arrUIValues->Push (new dataString ('SimState',	($mdeMobileDetail) ? $mdeMobileDetail->Pull ('SimState')->getValue ()	: ""));
		$oblstrComments		= $arrUIValues->Push (new dataString ('Comments',	($mdeMobileDetail) ? $mdeMobileDetail->Pull ('Comments')->getValue ()	: ""));
		
		if ($srvService->Pull ('ServiceType')->getValue () == SERVICE_TYPE_INBOUND)
		{
			$oblstrAnswerPoint	= $arrUIValues->Push (new dataString ('AnswerPoint',($inbInboundDetail) ? $inbInboundDetail->Pull ('AnswerPoint')->getValue ()	: ""));
			$oblstrConfig		= $arrUIValues->Push (new dataString ('Configuration',		($inbInboundDetail) ? $inbInboundDetail->Pull ('Configuration')->getValue ()	: ""));
			
		}
	
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
	
	if (isset ($_POST ['AnswerPoint']))		$oblstrAnswerPoint->setValue		($_POST ['AnswerPoint']);
	if (isset ($_POST ['Config']))			$oblstrConfig->setValue		($_POST ['Config']);
	if (isset ($_POST ['SimPUK']))			$oblstrSimPUK->setValue		($_POST ['SimPUK']);
	if (isset ($_POST ['SimESN']))			$oblstrSimESN->setValue		($_POST ['SimESN']);
	if (isset ($_POST ['SimState']))		$oblstrSimState->setValue	($_POST ['SimState']);
	if (isset ($_POST ['DOB']['day']))		$oblstrDOBday->setValue		($_POST ['DOB']['day']);
	if (isset ($_POST ['DOB']['month']))	$oblstrDOBmonth->setValue	($_POST ['DOB']['month']);
	if (isset ($_POST ['DOB']['year']))		$oblstrDOByear->setValue	($_POST ['DOB']['year']);
	if (isset ($_POST ['Comments']))		$oblstrComments->setValue	($_POST ['Comments']);



	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_POST ['FNN'])
	{
		$strFNN = preg_replace ('/\s/', '', $_POST ['FNN']['1']);
		
		$bolDifferent = ($strFNN <> $srvService->Pull ('FNN')->getValue ());
		
		if ($bolDifferent && $_POST ['FNN']['1'] <> $_POST ['FNN']['2'])
		{
			// Check the Line Numbers Match
			$oblstrError->setValue ('Mismatch');
		}
		else if ($bolDifferent && $strFNN <> "" && !IsValidFNN ($strFNN))
		{
			// Check the FNN is Valid
			$oblstrError->setValue ('FNN ServiceType');
		}
		else if ($bolDifferent && $strFNN <> "" && ServiceType ($strFNN) <> $srvService->Pull ('ServiceType')->getValue ())
		{
			// Check the FNN is the Right Service Type
			$oblstrError->setValue ('FNN ServiceType');
		}
		else
		{
			$intService = $srvService->Pull ('Id')->getValue ();
			
			// Save Service Details
			$srvService->Update (
				Array (
					'FNN'                => $strFNN,
					'CostCentre'        => $_POST ['CostCentre']
				)
			);
						
			
			// Save Mobile Details
			if ($srvService->Pull ('ServiceType')->getValue () == SERVICE_TYPE_MOBILE)
			{
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
			}
			
			// Save Inbound Details
			if ($srvService->Pull ('ServiceType')->getValue () == SERVICE_TYPE_INBOUND)
			{		
				$srvService->InboundDetailUpdate (
					Array (
						'AnswerPoint' 		=> $_POST ['AnswerPoint'],
						'Configuration'		=> $_POST ['Configuration']
					)
				);
			}
			
			// Set ELB Status
			if (trim(strtoupper($_POST['ELB'])) == 'ON')
			{
				$GLOBALS['fwkFramework']->EnableELB($intService);
			}
			else
			{
				$GLOBALS['fwkFramework']->DisableELB($intService);
			}		
			
			if (isset ($_POST ['Archived']))
			{
				$intService = $srvService->ArchiveStatus (
					$_POST ['Archived'],
					$athAuthentication->AuthenticatedEmployee ()
				);
				if (!$intService)
				{
					$oblstrError->setValue ('Unarchive Fail');
				}
			}
			if ($intService)
			{
				header ('Location: ../admin/flex.php/Service/View/?Service.Id=' . $intService);
				exit;
			}
			
		}
	}
	
	// Get Cost Centres
	$ccrCostCentres = $Style->attachObject (new CostCentres);
	$ccrCostCentres->Constrain ('Account',	'=',	$actAccount->Pull ('Id')->getValue ());
	$ccrCostCentres->Sample ();

	// States List
	$sstStates = $Style->attachObject (new ServiceStateTypes);
	
	// Pull documentation information for Service
	$docDocumentation->Explain ('Service Mobile');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Service Inbound');
	
	$Style->Output (
		'xsl/content/service/edit.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue (),
		)
	);
	
?>
