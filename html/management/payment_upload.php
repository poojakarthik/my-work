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
	$arrPage['Modules']		= MODULE_BASE | MODULE_PAYMENT;
	
	// call application
	require ('config/application.php');
	
	// Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Payment Types
	$ptlPaymentTypes = $Style->attachObject (new PaymentTypes);
	
	if (is_uploaded_file ($_FILES ['BillFile'] ['tmp_name']))
	{
		if (!$ptlPaymentTypes->setValue ($_POST ['PaymentType']))
		{
			$oblstrError->setValue ('PaymentType');
		}
		else
		{
			$strFilename = PATH_PAYMENT_UPLOADS . "/" . $_POST ['PaymentType'] . "/" . date ("Y-m-d-H-i-s-") . sha1 (uniqid (rand (), true));
			move_uploaded_file ($_FILES ['BillFile'] ['tmp_name'], $strFilename);
			chmod ($strFilename, 0755);
			
			header ("Location: payment_uploaded.php");
			exit;
		}
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Payment');
	
	// Output the Account View
	$Style->Output ('xsl/content/payment/upload.xsl');
	
?>
