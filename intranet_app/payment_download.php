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
	
	// Payment Type
	$ptlPaymentTypes = $Style->attachObject (new PaymentTypes);
	
	// Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	if ($_POST ['PaymentType'])
	{
		// Handle the PaidOn option
		switch ($_POST ['PaidOn'])
		{
			case 'TODAY':
				$strPaidOn = date ('Y-m-d');
				break;
				
			case 'YESTERDAY':
				$strPaidOn = date ('Y-m-d', strtotime ('-1 day'));
				break;
				
			case 'CUSTOM':
				$strPaidOn = date ('Y-m-d', mktime (0, 0, 0, $_POST ['PaidOn:CUSTOM:month'], $_POST ['PaidOn:CUSTOM:day'], $_POST ['PaidOn:CUSTOM:year']));
				break;
		}
		
		if (!$ptlPaymentTypes->setValue ($_POST ['PaymentType']))
		{
			$oblstrError->setValue ('PaymentType');
		}
		else
		{
			$payPayments = $Style->attachObject (new Payments);
			$payPayments->Constrain ('EnteredBy',	'EQUALS', $athAuthentication->AuthenticatedEmployee ()->Pull ('Id')->getValue ());
			$payPayments->Constrain ('PaymentType',	'EQUALS', $_POST ['PaymentType']);
			$payPayments->Constrain ('PaidOn',		'EQUALS', $strPaidOn);
			$payPayments->Sample ();
			
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="Payments-' . $strPaidOn . '.csv"');
			
			$Style->Output ('xsl/content/payment/download_csv.xsl');
			exit;
		}
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Payment');
	
	// Output the Account View
	$Style->Output ('xsl/content/payment/download.xsl');
	
?>
