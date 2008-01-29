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
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_CHARGE_TYPE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$crgCharge = $Style->attachObject (new ChargeType (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/charges/charges/notfound.xsl');
		exit;
	}
	
	if (isset ($_POST ['Confirm']))
	{
		if ($_POST ['Confirm'])
		{
			// Archive it
			$crgCharge->Archive (TRUE);
		}
		
		$Style->Output ('xsl/content/charges/charges/archive_confirmed.xsl');
		exit;
	}
	
	$Style->Output ('xsl/content/charges/charges/archive_confirm.xsl');
	
?>
