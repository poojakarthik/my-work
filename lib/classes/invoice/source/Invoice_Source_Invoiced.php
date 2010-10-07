<?php

// Invoice_Source_Invoiced: This class contains methods for setting/getting data for a copied/regenerated invoice
class Invoice_Source_Invoiced extends Invoice_Source
{
	private $_oOriginalInvoice	= null;
	
	// __construct: For a regenerated invoice, to get/set data for it we need to have a reference to the original invoice. 
	public function __construct($oOriginalInvoice)
	{
		$this->_oOriginalInvoice	= $oOriginalInvoice;
	}
	
	// getAccountBalance: For a regenerated invoice, the balance of the original invoice is reused.
	public function getAccountBalance($oAccount)
	{
		if (($this->_oOriginalInvoice === null) || ($this->_oOriginalInvoice->AccountBalance === null))
		{
			throw new Exception("Unable to calculate Account Balance for {$oAccount->Id}, cannot access original Invoice AccountBalance");
		}
		return $this->_oOriginalInvoice->AccountBalance;
	}
	
	// markAllCDRsAsTemporaryInvoice: 	For a regenerated invoice, all RATED or TEMP_INVOICE cdrs within the given service ids that started before the given date
	//									and belong the the invoice_run_id are given the status of TEMP_INVOICE.
	public function markAllCDRsAsTemporaryInvoice($iInvoiceRunId, $iCustomerGroupId, $aServiceIds, $sEffectiveDatetime)
	{
		$sSQL		= "	UPDATE 	CDR 
						SET 	Status = ".CDR_TEMP_INVOICE."
						WHERE 	Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") 
						AND 	Service IN (".implode(', ', $aServiceIds).") 
						AND 	StartDatetime <= '{$sEffectiveDatetime}'
						AND		invoice_run_id = {$iInvoiceRunId}";
		$sSQL		.= (!Customer_Group::getForId($iCustomerGroupId)->invoiceCdrCredits) ? " AND (Credit = 0 OR RecordType = 21)" : '';
		$oQuery		= new Query();
		$oResult	= $oQuery->Execute($sSQL);
		if ($oResult === FALSE)
		{
			throw new Exception("Failed to mark all cdrs as temporary invoice, DB ERROR: ".$oQuery->Error());
		}
		
		$iAffectedRows	= $oQuery->AffectedRows();
		
		Log::getLog()->log("\nMarking cdrs as temporary invoice, affected rows: $iAffectedRows");

		return true;
	}
	
	// isAnInterimInvoiceRun: 	For a regenerated invoice, since it's invoice run has a special 
	//							invoice_run_type, the original invoices (invoice runs) invoice run type is used to determine if it is an
	//							interim invoice run
	public function isAnInterimInvoiceRun($oInvoiceRun)
	{
		// Use the original invoices invoice run instead (invoice_run_type_id)
		$oInvoiceRun	= Invoice_Run::getForId($this->_oOriginalInvoice->invoice_run_id);
		return in_array($oInvoiceRun->invoice_run_type_id, array(INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_INTERIM_FIRST));
	}
}

?>