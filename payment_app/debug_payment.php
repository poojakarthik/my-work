<?php

// Check parameters
if (!($intPayment = (int)$argv[1]))
{
	Debug("Invalid paramater '{$argv[1]}' specified!  Please specify a valid Payment Id\n");
	die;
}


require_once("../framework/require.php");
LoadApplication();

// Payment modules
$arrPaymentModules[PAYMENT_TYPE_BILLEXPRESS]	= new PaymentModuleBillExpress();
$arrPaymentModules[PAYMENT_TYPE_BPAY]			= new PaymentModuleBPay();
$arrPaymentModules[PAYMENT_TYPE_SECUREPAY]		= new PaymentModuleSecurePay();


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

Debug($arrPaymentModules[$arrPayment['FileType']]->Normalise($arrPayment['Payment']));
Debug($arrPaymentModules[$arrPayment['FileType']]->FetchRawComplete());
?>