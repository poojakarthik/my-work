<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// TODO!bash! [  DONE  ] Fatal error: Call to a member function getValue() on a non-object in /home/flame/vixen/intranet_app/classes/accounts/account.php on line 402
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information for Service
	$docDocumentation->Explain ('Lessee');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	try
	{
		// Try to get the Service
		$srvService 	= $Style->attachObject (new Service (($_GET ['Service']) ? $_GET ['Service'] : $_POST ['Service']));
		$actOriginal	= $Style->attachObject (new dataArray ('Account-Original', 'Account'))->Push ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		// If the service does not exist, an exception will be thrown
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	$oblstrError		= $Style->attachObject (new dataString ('Error', ''));
	
	$arrUIValues		= $Style->attachObject (new dataArray ('ui-values'));
	
	$oblstrAccount		= $arrUIValues->Push (new dataString	('Account',		$_POST ['Account']));
	$oblintDateDay		= $arrUIValues->Push (new dataInteger	('Date-day',	$_POST ['Date']['day']));
	$oblintDateMonth	= $arrUIValues->Push (new dataInteger	('Date-month',	$_POST ['Date']['month']));
	$oblintDateYear		= $arrUIValues->Push (new dataInteger	('Date-year',	$_POST ['Date']['year']));
	
	// If we've got an account, validate it
	if (isset ($_POST ['Account']))
	{
		if (empty ($_POST ['Account']))
		{
			$oblstrError->setValue ('Empty Account');
		}
		else if ($_POST ['Account'] == $actOriginal->Pull ('Id')->getValue ())
		{
			$oblstrError->setValue ('Same Account');
		}
		else
		{
			try
			{
				$actReceiving		= $Style->attachObject (new dataArray ('Account-Receiving', 'Account'))->Push (new Account ($_POST ['Account']));
				
				if ($_POST ['Date'])
				{
					$intDate = mktime (0, 0, 0, (float) $_POST ['Date']['month'], (float) $_POST ['Date']['day'], (float) $_POST ['Date']['year']);
					
					$bolDate = @checkdate ((int) $_POST ['Date']['month'], (int) $_POST ['Date']['day'], (int) $_POST ['Date']['year']);
					
					// TODO!bash! [  DONE  ]		Warning: mktime() expects parameter 4 to be long, string given in ... (submit with no date)
					if (!$_POST ['Date']['month'] || !$_POST ['Date']['day'] || !$_POST ['Date']['year'])
					{
						$oblstrError->setValue ('Date Invalid');
					}
					else if (!$bolDate)
					{
						$oblstrError->setValue ('Date Invalid');
					}
					else if ($intDate < mktime (0, 0, 0))
					{
						$oblstrError->setValue ('Date Invalid');
					}
					else
					{
						$bolTransferUnbilled = $_POST['Unbilled'];
						//Debug($_POST);die;
						//Debug($bolTransferUnbilled);die;
						//Debug($actReceiving);die;
						$arrReturnStatus = $srvService->LesseePassthrough (
							$actReceiving, 
							$athAuthentication->AuthenticatedEmployee (),
							$_POST ['Date'],
							$bolTransferUnbilled
						);
						
						// After the lessee changed is created a System note is generated
						$strEmployeeFirstName = $athAuthentication->AuthenticatedEmployee()->Pull('FirstName')->getValue();
						$strEmployeeLastName = $athAuthentication->AuthenticatedEmployee()->Pull('LastName')->getValue() ;
						$intEmployeeId = $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue();
						$strEmployeeFullName =  "$strEmployeeFirstName $strEmployeeLastName";
											
						$intAccountId = $actOriginal->Pull ('Id')->getValue();
						$intAccountGroup = $srvService->Pull ('AccountGroup')->getValue();
						$strBusinessName = $actOriginal->Pull ('BusinessName')->getValue();
						$strTradingName = $actOriginal->Pull ('TradingName')->getValue();
						$intServiceId = $srvService->Pull ('Id')->getValue();
						$strServiceFNN = $srvService->Pull ('FNN')->getValue();
						
						$intReceivingAccountId = $actReceiving->Pull ('Id')->getValue();
						$intReceivingBusinessName = $actReceiving->Pull ('BusinessName')->getValue();
						
						$strNote = "lessee changed by $strEmployeeFullName on " . date('m/d/y') . "\n";
						$strNote .= "The following changed were made:\n";
						$strNote .= "Service Id: $intServiceId\n";
						$strNote .= "Service FNN: $strServiceFNN\n";
						$strNote .= "Current Account Details\n";
						$strNote .= "+ Account Id: $intAccountId\n";
						$strNote .= "+ Business Name: $strBusinessName\n";
						$strNote .= "+ Trading Name: $strTradingName\n";
						$strNote .= "Receiving Account Details\n";
						$strNote .= "+ Account Id: $intReceivingAccountId\n";
						$strNote .= "+ Business Name: $intReceivingBusinessName\n";
						$strNote .= "Change Date: " . date("l, M j, Y g:i:s A", $intDate) . "\n";
						$strNote .= "Transfer Charges: " . (($bolTransferUnbilled)? "Yes" : "No") ;
				
						$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE_TYPE, $intEmployeeId, $intAccountGroup, $intAccountId, $intServiceId, NULL);
												
						// Transfer unbilled charges to the new lessee
						
						header ("Location: service_lessee_changed.php?Old=" . $srvService->Pull ('Id')->getValue () . "&New=" . $arrReturnStatus[0] . "&Updated=" . $arrReturnStatus[1]);
						exit;
					}
				}
				
				$Style->Output (
					'xsl/content/service/lessee/date.xsl',
					Array (
						'Account'		=> $actOriginal->Pull ('Id')->getValue (),
						'Service'		=> $srvService->Pull ('Id')->getValue ()
					)
				);
				exit;
			}
			catch (Exception $e)
			{
				$oblstrError->setValue ('Invalid Account');
			}
		}
	}
	
	$Style->Output (
		'xsl/content/service/lessee/select.xsl',
		Array (
			'Account'		=> $actOriginal->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>
