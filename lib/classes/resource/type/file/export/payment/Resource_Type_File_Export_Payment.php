<?php
/**
 * Resource_Type_File_Export_Payment
 *
 * @class	Resource_Type_File_Export_Payment
 */
abstract class Resource_Type_File_Export_Payment extends Resource_Type_File_Export
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_PAYMENT_DIRECT_DEBIT;
	
	public static function exportDirectDebits()
	{
		$iTimestamp			= time();
		$aSummaryData		= array(
			'iTimestamp'	=> $iTimestamp,
			'aFiles'		=> array()
		);

		$aDirectDebitCarrierModules	= Carrier_Module::getForCarrierModuleType(self::CARRIER_MODULE_TYPE);
		foreach ($aDirectDebitCarrierModules as $oCarrierModule)
		{
			$iInvoiceDirectDebitCount	= 0;
			$fInvoiceDirectDebitValue	= 0.0;
			$iPromiseDirectDebitCount	= 0;
			$fPromiseDirectDebitValue	= 0.0;
			$iAdHocPaymentCount			= 0;
			$fAdHocPaymentValue			= 0.0;

			Log::getLog()->log("\nResource type handler {$oCarrierModule->Module}");
			Log::getLog()->log("Customer Group: ".Customer_Group::getForId($oCarrierModule->customer_group)->internal_name);
			
			$oDataAccess	= DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception("Failed to START db transaction for customer group {$oCarrierModule->customer_group}");
			}
			Log::getLog()->log("Transaction started");
			
			// Create the file export resource type
			$sModuleClassName		= $oCarrierModule->Module;
			$oResourceTypeHandler	= new $sModuleClassName($oCarrierModule);
			
			// Get all pending payment requests for the customer group & payment type associated 
			// with the carrier module
			$aExportedPaymentRequests	= array();
			$aPaymentRequests			=	Payment_Request::getForStatusAndCustomerGroupAndPaymentType(
												PAYMENT_REQUEST_STATUS_PENDING, 
												$oCarrierModule->customer_group,
												$oResourceTypeHandler->getAssociatedPaymentType()
											);
			foreach ($aPaymentRequests as $oPaymentRequest)
			{
				try
				{
					Log::getLog()->log("Payment request {$oPaymentRequest->id}");
					
					// Add to the output
					$oResourceTypeHandler->addRecord($oPaymentRequest);
					
					// Update the status of the payment request
					$oPaymentRequest->payment_request_status_id	= PAYMENT_REQUEST_STATUS_DISPATCHED;
					$oPaymentRequest->save();
					
					// Add to set of successful exports
					$aExportedPaymentRequests[]	= $oPaymentRequest;

					// Add to summary totals
					if (Payment_Request_Invoice::getForPaymentRequest($oPaymentRequest)) {
						// Invoice Direct Debit
						$iInvoiceDirectDebitCount++;
						$fInvoiceDirectDebitValue	+= (float)$oPaymentRequest->amount;
					} elseif (Payment_Request_Collection_Promise_Instalment::getForPaymentRequest($oPaymentRequest)) {
						// Promise Direct Debit
						$iPromiseDirectDebitCount++;
						$fPromiseDirectDebitValue	+= (float)$oPaymentRequest->amount;
					} else {
						// Ad Hoc Payment
						$iAdHocPaymentCount++;
						$fAdHocPaymentValue	+= (float)$oPaymentRequest->amount;
					}
				}
				catch (Exception $oException)
				{
					// Continue processing other requests
					Log::getLog()->log("Failed to export payment request, id={$oPaymentRequest->id}. ".$oException->getMessage());
				}
			}
			
			// File Summary Data
			$aFileData	= array(
				'iCarrierModule'			=> $oCarrierModule->Id,
				'iInvoiceDirectDebitCount'	=> $iInvoiceDirectDebitCount,
				'fInvoiceDirectDebitValue'	=> $fInvoiceDirectDebitValue,
				'iPromiseDirectDebitCount'	=> $iPromiseDirectDebitCount,
				'fPromiseDirectDebitValue'	=> $fPromiseDirectDebitValue,
				'iAdHocPaymentCount'		=> $iAdHocPaymentCount,
				'fAdHocPaymentValue'		=> $fAdHocPaymentValue
			);
			if (count($aExportedPaymentRequests) == 0) {
				Log::getLog()->log("No payment requests exported");
				if ($oDataAccess->TransactionRollback() === false) {
					throw new Exception("Failed to ROLLBACK db transaction for customer group {$oCarrierModule->customer_group}");
				}
				Log::getLog()->log("Transaction rolled back");
			} else {
				try {
					Log::getLog()->log("Rendering to file...");
					$oResourceTypeHandler->render()->save();

					$aFileData['sFileName']	= $oResourceTypeHandler->getFileExport()->FileName;

					foreach ($aExportedPaymentRequests as $oPaymentRequest) {
						$oPaymentRequest->file_export_id	= $oResourceTypeHandler->getFileExport()->Id;
						$oPaymentRequest->save();
					}
					
					Log::getLog()->log("Delivering...");
					$oResourceTypeHandler->deliver();
					
					if ($oDataAccess->TransactionCommit() === false) {
						throw new Exception("Failed to COMMIT db transaction for customer group {$oCarrierModule->customer_group}");
					}
					Log::getLog()->log("Transaction commited");
				} catch (Exception $oException) {
					if ($oDataAccess->TransactionRollback() === false) {
						throw new Exception("Failed to ROLLBACK db transaction for customer group {$oCarrierModule->customer_group}");
					}
					Log::getLog()->log("Transaction rolled back");
					
					throw $oException;
				}
			}

			// Attach File Summary Data
			$aSummaryData['aFiles'][]	= $aFileData;
		}

		// Summary Email
		$oPaymentExportSummaryEmail				= Email_Notification::getForSystemName('DIRECT_DEBIT_REPORT');
		$oPaymentExportSummaryEmail->subject	= 'Payment Export Summary Report from '.date('d/m/Y H:i', $iTimestamp);
		$oPaymentExportSummaryEmail->html		= self::_buildExportSummaryEmailHTMLContent($aSummaryData);
		$oPaymentExportSummaryEmail->text		= self::_buildExportSummaryEmailTextContent($aSummaryData);
		Email_Flex_Queue::get()->push($oPaymentExportSummaryEmail);
	}

	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."payment/{$iCarrier}/{$sClass}/";
	}

	private static function _buildExportSummaryEmailHTMLContent($aData) {
		$D				= new DOM_Factory();
		$oDOMDocument	= $D->getDOMDocument();
		
		// General Content
		$oContent	= $D->div(
			$D->h1('Payment Export File Report from '.date('d/m/Y H:i', $aData['iTimestamp'])),
			$D->table(
				$D->thead(
					$D->tr(
						$D->th(array('rowspan'=>2), 'File'),
						$D->th(array('colspan'=>2), 'Invoice Direct Debits'),
						$D->th(array('colspan'=>2), 'Promise Direct Debits'),
						$D->th(array('colspan'=>2), 'Ad Hoc Payments')
					),
					$D->tr(
						$D->th($D->abbr(array('title'=>'Count'), '#')),
						$D->th($D->abbr(array('title'=>'Dollar Value'), '$')),
						$D->th($D->abbr(array('title'=>'Count'), '#')),
						$D->th($D->abbr(array('title'=>'Dollar Value'), '$')),
						$D->th($D->abbr(array('title'=>'Count'), '#')),
						$D->th($D->abbr(array('title'=>'Dollar Value'), '$'))
					)
				),
				$oTableBody = $D->tbody(),
				$D->tfoot(
					$D->tr(
						$D->th('Totals'),
						$oTotalInvoiceDirectDebitCount = $D->td(),
						$oTotalInvoiceDirectDebitValue = $D->td(),
						$oTotalPromiseDirectDebitCount = $D->td(),
						$oTotalPromiseDirectDebitValue = $D->td(),
						$oTotalAdHocPaymentCount = $D->td(),
						$oTotalAdHocPaymentValue = $D->td()
					)
				)
			),
			$D->div(array('class'=>'signature'),
				$D->p('Regards'),
				$D->p('Flexor')
			)
		);
		$D->getDOMDocument()->appendChild($oContent);

		$iTotalInvoiceDirectDebitCount	= 0;
		$fTotalInvoiceDirectDebitValue	= 0.0;
		$iTotalPromiseDirectDebitCount	= 0;
		$fTotalPromiseDirectDebitValue	= 0.0;
		$iTotalAdHocPaymentCount		= 0;
		$fTotalAdHocPaymentValue		= 0.0;

		// File Details
		foreach ($aData['aFiles'] as $aFileData) {
			// Data
			$iInvoiceDirectDebitCount	= $aFileData['iInvoiceDirectDebitCount'];
			$fInvoiceDirectDebitValue	= $aFileData['fInvoiceDirectDebitValue'];
			$iPromiseDirectDebitCount	= $aFileData['iPromiseDirectDebitCount'];
			$fPromiseDirectDebitValue	= $aFileData['fPromiseDirectDebitValue'];
			$iAdHocPaymentCount			= $aFileData['iAdHocPaymentCount'];
			$fAdHocPaymentValue			= $aFileData['fAdHocPaymentValue'];

			// Add a row for this File
			$oTableBody->appendChild(
				$D->tr(
					$D->td(
						$D->div(array('class'=>'description'), Carrier_Module::getForId($aFileData['iCarrierModule'])->description),
						$D->div(array('class'=>'filename'.((isset($aFileData['sFileName'])) ? '' : ' -no-file')),
							(isset($aFileData['sFileName'])) ? $aFileData['sFileName'] : 'No file generated'
						)
					),
					$D->td($iInvoiceDirectDebitCount),
					$D->td('$'.number_format($fInvoiceDirectDebitValue, 2)),
					$D->td($iPromiseDirectDebitCount),
					$D->td('$'.number_format($fPromiseDirectDebitValue, 2)),
					$D->td($iAdHocPaymentCount),
					$D->td('$'.number_format($fAdHocPaymentValue, 2))
				)
			);

			// Add to totals
			$iTotalInvoiceDirectDebitCount	+= $iInvoiceDirectDebitCount;
			$fTotalInvoiceDirectDebitValue	+= $fInvoiceDirectDebitValue;
			$iTotalPromiseDirectDebitCount	+= $iPromiseDirectDebitCount;
			$fTotalPromiseDirectDebitValue	+= $fPromiseDirectDebitValue;
			$iTotalAdHocPaymentCount		+= $iAdHocPaymentCount;
			$fTotalAdHocPaymentValue		+= $fAdHocPaymentValue;
		}

		// Totals
		$oTotalInvoiceDirectDebitCount->appendChild($D->getDOMDocument()->createTextNode($iTotalInvoiceDirectDebitCount));
		$oTotalInvoiceDirectDebitValue->appendChild($D->getDOMDocument()->createTextNode('$'.number_format($fTotalInvoiceDirectDebitValue, 2)));
		$oTotalPromiseDirectDebitCount->appendChild($D->getDOMDocument()->createTextNode($iTotalPromiseDirectDebitCount));
		$oTotalPromiseDirectDebitValue->appendChild($D->getDOMDocument()->createTextNode('$'.number_format($fTotalPromiseDirectDebitValue, 2)));
		$oTotalAdHocPaymentCount->appendChild($D->getDOMDocument()->createTextNode($iTotalAdHocPaymentCount));
		$oTotalAdHocPaymentValue->appendChild($D->getDOMDocument()->createTextNode('$'.number_format($fTotalAdHocPaymentValue, 2)));

		// Apply Style
		DOM_Style::style($oDOMDocument, array(
			'//*'		=> '
				font-family	: "Helvetica Neue", Arial, sans-serif;
				color		: #111;
			',
			'//table'	=> '
				border			: 1px solid #333;
				border-collapse	: collapse;
			',
			'//thead/tr/*|//tfoot/tr/*'	=> '
				background-color	: #333;
				color				: #eee;
				border				: 0;
			',
			'//thead//*|//tfoot//*'	=> '
				color				: #eee;
			',
			'//td|//th'	=> '
				vertical-align	: top;
				padding			: 0.2em 0.5em;
			',
			'//td'	=> '
				text-align	: right;
			',
			'//tbody/tr/td[1]'	=> '
				text-align		: left;
				font-weight		: bold;
				padding-left	: 0.2em;
				padding-right	: 1em;
			',
			'//h1'	=> '
				font-size	: 1.2em;
			',
			'//thead/tr[1]/th[not(1)]'	=> '
				width	: 14em;
			',
			'//thead/tr[2]/th'	=> '
				width				: 7em;
				background-color	: #444;
			',
			'//tfoot/tr/*'	=> '
				font-weight	: bold;
			',
			'//tfoot/tr/th[1]'	=> '
				text-align		: left;
				padding-left	: 0.2em
			',
			'//thead/tr[1]/th[1]'	=> '
				vertical-align	: middle;
				padding-left	: 1em;
				padding-right	: 1em;
			',
			'//*[@class="signature"]/p[1]'	=> '
				margin-bottom	: 0;
			',
			'//*[@class="signature"]/p[2]'	=> '
				margin-top	: 0.2em;
				font-weight	: bold;
			',
			'//tbody//td[1]'	=> '
				font-size	: 0.8em;
			',
			"//tbody//td[1]/*[contains(normalize-space(concat(' ', @class, ' ')), 'filename')]"	=> '
				font-weight	: normal;
			',
			"//tbody//td[1]/*[contains(normalize-space(concat(' ', @class, ' ')), '-no-file')]"	=> '
				font-style	: oblique;
			',
			'//tbody//td'	=> '
				vertical-align	: top;
			'
		));

		return "<!DOCTYPE html>\n".$oDOMDocument->saveXML($oContent);
	}

	private static function _buildExportSummaryEmailTextContent($aData) {
		$aLines	= array();

		$aLines[]	= 'Payment Export File Report from '.date('d/m/Y H:i', $aData['iTimestamp']);
		$aLines[]	= '';

		$iTotalInvoiceDirectDebitCount	= 0;
		$fTotalInvoiceDirectDebitValue	= 0.0;
		$iTotalPromiseDirectDebitCount	= 0;
		$fTotalPromiseDirectDebitValue	= 0.0;
		$iTotalAdHocPaymentCount		= 0;
		$fTotalAdHocPaymentValue		= 0.0;

		// Customer Group Details
		foreach ($aData['aFiles'] as $aFileData) {
			// Data
			$iInvoiceDirectDebitCount	= $aFileData['iInvoiceDirectDebitCount'];
			$fInvoiceDirectDebitValue	= $aFileData['fInvoiceDirectDebitValue'];
			$iPromiseDirectDebitCount	= $aFileData['iPromiseDirectDebitCount'];
			$fPromiseDirectDebitValue	= $aFileData['fPromiseDirectDebitValue'];
			$iAdHocPaymentCount			= $aFileData['iAdHocPaymentCount'];
			$fAdHocPaymentValue			= $aFileData['fAdHocPaymentValue'];

			// Add a data set for this File
			$aLines[]	= "\t".Carrier_Module::getForId($aFileData['iCarrierModule'])->Description.': '.$aFileData['sFileName'];
			$aLines[]	= "\t\tInvoice Direct Debits #: ".$iInvoiceDirectDebitCount;
			$aLines[]	= "\t\tInvoice Direct Debits $: ".'$'.number_format($fInvoiceDirectDebitValue, 2);
			$aLines[]	= "\t\tPromise Direct Debits #: ".$iPromiseDirectDebitCount;
			$aLines[]	= "\t\tPromise Direct Debits $".'$'.number_format($fPromiseDirectDebitValue, 2);
			$aLines[]	= "\t\tAd Hoc Payments #: ".$iAdHocPaymentCount;
			$aLines[]	= "\t\tAd Hoc Payments $".'$'.number_format($fAdHocPaymentValue, 2);

			// Add to totals
			$iTotalInvoiceDirectDebitCount	+= $iInvoiceDirectDebitCount;
			$fTotalInvoiceDirectDebitValue	+= $fInvoiceDirectDebitValue;
			$iTotalPromiseDirectDebitCount	+= $iPromiseDirectDebitCount;
			$fTotalPromiseDirectDebitValue	+= $fPromiseDirectDebitValue;
			$iTotalAdHocPaymentCount		+= $iAdHocPaymentCount;
			$fTotalAdHocPaymentValue		+= $fAdHocPaymentValue;
		}
		$aLines[]	= '';

		// Totals
		$aLines[]	= 'Total Invoice Direct Debits #: '.$iTotalInvoiceDirectDebitCount;
		$aLines[]	= 'Total Invoice Direct Debits $: '.number_format($iTotalInvoiceDirectDebitCount, 2);
		$aLines[]	= 'Total Promise Direct Debits #: '.$iTotalInvoiceDirectDebitCount;
		$aLines[]	= 'Total Promise Direct Debits $: '.number_format($iTotalInvoiceDirectDebitCount, 2);
		$aLines[]	= 'Total Ad Hoc Payments #: '.$iTotalAdHocPaymentCount;
		$aLines[]	= 'Total Ad Hoc Payments $: '.number_format($fTotalAdHocPaymentValue, 2);

		// Signature
		$aLines[]	= '';
		$aLines[]	= 'Regards';
		$aLines[]	= 'Flexor';

		return implode("\n", $aLines);
	}
	
	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType, self::CARRIER_MODULE_TYPE);
	}
}
?>