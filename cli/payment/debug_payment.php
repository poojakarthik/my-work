<?php
require_once("../../flex.require.php");

// Check parameters
if (!($intPayment = (int)$argv[1]))
{
	Debug("Invalid paramater '{$argv[1]}' specified!  Please specify a valid Payment Id\n");
	die;
}


LoadApplication();

// Payment modules
$arrPaymentModules[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT]	= new PaymentModuleDirectEntryReport();


$arrColumns = Array();
$arrColumns['Id']			= "Payment.Id";
$arrColumns['Payment']		= "Payment.Payment";
$arrColumns['FileType']		= "FileImport.FileType";
$arrColumns['File']			= "FileImport.Id";
$arrColumns['SequenceNo']	= "Payment.SequenceNo";
$selPayment = new StatementSelect("Payment JOIN FileImport ON FileImport.Id = Payment.File", $arrColumns, "Payment.Id = $intPayment");
if (!$selPayment->Execute())
{
	Debug("No Payment with Id $intPayment found!\n");
	die;
}
$arrPayment = $selPayment->Fetch();

$arrPayment = Array();
$arrPayment['Payment']		= '1633-000134687961 130000004278MN  JM COMDITSIS                1000168995_122008 032-797   046124TELCO BLUE PTY L00000000G';
$arrPayment['FileType']		= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT;
$arrPayment['File']			= 1337;
$arrPayment['SequenceNo']	= 80085;


Debug($arrPaymentModules[$arrPayment['FileType']]->Normalise($arrPayment['Payment']));
Debug($arrPaymentModules[$arrPayment['FileType']]->FetchRawComplete());
?>