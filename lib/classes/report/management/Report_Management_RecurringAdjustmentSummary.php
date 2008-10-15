<?php
//----------------------------------------------------------------------------//
// Report_Management_RecurringAdjustmentSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_RecurringAdjustmentSummary
 *
 * Recurring Adjustment Summary Management Report
 *
 * @class	Report_Management_RecurringAdjustmentSummary
 */
class Report_Management_RecurringAdjustmentSummary extends Report_Management
{
	//------------------------------------------------------------------------//
	// run
	//------------------------------------------------------------------------//
	/**
	 * run()
	 *
	 * Runs the Management Report
	 *
	 * @param	array	$arrProfitData				The Profit Data for this Invoice Run
	 * @param	string	$strReportBasePath			Directory to output the XLS file to
	 * @param	string	$strCustomerName			Customer Name
	 *
	 * @return	void
	 *
	 * @constructor
	 */
 	public static function run($arrProfitData, $strReportBasePath, $strCustomerName)
 	{
		$selActiveCharges	= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "(RecurringCharge.LastChargedOn > SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND RecurringCharge.LastChargedOn <= <BillingDate>) OR (RecurringCharge.MinCharge > RecurringCharge.TotalCharged AND RecurringCharge.Archived = 0) AND Account.Archived = 0");
		$selTotalCharged	= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "SUM(RecurringCharge.RecursionCharge) AS Total", "(LastChargedOn > SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND LastChargedOn <= <BillingDate>)");
		$selChargeFinished	= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "(LastChargedOn > SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND LastChargedOn <= <BillingDate>) AND TotalCharged >= MinCharge");
		$selChargeCancelled	= new StatementSelect("(RecurringCharge JOIN Charge USING (Account, ChargeType)) JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "Charge.Service <=> RecurringCharge.Service AND Charge.Description = CONCAT('CANCELLATION: ', RecurringCharge.Description) AND invoice_run_id = <invoice_run_id>");
		//$selNewCharges		= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "(RecurringCharge.CreatedOn BETWEEN SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND <BillingDate>) AND 1 = (SELECT COUNT(Id) FROM Charge WHERE Charge.Account = RecurringCharge.Account AND Charge.Service <=> RecurringCharge.Service AND Charge.ChargeType = RecurringCharge.ChargeType)");
		$selNewCharges		= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "(RecurringCharge.CreatedOn BETWEEN SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND <BillingDate>)");
		
		$arrCols = Array();
		$arrCols['Account']			= "RecurringCharge.Account";
		$arrCols['Service']			= "Service.FNN";
		$arrCols['Type']			= "RecurringCharge.Description";
		$arrCols['Nature']			= "CASE WHEN RecurringCharge.Nature = 'CR' THEN 'Credit' ELSE 'Debit' END";
		$arrCols['FreqType']		= "RecurringCharge.RecurringFreqType";
		$arrCols['Freq']			= "RecurringCharge.RecurringFreq";
		$arrCols['StartedOn']		= "RecurringCharge.StartedOn";
		$arrCols['Installment']		= "RecurringCharge.RecursionCharge";
		$arrCols['ChargedToDate']	= "RecurringCharge.TotalCharged";
		$arrCols['TotalValue']		= "RecurringCharge.MinCharge";
		$arrCols['LastChargedOn']	= "RecurringCharge.LastChargedOn";
		$selBreakdown	= new StatementSelect(	"(RecurringCharge LEFT JOIN Service ON Service.Id = RecurringCharge.Service) JOIN Account ON RecurringCharge.Account = Account.Id",
												$arrCols,
												"(RecurringCharge.LastChargedOn > SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND RecurringCharge.LastChargedOn <= <BillingDate>) OR (RecurringCharge.MinCharge > RecurringCharge.TotalCharged AND RecurringCharge.Archived = 0 AND Account.Archived = 0)",
												"RecurringCharge.ChargeType");
		
		// Create Workbook
		$strFilename = $strReportBasePath."Recurring_Adjustment_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = self::_initExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Recurring Adjustment Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Recurring Adjustment Summary for {$strCustomerName}");
		$wksWorksheet->writeString(0, 4, $strPageTitle, $arrFormat['PageTitle']);
		
		$wksWorksheet->writeString(3, 0, "Bill Date"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(4, 0, "Billing Period"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(5, 0, "Invoice Run"		, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(3, 10, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(4, 10, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(5, 10, "N/A"				, $arrFormat['Percentage']);
		
		for ($i = 0; $i <= 10; $i++)
		{
			$wksWorksheet->writeBlank(2, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(6, $i, $arrFormat['Spacer']);
			$wksWorksheet->writeBlank(7, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(13, $i, $arrFormat['Spacer']);
			$wksWorksheet->writeBlank(14, $i, $arrFormat['BlankUnderline']);
		}
		
		$wksWorksheet->writeString(2, 8, "This Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 9, "Last Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 10, "% Change"			, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(7, 0, "Recurring Adjustment Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString(7, 8, "This Month"			, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 9, "Last Month"			, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 10, "% Change"				, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(8, 0, "Total Active Adjustments"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(9, 0, "Total New Adjustments"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(10, 0, "Total Charged"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(11, 0, "Total Finished"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(12, 0, "Total Cancelled"			, $arrFormat['TextBold']);
		
		for ($i = 9; $i <= 13; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 10, "=IF(AND(I$i <> 0, NOT(I$i = \"N/A\")), (I$i - J$i) / ABS(I$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		$wksWorksheet->writeString(14, 0, "Recurring Adjustment Breakdown"	, $arrFormat['Title']);
		$wksWorksheet->writeString(15, 0, "Account"				, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 1, "Service"				, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 2, "Type"				, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 3, "Nature"				, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 4, "Start Date"			, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 5, "End Date"			, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 6, "Frequency"			, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 7, "Installment"			, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 8, "Charged to Date"		, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 9, "Total Value"			, $arrFormat['ColTitle']);
		$wksWorksheet->writeString(15, 10, "Last Charged On"	, $arrFormat['ColTitle']);
	
		// Create Adjustments Summary		
		$intCol = 8;
		foreach ($arrProfitData as $arrData)
		{
			// Header Data
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['invoice_run_id']);
			
			$intActiveCharge	= $selActiveCharges->Execute($arrData);
			$intChargeFinished	= $selChargeFinished->Execute($arrData);
			$intChargeCancelled	= $selChargeCancelled->Execute($arrData);
			$intNewCharges		= $selNewCharges->Execute($arrData);
			$selTotalCharged->Execute($arrData);
			$arrTotalCharged	= $selTotalCharged->Fetch();
			
			// Recurring Adjustment Summary
			$wksWorksheet->writeNumber(8, $intCol, $intActiveCharge					, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(9, $intCol, $intNewCharges					, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(10, $intCol, $arrTotalCharged['Total']		, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(11, $intCol, $intChargeFinished				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(12, $intCol, $intChargeCancelled				, $arrFormat['Integer']);
			
			$intCol++;
		}
		
		// Breakdown
		$intRow = 16;
		$selBreakdown->Execute($arrProfitData['ThisMonth']);
		while ($arrBreakdown = $selBreakdown->Fetch())
		{
			if ($arrBreakdown['Freq'] == 1)
			{
				$strS = '';
			}
			else
			{
				$strS = 's';
			}
			
			$fltTotalCharged	= $arrBreakdown['ChargedToDate'];
			$strEndDate			= $arrBreakdown['LastChargedOn'];
			switch ($arrBreakdown['FreqType'])
			{
				case BILLING_FREQ_DAY:
					$strFrequency		= "{$arrBreakdown['Freq']} Day$strS";
					if ($arrBreakdown['Freq'])
					{
						while ($fltTotalCharged < $arrBreakdown['TotalValue'])
						{
							$fltTotalCharged	+= $arrBreakdown['Installment'];
							$strEndDate			= date("Y-m-d", strtotime("+".$arrBreakdown['Freq']." days", strtotime($strEndDate)));
						}
					}
					else
					{
						$strEndDate = "ERROR: Frequency = 0";
					}
					break;
				
				case BILLING_FREQ_MONTH:
					$strFrequency	= "{$arrBreakdown['Freq']} Month$strS";
					if ($arrBreakdown['Freq'])
					{
						while ($fltTotalCharged < $arrBreakdown['TotalValue'])
						{
							$fltTotalCharged	+= $arrBreakdown['Installment'];
							$strEndDate			= date("Y-m-d", strtotime("+".$arrBreakdown['Freq']." months", strtotime($strEndDate)));
				
							if ($strEndDate == '1970-01-01')
							{
								$strEndDate = "ERROR: Bad End Date";
								break;
							}
						}
					}
					else
					{
						$strEndDate = "ERROR: Frequency = 0";
					}
					break;
				
				case BILLING_FREQ_HALF_MONTH:
					$strFrequency	= "{$arrBreakdown['Freq']} Half-Month$strS";
					if ($arrBreakdown['Freq'])
					{
						while ($fltTotalCharged < $arrBreakdown['TotalValue'])
						{
							$fltTotalCharged	+= $arrBreakdown['Installment'];
							if ((int)date("d", strtotime($strEndDate)) < 15)
							{
								$strEndDate		= date("Y-m-d", strtotime("+14 days", strtotime($strEndDate)));
							}
							else
							{
								$strEndDate		= date("Y-m-d", strtotime("-14 days", strtotime($strEndDate)));
								$strEndDate		= date("Y-m-d", strtotime("+1 month", strtotime($strEndDate)));
							}
						}
					}
					else
					{
						$strEndDate = "ERROR: Frequency = 0";
					}
					break;
			}
			
			$wksWorksheet->writeNumber($intRow, 0, $arrBreakdown['Account']									, $arrFormat['Integer']);
			if ($arrBreakdown['Service'])
			{
				$wksWorksheet->writeNumber($intRow, 1, $arrBreakdown['Service']								, $arrFormat['FNN']);
			}
			$wksWorksheet->writeString($intRow, 2, $arrBreakdown['Type']);
			$wksWorksheet->writeString($intRow, 3, $arrBreakdown['Nature']);
			$wksWorksheet->writeString($intRow, 4, date("d/m/Y", strtotime($arrBreakdown['StartedOn'])));
			if (strtotime($strEndDate))
			{
				$wksWorksheet->writeString($intRow, 5, date("d/m/Y", strtotime($strEndDate)));
			}
			else
			{
				$wksWorksheet->writeString($intRow, 5, $strEndDate);
			}
			$wksWorksheet->writeString($intRow, 6, $strFrequency);
			$wksWorksheet->writeNumber($intRow, 7, $arrBreakdown['Installment']								, $arrFormat['Currency']);
			$wksWorksheet->writeNumber($intRow, 8, $arrBreakdown['ChargedToDate']							, $arrFormat['Currency']);
			$wksWorksheet->writeNumber($intRow, 9, $arrBreakdown['TotalValue']								, $arrFormat['Currency']);
			$wksWorksheet->writeString($intRow, 10, date("d/m/Y", strtotime($arrBreakdown['LastChargedOn'])));
			
			$intRow++;
		}
		
		for ($i = 0; $i <= 10; $i++)
		{
			$wksWorksheet->writeBlank($intRow, $i, $arrFormat['BlankOverline']);
		}
		
		// Set Column Widths
		$fltExcelWidthRatio = 5;
		$wksWorksheet->setColumn(0, 0, 4.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(1, 1, 2.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(2, 2, 9 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(3, 3, 1.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(4, 4, 2.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(5, 5, 2.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(6, 6, 2.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(7, 7, 2.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(8, 8, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(9, 9, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(10, 10, 3 * $fltExcelWidthRatio);
		
		// Close Workbook
		$wkbWorkbook->close();
		chmod($strFilename, 0777);
		
		return Array($strFilename);
	}
}
?>