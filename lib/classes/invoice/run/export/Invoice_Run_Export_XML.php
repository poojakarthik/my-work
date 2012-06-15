<?php
class Invoice_Run_Export_XML extends Invoice_Run_Export {
	const OUTPUT_RELATIVE_PATH	= 'xml/';
	
	public function export($aAccountIds=null) {
		// Get list of Invoices
		$oStatement	= new StatementSelect("Invoice", "*", "invoice_run_id = <invoice_run_id>");
		if ($oStatement->Execute(array('invoice_run_id'=>$this->_oInvoiceRun->Id)) === false) {
			throw new Exception_Database($oStatement->Error());
		}
		
		// Generate each XML document
		while ($aInvoice = $oStatement->Fetch()) {
			if ($aAccountIds === null || in_array($aInvoice['Account'], $aAccountIds)) {
				Invoice_Export_XML::export($aInvoice);
			}
		}
	}
}
