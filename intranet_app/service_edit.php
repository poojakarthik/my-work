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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_COST_CENTRE;
	
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
	
	// Error String
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	
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
			
			$srvService->Update (
				Array (
					"FNN"				=> $strFNN,
					"CostCentre"		=> $_POST ['CostCentre']
				)
			);
			
			if (isset ($_POST ['Archived']))
			{
				$intService = $srvService->ArchiveStatus (
					$_POST ['Archived'],
					$athAuthentication->AuthenticatedEmployee ()
				);
			}
			
			header ("Location: service_view.php?Id=" . $intService);
			exit;
		}
	}
	
	// Get Cost Centres
	$ccrCostCentres = $Style->attachObject (new CostCentres);
	$ccrCostCentres->Constrain ('Account',	'=',	$actAccount->Pull ('Id')->getValue ());
	$ccrCostCentres->Sample ();
	
	// Pull documentation information for Service
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output (
		'xsl/content/service/edit.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>
