<?php

// Invoice_Source: This class contains abstract methods for setting/getting data for an invoice during generation.
abstract class Invoice_Source
{
	public abstract function getAccountBalance($oAccount);
	
	public abstract function markAllCDRsAsTemporaryInvoice($iInvoiceRunId, $iCustomerGroupId, $aServiceIds, $sEffectiveDate);
	
	public abstract function isAnInterimInvoiceRun($oInvoiceRun);
}

?>