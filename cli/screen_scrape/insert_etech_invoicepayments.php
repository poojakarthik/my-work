<?php


	set_time_limit (0);
	
	// load framework
	$strFrameworkDir = "../framework/";
	require_once($strFrameworkDir."framework.php");
	require_once($strFrameworkDir."functions.php");
	require_once($strFrameworkDir."definitions.php");
	require_once($strFrameworkDir."config.php");
	require_once($strFrameworkDir."db_access.php");
	require_once($strFrameworkDir."report.php");
	require_once($strFrameworkDir."error.php");
	require_once($strFrameworkDir."exception_vixen.php");
	require_once($strFrameworkDir."Color.php");
	
	define('USER_NAME', 'Import');
	
	$arrConfig	= Array();
	
	$selEtechPayments	= new StatementSelect(	"Payment",
												"*",
												"Payment = 'Scraped from Etech'");
											
	$insInvoicePayment	= new StatementInsert("InvoicePayment");
											
	//------------------------------------------------------------------------//
	//	INSERT INVOICEPAYMENTS
	//------------------------------------------------------------------------//

	echo "<pre>\n\n";
	echo "INSERT ETECH INVOICEPAYMENTS\n";
	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n\n";
	
	if ($selEtechPayments->Execute() === FALSE)
	{
		Debug($selEtechPayments->Error());
		die;
	}
	
	$arrPayments = $selEtechPayments->FetchAll();
	
	foreach ($arrPayments as $arrPayment)
	{
		echo str_pad("+ InvoicePayment for {$arrPayment['Id']}...", 60, " ", STR_PAD_RIGHT);
		
		$arrData['InvoiceRun']		= "Etech";
		$arrData['Account']			= $arrPayment['Account'];
		$arrData['AccountGroup']	= $arrPayment['AccountGroup'];
		$arrData['Payment']			= $arrPayment['Id'];
		$arrData['Amount']			= $arrPayment['Amount'];
		
		if (!$insInvoicePayment->Execute($arrData))
		{
			echo "[ FAILED ]\n";
		}
		else
		{
			echo "[   OK   ]\n";
		}
	}
			
	echo "\InvoicePayments successfully generated!\n";
		
	echo "\n\n</pre>";

?>