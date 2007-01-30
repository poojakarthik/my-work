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
	$arrPage['Permission']	= PERMISSION_ADMIN;
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
					
					// TODO!bash! [  DONE  ]		Warning: mktime() expects parameter 4 to be long, string given in ... (submit with no date)
					if (!$_POST ['Date']['month'] || !$_POST ['Date']['day'] || !$_POST ['Date']['year'])
					{
						$oblstrError->setValue ('Date Invalid');
					}
					else if (!$intDate)
					{
						$oblstrError->setValue ('Date Invalid');
					}
					else if ($intDate < strtotime ("+2 days"))
					{
						$oblstrError->setValue ('Date Invalid');
					}
					else
					{
						$intNewService = $srvService->LesseePassthrough (
							$actReceiving, 
							$athAuthentication->AuthenticatedEmployee (),
							$_POST ['Date']
						);
						
						header ("Location: service_lessee_changed.php?Old=" . $srvService->Pull ('Id')->getValue () . "&New=" . $intNewService);
						exit;
					}
				}
				
				$Style->Output ('xsl/content/service/lessee/date.xsl');
				exit;
			}
			catch (Exception $e)
			{
				$oblstrError->setValue ('Invalid Account');
			}
		}
	}
	
	$Style->Output ('xsl/content/service/lessee/select.xsl');
	
?>
