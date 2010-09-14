<?php

class JSON_Handler_Invoice_Run extends JSON_Handler
{
	protected	$_sJSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_sJSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function commitInvoiceRun($iInvoiceRunId)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		
		// TODO: DEV ONLY -- Remove this, don't use transaction in this function
		$oDataAccess	= DataAccess::getDataAccess();
		$oDataAccess->TransactionStart();
		
		try
		{
			Log::getLog()->log("Attempting to commit invoice run {$iInvoiceRunId}");
			
			Invoice_Run::getForId($iInvoiceRunId)->commit();
			
			Log::getLog()->log("Invoice run {$iInvoiceRunId} committed successfully");
			
			// TODO: DEV ONLY -- Remove this, don't use transaction in this function
			$oDataAccess->TransactionRollback();
			
			return	array(
						'bSuccess'	=> true,
						'sDebug'	=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
		catch (Exception $oEx)
		{
			// TODO: DEV ONLY -- Remove this, don't use transaction in this function
			$oDataAccess->TransactionRollback();
			
			return array(
						"bSuccess"	=> false,
						"sMessage"	=> ($bIsGod ? $e->getMessage() : ''),
						'sDebug'	=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
	}
	
	public function deliverInvoiceRun($iInvoiceRunId)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		
		try
		{
			Log::getLog()->log("Attempting to deliver invoice run {$iInvoiceRunId}");
			
			$oInvoiceRun	= Invoice_Run::getForId($iInvoiceRunId);
			
			// TODO: DEV ONLY -- use the commented condition, the one without the '|| true' (only put there so that the commit isn't required when in development)
			if ($oInvoiceRun->invoice_run_status_id === INVOICE_RUN_STATUS_COMMITTED || true)
			//if ($oInvoiceRun->invoice_run_status_id === INVOICE_RUN_STATUS_COMMITTED)
			{
				$oInvoiceRun->deliver();
			}
			else
			{
				throw new Exception("Cannot deliver, the invoice run ({$iInvoiceRunId}) is uncommited");
			}
		
			Log::getLog()->log("Invoice run {$iInvoiceRunId} delivered successfully");
			
			return	array(
						'bSuccess'	=> true,
						'sDebug'	=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
		catch (Exception $oEx)
		{
			return array(
						"bSuccess"	=> false,
						"sMessage"	=> ($bIsGod ? $oEx->getMessage() : ''),
						'sDebug'	=> ($bIsGod ? $this->_sJSONDebug : false)
					);
		}
	}
}

class Exception_Invoice_Run 	extends Exception{}

?>