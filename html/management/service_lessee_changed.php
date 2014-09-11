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
	
	$oblarrServices = $Style->attachObject (new dataArray ('Services'));
	
	$oblarrServiceOld = $oblarrServices->Push (new dataArray ('Old'));
	$oblarrServiceNew = $oblarrServices->Push (new dataArray ('New'));
	
	try
	{
		$srvOld = new Service ($_GET ['Old']);
		$srvNew = new Service ($_GET ['New']);
		
		$oblarrServiceOld->Push ($srvOld);
		$oblarrServiceNew->Push ($srvNew);
		
		$Style->attachObject ($srvNew);
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// TODO!Sean! Pass get variable (#transferred) to page
	// to show that the service was successfully moved
	
	$Style->Output (
		'xsl/content/service/lessee/changed.xsl',
		
		Array (
			'Account'		=> $srvNew->Pull ('Account')->getValue (),
			'Service'		=> $srvNew->Pull ('Id')->getValue ()
		)
	);
	
?>
