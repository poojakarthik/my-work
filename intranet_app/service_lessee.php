<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	// Pull documentation information for Service
	$docDocumentation->Explain ('Lessee');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	try
	{
		if ($_SERVER ['REQUEST_METHOD'] == "GET")
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
				$srvService->LesseePassthrough (
					$actReceiving, 
					$_POST ['Date']
				);
				
		//		header ("Location: service_view.php?Id=" . $srvService->Pull ('Id')->getValue ());
				exit;
			}
			
			$Style->Output ('xsl/content/service/lessee_date.xsl');
			exit;
		}
		catch (Exception $e)
		{
			$oblstrError->setValue ('Invalid Account');
		}
	}
	
	$Style->Output ('xsl/content/service/lessee_select.xsl');
	
?>
