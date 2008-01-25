<?php
require_once("../framework/require.php");

// Check parameters
if (!($intPayment = (int)$argv[1]))
{
	Debug("Invalid paramater '{$argv[1]}' specified!  Please specify a valid Payment Id\n");
	die;
}


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

/*$arrPayment = Array();
$arrPayment['Payment']		= '"Westpac Banking Corporation","ABN 33 007 457 141","DeskBank Receivables","Remittance Processing",2/03/2007,"File Date","1/03/2007","Total Value",$6,918.72,"Total Items",25,"Reported Value",$6,918.72,"Reported Items",25,"Service Id","0178","Service Name","4TELCO BLUE PTY LTD","Account Number","164100","Account BSB","034010","Client","Item","Transaction","Originating","Tidd Receipt","Voucher","BPay Receipt","Transaction","Name","Amount","Type","System","Number","Trace Number","Number","Type Code","1646864",$39.41,"B","IB","        ","  2   0000000000","WBC280220072427599VRU","9302",1';
$arrPayment['FileType']		= 2;
$arrPayment['File']			= 1337;
$arrPayment['SequenceNo']	= 80085;*/


Debug($arrPaymentModules[$arrPayment['FileType']]->Normalise($arrPayment['Payment']));
Debug($arrPaymentModules[$arrPayment['FileType']]->FetchRawComplete());
?>