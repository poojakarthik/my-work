<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	
	$intAccount = $_GET['Id'];
	header("Location: vixen.php/Account/InvoicesAndPayments/?Account.Id=$intAccount");

	/*Old page
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_INVOICE | MODULE_PAYMENT;
	
	// call application
	require ('config/application.php');
	
	// Get Account
	try
	{
		// Try to pull the Account
		if ($_POST)
		{
			$actAccount = $Style->attachObject (new Account ($_POST ['Account']));
		}
		else
		{
			$actAccount = $Style->attachObject (new Account ($_GET ['Id']));
		}			
	}
	catch (Exception $e)
	{
		// Output the Account View
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	if ($_POST ['Id'])
	{
		$fwkReverse = $GLOBALS['fwkFramework']->ReversePayment((int)$_POST ['Id'], (int)$_POST ['Employee']);
	}
	
	//Check if the logged-in user is Admin
	$chkAdmin = HasPermission ($athAuthentication->Pull ('AuthenticatedEmployee')->Pull ('Privileges')->getValue (), PERMISSION_ADMIN);
	$GLOBALS['Style']->InsertDOM(Array('IsAuthenticated'=> $chkAdmin ), 'Permission');	
	
		
	
	//var_dump($fwkReverse);
	//Debug($_POST);die;
	// Retrieve the Invoices list
	$ivlInvoices = $Style->attachObject ($actAccount->Invoices ());
	
	// Retrieve the Payments list
	$payPaymentsnew = $Style->attachObject ($actAccount->Payments_new());
	$payPayments = $Style->attachObject ($actAccount->Payments());


	// Retrieve the PDF Listing
	$pdlInvoices = $Style->attachObject ($actAccount->PDFInvoices ());

	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	// Output the Account View
	$Style->Output (
		'xsl/content/account/ledger.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);*/
	
?>
