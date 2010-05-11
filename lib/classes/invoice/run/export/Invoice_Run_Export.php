<?php
abstract class Invoice_Run_Export
{
	const	OUTPUT_BASE_PATH	= 'invoices/';
	
	protected	$_oInvoiceRun;
	protected	$_oCarrierModule;
	
	public function __construct($mInvoiceRun, $mCarrierModule)
	{
		$this->_oInvoiceRun		= ($mInvoiceRun		instanceof Invoice_Run)		? $mInvoiceRun		: new Invoice_Run(array('Id'=>ORM::extractId($mInvoiceRun)), true);
		$this->_oCarrierModule	= ($mCarrierModule	instanceof Carrier_Module)	? $mCarrierModule	: Carrier_Module::getForId(ORM::extractId($mCarrierModule));
	}
	
	abstract function export($aAccountIds=null);
	
	//abstract public function deliver($mInvoiceRun);
	
	public static function getModulesForCustomerGroup($mCustomerGroup)
	{
		return Carrier_Module::getForCarrierModuleTypeAndCustomerGroup(MODULE_TYPE_INVOICE_RUN_EXPORT, $mCustomerGroup);
	}
}
?>