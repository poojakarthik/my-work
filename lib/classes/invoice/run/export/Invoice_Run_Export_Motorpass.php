<?php
class Invoice_Run_Export_Motorpass extends Invoice_Run_Export
{
	const	OUTPUT_RELATIVE_PATH	= 'motorpass/';
	
	public function export($aAccountIds=null)
	{
		$sOutputPath	= FILES_BASE_PATH
						.self::OUTPUT_BASE_PATH
						.self::OUTPUT_RELATIVE_PATH
						.$this->_oInvoiceRun->Id.'/'
						.$this->_makeFileName();
		@mkdir(dirname($sOutputPath));
		
		$rOutputFile	= fopen($sOutputPath, 'w');
		
		$aLines	= array();
		
		// Config
		$sReceiverCode	= $this->_oCarrierModule->getConfig()->ReceiverCode;
		$sSenderCode	= $this->_oCarrierModule->getConfig()->SenderCode;
		$iFileTimestamp	= time();
		
		// Header
		$aData[]	=	array
						(
							'00',
							$sSenderCode,
							$sReceiverCode,
							date('d/m/Y', $iFileTimestamp),
							date('H:i', $iFileTimestamp)
						);
		
		// Content
		$oQuery	= new Query();
		
		$sInvoicesSQL	= "	SELECT		i.Id				AS invoice_id,
										i.Account			AS account_id,
										i.Total				AS invoice_total,
										i.Tax				AS invoice_tax,
										i.Balance			AS invoice_balance,
										rm.account_number	AS motorpass_account_number
							
							FROM		Invoice i
										JOIN account_history ah ON	(
																		i.Account = ah.account_id
																		AND change_timestamp < ''
																		AND ah.id =	(
																						SELECT		id
																						FROM		account_history
																						WHERE		account_id = i.Account
																									AND change_timestamp < '{$this->_oInvoiceRun->billing_period_end_datetime}'
																						ORDER BY	change_timestamp DESC
																						LIMIT		1
																					)
																	)
										JOIN rebill r ON	(
																i.Account = r.account_id
																AND r.created_timestamp < ''
																AND r.rebill_type_id = (SELECT id FROM rebill_type WHERE system_name = 'MOTORPASS' LIMIT 1)
																AND r.id =	(
																				SELECT		id
																				FROM		rebill
																				WHERE		account_id = i.Account
																							AND created_timestamp < '{$this->_oInvoiceRun->billing_period_end_datetime}'
																				ORDER BY	created_timestamp DESC
																				LIMIT		1
																			)
															)
										JOIN rebill_motorpass rm ON (rm.rebill_id = r.id)
							
							WHERE		i.invoice_run_id = {$this->_oInvoiceRun->Id}";
		
		if (($mInvoicesResult = $oQuery->Execute($sInvoicesSQL)) === false)
		{
			throw new Exception($oQuery->Error());
		}
		$iInvoiceCount	= 0;
		while ($aInvoice = $mInvoicesResult->fetch_assoc())
		{
			if ($aAccountIds === null || in_array($aInvoice['account_id'], $aAccountIds))
			{
				$fBillingAmount	= round($aInvoice['invoice_total']+$aInvoice['invoice_tax'], 2);
				
				$aData[]	=	array
								(
									$aInvoice['invoice_id'],
									$aInvoice['motorpass_account_number'],
									$fBillingAmount,
									''
								);
				$iInvoiceCount++;
			}
		}
		
		// Footer
		$aData[]	=	array
						(
							'99',
							date('d/m/Y', $iFileTimestamp),
							date('H:i', $iFileTimestamp),
							date('d/m/Y', strtotime($this->_oInvoiceRun->BillingDate)),
							date('d/m/Y', strtotime($this->_oInvoiceRun->BillingDate)),
							$iInvoiceCount
						);
		
		// Convert Array of Arrays into a CSV
		foreach ($aLines as $aData)
		{
			foreach ($aData as &$sField)
			{
				$sField	= str_replace(',', '\\,', str_replace('\\', '\\\\', $sField));
			}
			unset($sField);
			fwrite($rOutputFile, implode(',', $aData)."\n");
		}
		fclose($rOutputFile);
		
		// Return the number of Invoices that were exported
		return $iInvoiceCount;
	}
	
	private function _makeFileName()
	{
		// Naming convention:
		// <CUSTOMERGROUP>_BILLING_<YYYYMMDD>_<HHMMSS>.TXT
		return	strtoupper(preg_replace("/[^A-Z0-9]/i", '', Customer_Group::getForId($this->_oInvoiceRun->customer_group_id)->externalName))
				.'_BILLING_'
				.date("Ymd")
				.date("His")
				.'.TXT';
	}
}
?>