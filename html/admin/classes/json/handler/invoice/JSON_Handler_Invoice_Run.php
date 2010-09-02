<?php

class JSON_Handler_Invoice_Run extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function commitInvoiceRun($iInvoiceRunId)
	{
		// TODO: DEV ONLY -- Remove this, don't use transaction in this function
		$oDataAccess	= DataAccess::getDataAccess();
		$oDataAccess->TransactionStart();
		
		try
		{
			Invoice_Run::getForId($iInvoiceRunId)->commit();
			
			// TODO: DEV ONLY -- Remove this, don't use transaction in this function
			$oDataAccess->TransactionRollback();
			
			return	array(
						'bSuccess'	=> true
					);
		}
		catch (Exception $oEx)
		{
			// TODO: DEV ONLY -- Remove this, don't use transaction in this function
			$oDataAccess->TransactionRollback();
			
			return array(
						"bSuccess"	=> false,
						"sMessage"	=> (Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : '')
					);
		}
	}
	
	public function deliverInvoiceRun($iInvoiceRunId)
	{
		// TODO: DEV ONLY -- Remove this, don't use transaction in this function
		$oDataAccess	= DataAccess::getDataAccess();
		$oDataAccess->TransactionStart();
		
		try
		{
			$oInvoiceRun	= Invoice_Run::getForId($iInvoiceRunId);
			// TODO: DEV ONLY -- use this condition, remove the || true (only put there so that the commit isn't required when in development)
			if ($oInvoiceRun->invoice_run_status_id === INVOICE_RUN_STATUS_COMMITTED || true)
			{
				$oInvoiceRun->deliver();
			}
			else
			{
				throw new Exception("Cannot deliver, the invoice run ({$iInvoiceRunId}) is uncommited");
			}
			
			// TODO: DEV ONLY -- Remove this, don't use transaction in this function
			//$oDataAccess->TransactionRollback();
			$oDataAccess->TransactionCommit();
			
			return	array(
						'bSuccess'	=> true
					);
		}
		catch (Exception $oEx)
		{
			// TODO: DEV ONLY -- Remove this, don't use transaction in this function
			$oDataAccess->TransactionRollback();
			
			return array(
						"bSuccess"	=> false,
						"sMessage"	=> (Employee::getForId(Flex::getUserId())->isGod() ? $oEx->getMessage() : '')
					);
		}
	}
}

class Exception_Invoice_Run 	extends Exception{}

?>