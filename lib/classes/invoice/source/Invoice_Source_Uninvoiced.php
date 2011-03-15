<?php

// Invoice_Source_Uninvoiced: This class contains methods for setting/getting data for a new (uninvoiced) invoice
class Invoice_Source_Uninvoiced extends Invoice_Source
{
	// getAccountBalance: For a new invoice, the balance is calculated by using the Account ORM object 
	public function getAccountBalance($oAccount)
	{
		$fAccountBalance	= $oAccount->getAccountBalance();	// We don't want to include Adjustments as they are handled elsewhere, but we do want outstanding Payments
		if ($fAccountBalance === FALSE)
		{
			throw new Exception("Unable to calculate Account Balance for {$oAccount->Id}");
		}
		return $fAccountBalance;
	}
	
	// markAllCDRsAsTemporaryInvoice: 	For a new invoice, all RATED or TEMP_INVOICE cdrs within the given service ids that started before the given date
	//									are given the status of TEMP_INVOICE and assigned an invoice_run_id
	public function markAllCDRsAsTemporaryInvoice($iInvoiceRunId, $iCustomerGroupId, $aServiceIds, $sEffectiveDatetime)
	{
		$sSQL		= "	UPDATE 	CDR 
						SET 	Status = ".CDR_TEMP_INVOICE.", 
								invoice_run_id = {$iInvoiceRunId} 
						WHERE 	Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") 
						AND 	Service IN (".implode(', ', $aServiceIds).") 
						AND 	StartDatetime <= '{$sEffectiveDatetime}'";
		$sSQL		.= (!Customer_Group::getForId($iCustomerGroupId)->invoiceCdrCredits) ? " AND (Credit = 0 OR RecordType = 21)" : '';
		$oQuery		= new Query();
		$oResult	= $oQuery->Execute($sSQL);
		if ($oResult === FALSE)
		{
			throw new Exception_Database("Failed to mark all cdrs as temporary invoice, DB ERROR: ".$oQuery->Error());
		}
		return true;
	}
	
	// isAnInterimInvoiceRun: 	For a new invoice, we check the invoice_run_type of it's invoice run to determine if it's an interim invoice run
	public function isAnInterimInvoiceRun($oInvoiceRun)
	{
		return in_array($oInvoiceRun->invoice_run_type_id, array(INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_INTERIM_FIRST));
	}
}

?>