<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

try
{
	DataAccess::getDataAccess()->TransactionStart();
	
	$qryQuery	= new Query();
	
	$strSQL	= "	SELECT Payment. * , (Payment.Amount - Payment.Balance), SUM( InvoicePayment.Amount ) AS ActualAmount
				FROM Payment JOIN InvoicePayment ON Payment.Id = InvoicePayment.Payment
				GROUP BY Payment.Id
				HAVING ActualAmount > ( Payment.Amount - Payment.Balance )
				AND ActualAmount = ( Payment.Amount - Payment.Balance ) * 2";
	$resPayments	= $qryQuery->Execute($strSQL);
	if ($resPayments === false)
	{
		throw new Exception($qryQuery->Error());
	}
	while ($arrPayment = $resPayments->fetch_assoc())
	{
		Log::getLog()->log("Deleting duplicate of #{{$arrPayment['Id']}}...");
		
		// Delete one of the InvoicePayment duplicates
		$resDelete	= $qryQuery->Execute("DELETE FROM InvoicePayment WHERE Payment = {$arrPayment['Id']} AND invoice_run_id = {$arrPayment['invoice_run_id']} AND Account = {$arrPayment['Account']} AND Amount = '{$arrPayment['Amount']}' LIMIT 1");
		if ($resDelete === false)
		{
			throw new Exception($qryQuery->Error());
		}
	}
	
	Log::getLog()->log("Actioned {$resPayments->num_rows} erroneous Payments.");
	
	throw new Exception("TEST MODE");
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}

?>