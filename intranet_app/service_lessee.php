<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// TODO!bash! Fatal error: Call to a member function getValue() on a non-object in /home/flame/vixen/intranet_app/classes/accounts/account.php on line 402
	// TODO!bash! I give up.... I don't think there is anything left to say? do you?
	
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
		if ($_GET ['Service'])
		{
			// Using GET
			$srvService = $Style->attachObject (new Service ($_GET ['Service']));
		}
		else
		{
			// Using POST
			$srvService = $Style->attachObject (new Service ($_POST ['Service']));
		}
	}
	catch (Exception $e)
	{
		// If the service does not exist, an exception will be thrown
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	$actOriginal		= $Style->attachObject (new dataArray ('Account-Original', 'Account'))->Push ($srvService->getAccount ());
	$oblstrError		= $Style->attachObject (new dataString ('Error', ''));
	
	// If we've got an account, validate it
	
	if (isset ($_POST ['Account']))
	{
		try
		{
			$actReceiving		= $Style->attachObject (new dataArray ('Account-Receiving', 'Account'))->Push (new Account ($_POST ['Account']));
			
			if (isset ($_POST ['Date']))
			{
				// TODO!bash! Warning: mktime() expects parameter 4 to be long, string given in /home/flame/vixen/intranet_app/service_lessee.php on line 59
				// TODO!bash! submit with no date causes a PHP warning
				if (mktime (0, 0, 0, $_POST ['Date']['month'], $_POST ['Date']['day'], $_POST ['Date']['year']) < strtotime ("+48 hours", mktime (0, 0, 0)))
				{
					$oblstrError->setValue ('Date Past');
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
	
	$Style->Output ('xsl/content/service/lessee/select.xsl');
	
?>
