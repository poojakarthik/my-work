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
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE;
	
	// call application
	require ('config/application.php');
	
	$oblarrServices = $Style->attachObject (new dataArray ('Services'));
	
	$oblarrServiceOld = $oblarrServices->Push (new dataArray ('Old'));
	$oblarrServiceNew = $oblarrServices->Push (new dataArray ('New'));
	
	try
	{
		$oblarrServiceOld->Push (new Service ($_GET ['Old']));
		$oblarrServiceNew->Push (new Service ($_GET ['New']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	$Style->Output ('xsl/content/service/lessee/changed.xsl');
	
?>
