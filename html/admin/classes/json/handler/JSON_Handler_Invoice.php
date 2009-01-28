<?php

class JSON_Handler_Invoice extends JSON_Handler
{
	
	public function generateInterimInvoice($intAccount, $intInvoiceRunType)
	{
		try
		{
			// Check user permissions
			if (!(AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || AuthenticatedUser()->UserHasPerm(PERMISSION_ACCOUNTS)))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			// Attempt to generate the interim/final Invoice
			try
			{
				// Start the Transaction
				DataAccess::getDataAccess()->TransactionStart();
				
				// TODO: Generate the Invoice
				
				// Commit the Transaction
				DataAccess::getDataAccess()->TransactionCommit();
			}
			catch (Exception $eException)
			{
				DataAccess::getDataAccess()->TransactionRollback();
				
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
							"intInvoiceRunType"			=> $intInvoiceRunType,
							"intAccountId"				=> $intAccount
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
	
	public function getPreGenerateValues($intAccount)
	{
		try
		{
			// Check user permissions
			if (!(AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN) || AuthenticatedUser()->UserHasPerm(PERMISSION_ACCOUNTS)))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
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
				
				// Adjustment Totals
				$arrAdjustmentTotals	= array(
													'CR'	=> array(
																		'Count'	=> 0,
																		'Total'	=> 0.0
																	),
													'DR'	=> array(
																		'Count'	=> 0,
																		'Total'	=> 0.0
																	)
												);
				$resAdjustmentTotals	= $qryQuery->Execute(	"SELECT Charge.Nature, COUNT(Charge.Id) AS Count, SUM(Charge.Amount) AS Total " .
																"FROM Charge LEFT JOIN Service ON Service.Id = Charge.Service LEFT JOIN service_status ON service_status.id = Service.Status " .
																"WHERE Charge.Account = {$intAccount} AND Charge.Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND (service_status.can_invoice = 1 OR Charge.Service IS NULL)" .
																"GROUP BY Charge.Nature");
				if ($resAdjustmentTotals === false)
				{
					throw new Exception($qryQuery->Error());
				}
				while ($arrAdjustmentTotal = $resAdjustmentTotals->fetch_assoc())
				{
					$arrAdjustmentTotals[$arrAdjustmentTotal['Nature']]['Count']	= $arrAdjustmentTotal['Count'];
					$arrAdjustmentTotals[$arrAdjustmentTotal['Nature']]['Total']	= $arrAdjustmentTotal['Total'];
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
				$resCDRTotals	= $qryQuery->Execute(	"SELECT CASE WHEN Credit = 0 THEN 'DR' ELSE 'CR' END AS Nature, COUNT(Id) AS Count, SUM(Charge) AS Total " .
														"FROM CDR LEFT JOIN Service ON Service.Id = CDR.Service LEFT JOIN service_status ON service_status.id = Service.Status " .
														"WHERE Account = {$intAccount} AND Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") AND service_status.can_invoice = 1 " .
														"GROUP BY Nature");
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
							"intInvoiceRunType"			=> $intInvoiceRunType,
							"intAccountId"				=> $intAccount,
							"bolInvoiceCDRCredits"		=> (bool)$arrInvoiceCDRCredits['invoice_cdr_credits'],
							
							"intAdjustmentCreditCount"	=> $arrAdjustmentTotals['CR']['Count'],
							"intAdjustmentCreditTotal"	=> round($arrAdjustmentTotals['CR']['Total'], 2),
							"intAdjustmentDebitCount"	=> $arrAdjustmentTotals['DR']['Count'],
							"intAdjustmentDebitTotal"	=> round($arrAdjustmentTotals['DR']['Total'], 2),
							
							"intCDRCreditCount"			=> $arrCDRTotals['CR']['Count'],
							"intCDRCreditTotal"			=> round($arrCDRTotals['CR']['Total'], 2),
							"intCDRDebitCount"			=> $arrCDRTotals['DR']['Count'],
							"intCDRDebitTotal"			=> round($arrCDRTotals['DR']['Total'], 2),
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> false,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
}

?>
