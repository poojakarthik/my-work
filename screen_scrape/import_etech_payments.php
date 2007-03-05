<?php


	set_time_limit (0);
	
	// load framework
	$strFrameworkDir = "../framework/";
	require_once($strFrameworkDir."framework.php");
	require_once($strFrameworkDir."functions.php");
	require_once($strFrameworkDir."definitions.php");
	require_once($strFrameworkDir."config.php");
	require_once($strFrameworkDir."database_define.php");
	require_once($strFrameworkDir."db_access.php");
	require_once($strFrameworkDir."report.php");
	require_once($strFrameworkDir."error.php");
	require_once($strFrameworkDir."exception_vixen.php");
	require_once($strFrameworkDir."Color.php");
	
	define('USER_NAME', 'Import');
	
	$arrConfig	= Array();
	
	// instanciate the etech decoder
	require_once('decode_etech.php');
	$objDecoder	= new VixenDecode($arrConfig);
	
	// instanciate the import object
	require_once('vixen_import.php');
	$objImport	= new VixenImport($arrConfig);


	//------------------------------------------------------------------------//
	//	IMPORT PAYMENTS
	//------------------------------------------------------------------------//

	echo "<pre>\n\n";
	echo "IMPORT ETECH PAYMENTS\n";
	echo "=-=-=-=-=-=-=-=-=-=-=\n\n";
	
	if ($arrPayments = $objDecoder->FetchPayment())
	{		
		foreach ($arrPayments['DataArray'] as $intAccount=>$arrPayment)
		{
			echo str_pad("+ Payments for $intAccount...", 60, " ", STR_PAD_RIGHT);
			$arrPayment['Account']	= (int)$intAccount;
			Debug($arrPayment);
			
			if ($arrPayment['Id'] = $objImport->InsertPayment($arrPayment))
			{
				if ($objImport->InsertInvoicePayment($arrPayment))
				{
					echo "[   OK   ]\n";
				}
				else
				{
					echo "[ FAILED ]\n\t- Reason: InsertInvoicePayment died\n";
				}
			}
			else
			{
				echo "[ FAILED ]\n\t- Reason: InsertInvoice died\n";
			}
		}
		
		echo "Data successfully imported!\n";
	}
	else
	{
		echo "There was an error retrieving the data...\n";
	}
		
	echo "\n\n</pre>";

?>