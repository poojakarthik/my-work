<?php

class JSON_Handler_Invoice_View extends JSON_Handler
{
	protected	$_JSONDebug		= '';
	protected	$_aPermissions	= array(PERMISSION_OPERATOR_VIEW);
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($iId)
	{
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_aPermissions))
			{
				throw(new JSON_Handler_Invoice_View_Exception('You do not have permission to view this invoice.'));
			}
			
			// Get the orm object
			$oInvoice			= Invoice::getForId($iId);
			$oStdClassInvoice	= $oInvoice->toStdClass();
			
			// Get account info
			$oAccount							= Account::getForId($oInvoice->Account);
			$oStdClassInvoice->business_name	= $oAccount->BusinessName;
			$oStdClassInvoice->trading_name		= $oAccount->TradingName;
			
			// Get service totals info
			$aServiceTotals	=	Service_Total::getForInvoiceRunAndAccount(
									$oInvoice->invoice_run_id,
									$oInvoice->Account
								);
			
			$aStdClassServiceTotals	= array();			
			foreach ($aServiceTotals as $iServiceTotalId => $oServiceTotal)
			{
				$aStdClassServiceTotals[$iServiceTotalId]	= $oServiceTotal->toStdClass();
			}
			
			// Check if the invoice is disputed and user has credit mgmt priv, then send 'AllowResolve' so that it can be resolved
			if (($oInvoice->Status == INVOICE_DISPUTED) && AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT))
			{
				$oStdClassInvoice->allow_resolve	= true;
			}
			else
			{
				$oStdClassInvoice->allow_resolve	= false;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
						"Success"			=> true,
						"oInvoice"			=> $oStdClassInvoice,
						"aServiceTotals"	=> $aStdClassServiceTotals,	
						"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (JSON_Handler_Invoice_View_Exception $oException)
		{
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function resolveDispute($iInvoiceId, $iResolveMethod, $fResolveAmount)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_aPermissions))
			{
				throw(new JSON_Handler_Invoice_View_Exception('You do not have permission to resolve a dispute.'));
			}
			
			if ($iResolveMethod == DISPUTE_RESOLVE_PARTIAL_PAYMENT && (!is_numeric($fResolveAmount) || $fResolveAmount <= 0))
			{
				throw(new JSON_Handler_Invoice_View_Exception('Invalid payment amount specified.'));
			}
			else
			{
				$oInvoice	= Invoice::getForId($iInvoiceId);
				$oInvoice->resolve($iResolveMethod, $fResolveAmount);
				
				// All good, commit db transaction
				$oDataAccess->TransactionCommit();
				
				return 	array(
						"Success"			=> true,	
						"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
			}
		} 
		catch (JSON_Handler_Invoice_View_Exception $oException)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

class JSON_Handler_Invoice_View_Exception extends Exception
{
	// No changes
}

?>