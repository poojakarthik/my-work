<?php
abstract class Invoice_Run_Export
{
	const	OUTPUT_BASE_PATH	= 'invoice/';
	
	abstract public function export($mInvoiceRun, $aInvoiceIds=null);
	
	//abstract public static function deliver($mInvoiceRun);
	
	public static function getModulesForCustomerGroup($mCustomerGroup)
	{
		return Carrier_Module::getForCarrierModuleTypeAndCustomerGroup(MODULE_TYPE_INVOICE_RUN_EXPORT, $mCustomerGroup);
	}
}
?>