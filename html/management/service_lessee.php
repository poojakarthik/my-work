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
						
						// Build System Note if the change of lessee worked
						if ($arrReturnStatus[0] !== FALSE)
						{
							// The new service was created, signifying that the Change of Lessee worked
							$intEmployeeId = $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue();
												
							$intOriginalAccountId		= $actOriginal->Pull('Id')->getValue();
							$intOriginalAccountGroup	= $srvService->Pull('AccountGroup')->getValue();
							$strOriginalAccountName		= trim($actOriginal->Pull('BusinessName')->getValue());
							if ($strOriginalAccountName == NULL)
							{
								$strOriginalAccountName	= trim($actOriginal->Pull('TradingName')->getValue());
							}
							
							$intOriginalServiceId		= $srvService->Pull('Id')->getValue();
							$strServiceFNN 				= $srvService->Pull('FNN')->getValue();
							
							$intReceivingAccountId		= $actReceiving->Pull('Id')->getValue();
							$intReceivingAccountGroup	= $actReceiving->Pull('AccountGroup')->getValue();
							$strReceivingAccountName	= trim($actReceiving->Pull('BusinessName')->getValue());
							if ($strReceivingAccountName == NULL)
							{
								$strReceivingAccountName = trim($actReceiving->Pull('TradingName')->getValue());
							}
							$intReceivingServiceId		= $arrReturnStatus[0];
							
							// The note bit regarding the transfer of unbilled charges
							$strUnbilledChargesNoteClause = "";
							if ($bolTransferUnbilled)
							{
								// The user wanted to transfer the unbilled charges
								if ($arrReturnStatus[1] !== FALSE)
								{
									// The transfer didn't fail
									$strUnbilledChargesNoteClause = "All unbilled charges have been transfered.";
								}
								else
								{
									// The transfer failed
									$strUnbilledChargesNoteClause = "Transfering the unbilled charges failed, unexpectedly.";
								}
							}
							
							$strDate = "{$_POST['Date']['day']}/{$_POST['Date']['month']}/{$_POST['Date']['year']}";
							
							$strNoteForOriginalAccount  = "A change of lessee was scheduled.  This service is scheduled to move to account $intReceivingAccountId, '$strReceivingAccountName' on $strDate.  $strUnbilledChargesNoteClause";
							$strNoteForReceivingAccount = "A change of lessee was scheduled.  This account will receive this service from account $intOriginalAccountId, '$strOriginalAccountName' on $strDate.  $strUnbilledChargesNoteClause";

							$GLOBALS['fwkFramework']->AddNote($strNoteForOriginalAccount, SYSTEM_NOTE_TYPE, $intEmployeeId, $intOriginalAccountGroup, $intOriginalAccountId, $intOriginalServiceId, NULL);
							$GLOBALS['fwkFramework']->AddNote($strNoteForReceivingAccount, SYSTEM_NOTE_TYPE, $intEmployeeId, $intReceivingAccountGroup, $intReceivingAccountId, $intReceivingServiceId, NULL);
						}
						
						// Load the service_lessee_changed page
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
