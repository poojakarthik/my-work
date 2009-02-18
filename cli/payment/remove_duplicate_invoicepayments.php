<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

try
{
	DataAccess::getDataAccess()->TransactionStart();
	
	$qryQuery	= new Query();
	
	$strSQL	= "	SELECT Payment. * , (Payment.Amount - Payment.Balance), SUM( InvoicePayment.Amount ) AS ActualAmount, InvoicePayment.invoice_run_id
				FROM Payment JOIN InvoicePayment ON Payment.Id = InvoicePayment.Payment
				GROUP BY Payment.Id, Account, InvoicePayment.invoice_run_id
				HAVING ActualAmount > ( Payment.Amount - Payment.Balance )
				AND ActualAmount = ( Payment.Amount - Payment.Balance ) * 2";
	$resPayments	= $qryQuery->Execute($strSQL);
	if ($resPayments === false)
	{
		throw new Exception($qryQuery->Error());
	}
	while ($arrPayment = $resPayments->fetch_assoc())
	{
		Log::getLog()->log("Deleting duplicate of #{$arrPayment['Id']}...");
		
		// Delete one of the InvoicePayment duplicates
		$strDeleteSQL	= "DELETE FROM InvoicePayment WHERE Payment = {$arrPayment['Id']} AND invoice_run_id = {$arrPayment['invoice_run_id']} AND Account = {$arrPayment['Account']} LIMIT 1";
		Log::getLog()->log($strDeleteSQL);
		$resDelete	= $qryQuery->Execute($strDeleteSQL);
		if ($resDelete === false)
		{
			throw new Exception($qryQuery->Error());
		}
	}
	
	Log::getLog()->log("Actioned {$resPayments->num_rows} erroneous Payments.");
	
	//throw new Exception("TEST MODE");
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}

?>