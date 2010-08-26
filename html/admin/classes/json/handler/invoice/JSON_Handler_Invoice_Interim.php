<?php

class JSON_Handler_Invoice_Interim extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getPreGenerateValues($intAccount)
	{
		try
		{
			// Check user permissions
			if (!(AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT)))
			{
				return array(
								'Success'			=> false,
								'Error'				=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			// Attempt to generate the interim/final Invoice
			try
			{
				// Start the Transaction
				DataAccess::getDataAccess()->TransactionStart();
				
				$qryQuery	= new Query();
				
				// Load Objects
				$objAccount			= new Account(array('Id'=>$intAccount), false, true);
				
				// Invoice Run Type
				$intInvoiceRunType	= $objAccount->getInterimInvoiceType();
				
				// Check the last Invoice Type
				$rLastInvoiceType	= $qryQuery->Execute("SELECT ir.Id, ir.BillingDate, ir.invoice_run_type_id FROM Invoice i JOIN InvoiceRun ir ON (i.invoice_run_id = ir.Id) WHERE i.Account = {$objAccount->Id} AND i.Status != ".INVOICE_TEMP." ORDER BY BillingDate DESC");
				if ($rLastInvoiceType === false)
				{
					throw new Exception($qryQuery->Error());
				}
				$aLastInvoiceType	= $rLastInvoiceType->fetch_assoc();
				if ($aLastInvoiceType && in_array($aLastInvoiceType['invoice_run_type_id'], array(INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM_FIRST)))
				{
					throw new Exception_Invoice_Interim_NotAllowed("You are not permitted to generate a ".GetConstantDescription($intInvoiceRunType, 'invoice_run_type').", as the last Invoice Run was a ".GetConstantDescription($aLastInvoiceType['invoice_run_type_id'], 'invoice_run_type').", dated ".date('d/m/Y', strtotime($aLastInvoiceType['BillingDate'])).".");
				}
				
				$strTodaysDate			= date("Y-m-d");
				$strTodaysDatetime		= $strTodaysDate." 00:00:00";
				$strTomorrowsDate		= date("Y-m-d", strtotime("+1 day", strtotime($strTodaysDate)));
				$strTomorrowsDatetime	= $strTomorrowsDate." 00:00:00";
				
				// Check if there is a scheduled LIVE Invoice Run today or tomorrow
				$strScheduledDatetime	= Invoice_Run::predictNextInvoiceDate($objAccount->CustomerGroup);
				if ($strScheduledDatetime === $strTodaysDatetime)
				{
					throw new Exception_Invoice_Interim_NotAllowed("You are not permitted to generate a ".GetConstantDescription($intInvoiceRunType, 'invoice_run_type').", as there is an Invoice Run scheduled to run today.");
				}
				elseif ($strScheduledDatetime === $strTomorrowsDatetime)
				{
					throw new Exception_Invoice_Interim_NotAllowed("You are not permitted to generate a ".GetConstantDescription($intInvoiceRunType, 'invoice_run_type').", as there is an Invoice Run scheduled to run tomorrow.");
				}
				
				// Check if there has already been a Committed Interim/Final Invoice today (well, with tomorrow's date)
				$resInterimInvoiceRuns	= $qryQuery->Execute(	"SELECT InvoiceRun.Id, invoice_run_type_id " .
																"FROM InvoiceRun JOIN Invoice ON InvoiceRun.Id = Invoice.invoice_run_id " .
																"WHERE InvoiceRun.BillingDate = '{$strTomorrowsDate}' AND Invoice.Account = {$objAccount->Id} AND invoice_run_status_id IN (".INVOICE_RUN_STATUS_COMMITTING.", ".INVOICE_RUN_STATUS_COMMITTED.") AND invoice_run_type_id IN (".INVOICE_RUN_TYPE_INTERIM.", ".INVOICE_RUN_TYPE_FINAL.", ".INVOICE_RUN_TYPE_INTERIM_FIRST.") " .
																"LIMIT 1");
				if ($resInterimInvoiceRuns === false)
				{
					throw new Exception($qryQuery->Error());
				}
				if ($arrInterimInvoiceRun = $resInterimInvoiceRuns->fetch_assoc())
				{
					throw new Exception_Invoice_Interim_NotAllowed("You are not permitted to generate ".(($intInvoiceRunType === INVOICE_RUN_TYPE_FINAL) ? 'a ' : 'an ').GetConstantDescription($intInvoiceRunType, 'invoice_run_type').", as there there has already been ".(($arrInterimInvoiceRun['invoice_run_type_id'] === INVOICE_RUN_TYPE_FINAL) ? 'a ' : 'an ').GetConstantDescription($arrInterimInvoiceRun['invoice_run_type_id'], 'invoice_run_type')." generated today.");
				}
				
				// Charge Totals
				$arrChargeTotals	= array(
													'CR'	=> array(
																		'Count'	=> 0,
																		'Total'	=> 0.0
																	),
													'DR'	=> array(
																		'Count'	=> 0,
																		'Total'	=> 0.0
																	)
												);
				$resChargeTotals	= $qryQuery->Execute(	"SELECT Charge.Nature, COUNT(Charge.Id) AS Count, SUM(Charge.Amount) AS Total " .
																"FROM Charge LEFT JOIN Service ON Service.Id = Charge.Service LEFT JOIN service_status ON service_status.id = Service.Status " .
																"WHERE Charge.Account = {$intAccount} AND Charge.Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND (service_status.can_invoice = 1 OR Charge.Service IS NULL) AND ChargeType NOT IN ('PCR', 'PCAD', 'PCAR', 'PDCR') " .
																"GROUP BY Charge.Nature");
				if ($resChargeTotals === false)
				{
					throw new Exception($qryQuery->Error());
				}
				while ($arrChargeTotal = $resChargeTotals->fetch_assoc())
				{
					$arrChargeTotals[$arrChargeTotal['Nature']]['Count']	= $arrChargeTotal['Count'];
					$arrChargeTotals[$arrChargeTotal['Nature']]['Total']	= $arrChargeTotal['Total'];
				}
				
				// CDR Totals
				$arrCDRTotals	= array(
													'CR'	=> array(
																		'Count'	=> 0,
																		'Total'	=> 0.0
																	),
													'DR'	=> array(
																		'Count'	=> 0,
																		'Total'	=> 0.0
																	)
												);
				$resCDRTotals	= $qryQuery->Execute(	"SELECT CASE WHEN Credit = 0 THEN 'DR' ELSE 'CR' END AS Nature, COUNT(CDR.Id) AS Count, SUM(CDR.Charge) AS Total " .
														"FROM CDR LEFT JOIN Service ON Service.Id = CDR.Service LEFT JOIN service_status ON service_status.id = Service.Status " .
														"WHERE CDR.Account = {$intAccount} AND CDR.Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") AND service_status.can_invoice = 1 " .
														"GROUP BY Credit");
				if ($resCDRTotals === false)
				{
					throw new Exception($qryQuery->Error());
				}
				while ($arrCDRTotal = $resCDRTotals->fetch_assoc())
				{
					$arrCDRTotals[$arrCDRTotal['Nature']]['Count']	= $arrCDRTotal['Count'];
					$arrCDRTotals[$arrCDRTotal['Nature']]['Total']	= $arrCDRTotal['Total'];
				}
				
				// See if we can invoice Credit CDRs
				$resInvoiceCDRCredits	= $qryQuery->Execute("SELECT invoice_cdr_credits FROM CustomerGroup WHERE Id = {$objAccount->CustomerGroup}");
				if ($resInvoiceCDRCredits === false)
				{
					throw new Exception($qryQuery->Error());
				}
				$arrInvoiceCDRCredits	= $resInvoiceCDRCredits->fetch_assoc();
				
				// Commit the Transaction
				DataAccess::getDataAccess()->TransactionCommit();
			}
			catch (Exception $eException)
			{
				DataAccess::getDataAccess()->TransactionRollback();
				
				if ($eException instanceof Exception_Invoice_Interim_NotAllowed || AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
				{
					throw $eException;
				}
				else
				{
					throw new Exception("There was an internal error in Flex.  Please try again.");
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"					=> true,
							"strDebug"					=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : '',
							"intInvoiceRunType"			=> $intInvoiceRunType,
							"intAccountId"				=> $intAccount,
							"bolInvoiceCDRCredits"		=> (bool)$arrInvoiceCDRCredits['invoice_cdr_credits'],
							
							"intChargeCreditCount"	=> $arrChargeTotals['CR']['Count'],
							"fltChargeCreditTotal"	=> round($arrChargeTotals['CR']['Total'], 2),
							"intChargeDebitCount"	=> $arrChargeTotals['DR']['Count'],
							"fltChargeDebitTotal"	=> round($arrChargeTotals['DR']['Total'], 2),
							
							"intCDRCreditCount"			=> $arrCDRTotals['CR']['Count'],
							"fltCDRCreditTotal"			=> round($arrCDRTotals['CR']['Total'], 2),
							"intCDRDebitCount"			=> $arrCDRTotals['DR']['Count'],
							"fltCDRDebitTotal"			=> round($arrCDRTotals['DR']['Total'], 2),
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function generateInterimInvoice($intAccount, $intInvoiceRunType)
	{
		try
		{
			// Check user permissions
			if (!(AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT)))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			// Attempt to generate the interim/final Invoice
			$dacFlex	= DataAccess::getDataAccess();
			
			// DEBUG: Turn on Query Profiling
			//$dacFlex->setProfilingEnabled(true);
			try
			{
				$dacFlex->TransactionStart();
				
				$qryQuery	= new Query();
				
				//throw new Exception("Invoice::generateInterimInvoice() is not implemented yet!");
				
				$objAccount	= new Account(array('Id'=>$intAccount), false, true);
				
				// Calculate Billing Date
				$intInvoiceDatetime	= strtotime(date('Y-m-d', strtotime('+1 day')));
				
				// Generate the Invoice
				try
				{
					$objInvoiceRun	= new Invoice_Run();
					$objInvoiceRun->generateSingle($objAccount->CustomerGroup, $intInvoiceRunType, $intInvoiceDatetime, $intAccount);
				}
				catch (Exception $eException)
				{
					// Perform a Revoke on the Temporary Invoice Run
					if ($objInvoiceRun->Id)
					{
						$objInvoiceRun->revoke();
					}
					throw $eException;
				}
				
				// Force the Invoice to be an eBill
				$resInvoice	= $qryQuery->Execute("SELECT * FROM Invoice WHERE Account = {$objAccount->Id} AND invoice_run_id = {$objInvoiceRun->Id} LIMIT 1");
				if ($resInvoice === false)
				{
					throw new Exception($qryQuery->Error());
				}
				$objInvoice	= new Invoice($resInvoice->fetch_assoc());
				$objInvoice->DeliveryMethod	= DELIVERY_METHOD_EMAIL;
				$objInvoice->save();
				
				// Regenerate the XML to account for forced Delivery Method
				$objInvoice->export();
				
				$strInvoiceDate		= date("j M y", strtotime($objInvoiceRun->BillingDate));
				$strBillingPeriod	= date("j M y", strtotime($objInvoice->billing_period_start_datetime)) . " - " . date("j M y", strtotime($objInvoice->billing_period_end_datetime));
				
				// Get the last Invoice to determine Payments
				$resLastInvoice	= $qryQuery->Execute("SELECT * FROM Invoice WHERE Account = {$objAccount->Id} AND Id < {$objInvoice->Id} ORDER BY Id DESC LIMIT 1");
				if ($resLastInvoice === false)
				{
					throw new Exception($qryQuery->Error());
				}
				$arrLastInvoice	= $resLastInvoice->fetch_assoc();
				$fltPayments	= ($arrLastInvoice) ? max(0.0, (float)$arrLastInvoice['TotalOwing'] - $objInvoice->AccountBalance) : 0.0;
				
				$dacFlex->TransactionCommit();
			}
			catch (Exception $eException)
			{
				$dacFlex->TransactionRollback();
				
				if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
				{
					throw $eException;
				}
				else
				{
					throw new Exception("There was an internal error in Flex.  Please try again.");
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"					=> true,
							"objInvoiceRun"				=> $objInvoiceRun->toArray(),
							"objInvoice"				=> $objInvoice->toArray(),
							"strBillingPeriod"			=> $strBillingPeriod,
							"strInvoiceDate"			=> $strInvoiceDate,
							"fltPayments"				=> $fltPayments,
							"strDebug"					=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function revokeInterimInvoice($intInvoice)
	{
		try
		{
			// Check user permissions
			if (!(AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT)))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			// Attempt to revoke the interim/final Invoice
			$dacFlex	= DataAccess::getDataAccess();
			try
			{
				$dacFlex->TransactionStart();
				
				$objInvoice		= new Invoice(array('Id'=>$intInvoice), true);
				$objInvoiceRun	= new Invoice_Run(array('Id'=>$objInvoice->invoice_run_id), true);
				
				// Ensure that this Invoice Run is either Interim or Final, and is Temporary
				$arrAllowableInvoiceRunTypes	= array(INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM_FIRST);
				if ($objInvoiceRun->invoice_run_status_id === INVOICE_RUN_STATUS_TEMPORARY)
				{
					if (in_array($objInvoiceRun->invoice_run_type_id, $arrAllowableInvoiceRunTypes))
					{
						// Revoke the Invoice Run
						$objInvoiceRun->revoke();
					}
					else
					{
						throw new Exception("Invoice Run {$objInvoiceRun->Id} is not an Interim or Final Invoice Run");
					}
				}
				else
				{
					throw new Exception("Invoice Run {$objInvoiceRun->Id} is not a Temporary Invoice Run");
				}
				
				$dacFlex->TransactionCommit();
			}
			catch (Exception $eException)
			{
				$dacFlex->TransactionRollback();
				if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
				{
					throw $eException;
				}
				else
				{
					throw new Exception("There was an internal error in Flex.  Please try again.");
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"					=> true,
							"objInvoiceRun"				=> $objInvoiceRun,
							"strDebug"					=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
				$dacFlex->TransactionRollback();
				
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function commitInterimInvoice($intInvoice)
	{
		try
		{
			// Check user permissions
			if (!(AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT)))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			// Attempt to commit the interim/final Invoice
			$dacFlex	= DataAccess::getDataAccess();
			$dacFlex->setProfilingEnabled(true);
			try
			{
				$dacFlex->TransactionStart();
				
				$qryQuery	= new Query();
				
				$objInvoice		= new Invoice(array('Id'=>(int)$intInvoice), true);
				$objInvoiceRun	= new Invoice_Run(array('Id'=>(int)$objInvoice->invoice_run_id), true);
				
				// Ensure that this Invoice Run is either Interim or Final, and is Temporary
				$arrAllowableInvoiceRunTypes	= array(INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM_FIRST);
				if ($objInvoiceRun->invoice_run_status_id === INVOICE_RUN_STATUS_TEMPORARY)
				{
					if (in_array($objInvoiceRun->invoice_run_type_id, $arrAllowableInvoiceRunTypes))
					{
						// Revoke the Invoice Run
						$objInvoiceRun->commit();
					}
					else
					{
						throw new Exception("Invoice Run {$objInvoiceRun->Id} is not an Interim or Final Invoice Run");
					}
				}
				else
				{
					throw new Exception("Invoice Run {$objInvoiceRun->Id} is not a Temporary Invoice Run");
				}
				
				// Add a System Note
				$fltGrandTotal	= number_format($objInvoice->Total + $objInvoice->Tax, 2, '.', '');
				$strAn			= (($objInvoiceRun->invoice_run_type_id === INVOICE_RUN_TYPE_INTERIM) || ($objInvoiceRun->invoice_run_type_id === INVOICE_RUN_TYPE_INTERIM_FIRST)) ? 'An' : 'A';
				$strContent		= $strAn." ".GetConstantDescription($objInvoiceRun->invoice_run_type_id, 'invoice_run_type') . " has been generated to the value of \${$fltGrandTotal}";
				$objSystemNote	= Note::createSystemNote($strContent, Flex::getUserId(), $objInvoice->Account);
				
				$dacFlex->TransactionCommit();
			}
			catch (Exception $eException)
			{
				$dacFlex->TransactionRollback();
				
				if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
				{
					throw $eException;
				}
				else
				{
					throw new Exception("There was an internal error in Flex.  Please try again.");
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"					=> true,
							"objInvoiceRun"				=> $objInvoiceRun,
							"strDebug"					=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function submitAllEligible()
	{
		try
		{
			Invoice_Interim::submitAllEligible();
			return array("bSuccess" => true);
		}
		catch (Exception $e)
		{
			$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return array(
						"bSuccess"	=> false,
						"sError"	=> ($bIsGod ? $e->getMessage() : '')
					);
		}
	}
}

class Exception_Invoice_Interim_NotAllowed extends Exception{}
?>