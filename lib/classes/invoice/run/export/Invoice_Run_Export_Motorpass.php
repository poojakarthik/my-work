<?php
class Invoice_Run_Export_Motorpass extends Invoice_Run_Export
{
	const	OUTPUT_RELATIVE_PATH	= 'motorpass/';
	
	public static function export($mInvoiceRun, $aInvoices=null)
	{
		$oInvoiceRun	= ($mInvoiceRun instanceof ORM) ? $mInvoiceRun : Invoice_Run::getForId(ORM::extractId($mInvoiceRun));
		
		$sOutputPath	= FILES_BASE_PATH
						.self::OUTPUT_BASE_PATH
						.self::OUTPUT_RELATIVE_PATH
						.$oInvoiceRun->Id.'/'
						.self::_makeFileName($oInvoiceRun);
		@mkdir(dirname($sOutputPath));
		
		$rOutputFile	= fopen($sOutputPath, 'w');
		
		$aLines	= array();
		
		// Header
		fwrite($rOutputFile, "00,TBLU,RED,".date('d/m/Y').",".date('H:i')."\n");
		
		// Content
		$oQuery	= new Query();
		
		$sInvoicesSQL	= "	SELECT		i.Id				AS invoice_id
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
																									AND change_timestamp < '{$oInvoiceRun->billing_period_end_datetime}'
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
																							AND created_timestamp < '{$oInvoiceRun->billing_period_end_datetime}'
																				ORDER BY	created_timestamp DESC
																				LIMIT		1
																			)
															)
										JOIN rebill_motorpass rm ON (rm.rebill_id = r.id)
							
							WHERE		i.invoice_run_id = {$oInvoiceRun->Id}";
		
		if (($mInvoicesResult = $oQuery->Execute($sGetInvoicesSQL)) === false)
		{
			throw new Exception($oQuery->Error());
		}
		while ($aInvoice = $mInvoicesResult->fetch_assoc())
		{
			$fBillingAmount	= round($aInvoice['invoice_total']+$aInvoice['invoice_tax'], 2);
			fwrite($rOutputFile, "{$aInvoice['invoice_id']},{$aInvoice['motorpass_account_number']},{$fBillingAmount},");
		}
		
		// Footer
		// HACKHACKHACK: Using TBLU and RED here!!!
		fwrite($rOutputFile, "00,TBLU,RED,".date('d/m/Y').",".date('H:i')."\n");
		
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
	}
	
	private static function _makeFileName($oInvoiceRun)
	{
		// Naming convention:
		// <CUSTOMERGROUP>_BILLING_<YYYYMMDD>_<HHMMSS>.TXT
		return	strtoupper(preg_replace("/[^A-Z0-9]/i", '', Customer_Group::getForId($oInvoiceRun->customer_group_id)->externalName))
				.'_BILLING_'
				.date("Ymd")
				.date("His")
				.'.TXT';
	}
}
?>