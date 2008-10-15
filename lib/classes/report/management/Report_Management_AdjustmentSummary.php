<?php
//----------------------------------------------------------------------------//
// Report_Management_AdjustmentSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_AdjustmentSummary
 *
 * Adjustment Summary Management Report
 *
 * @class	Report_Management_AdjustmentSummary
 */
class Report_Management_AdjustmentSummary extends Report_Management
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
		$arrCols = Array();
		$arrCols['CreditTotal']			= "SUM(CASE WHEN Nature = 'CR' THEN Amount ELSE 0 END)";
		$arrCols['DebitTotal']			= "SUM(CASE WHEN Nature = 'DR' THEN Amount ELSE 0 END)";
		$arrCols['CreditCount']			= "COUNT(CASE WHEN Nature = 'CR' THEN Id ELSE NULL END)";
		$arrCols['DebitCount']			= "COUNT(CASE WHEN Nature = 'DR' THEN Id ELSE NULL END)";
		$selAdjustmentSummary	= new StatementSelect("Charge", $arrCols, "invoice_run_id = <invoice_run_id>");
		
		$arrCols = Array();
		$arrCols['Nature']				= "Charge.Nature";
		$arrCols['Description']			= "Charge.Description";
		$arrCols['TotalAdjustments']	= "COUNT(Charge.Id)";
		$arrCols['MinCharge']			= "MIN(Charge.Amount)";
		$arrCols['MaxCharge']			= "MAX(Charge.Amount)";
		$arrCols['TotalCharge']			= "SUM(Charge.Amount)";
		$selTypeSummaries	= new StatementSelect("Charge LEFT JOIN Employee ON Employee.Id = Charge.CreatedBy", $arrCols, "Charge.invoice_run_id = <invoice_run_id>", "Charge.Description", NULL, "Charge.Description");

		
		// Create Workbook
		$strFilename = $strReportBasePath."Adjustment_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = self::_initExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Adjustment Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Adjustment Summary for {$strCustomerName}");
		$wksWorksheet->writeString(0, 2, $strPageTitle, $arrFormat['PageTitle']);
		
		$wksWorksheet->writeString(3, 0, "Bill Date"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(4, 0, "Billing Period"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(5, 0, "Invoice Run"		, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(3, 5, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(4, 5, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(5, 5, "N/A"				, $arrFormat['Percentage']);
		
		for ($i = 0; $i <= 5; $i++)
		{
			$wksWorksheet->writeBlank(2, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(7, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(6, $i, $arrFormat['Spacer']);
		}
		
		$wksWorksheet->writeString(2, 3, "This Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 4, "Last Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 5, "% Change"			, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(7, 0, "Adjustment Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString(7, 3, "This Month"			, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 4, "Last Month"			, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 5, "% Change"				, $arrFormat['TitleItalic']);
		
		// Set Column Widths
		$fltExcelWidthRatio = 5;
		$wksWorksheet->setColumn(0, 0, 9 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(1, 1, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(2, 2, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(3, 3, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(4, 4, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(5, 5, 3 * $fltExcelWidthRatio);
		
		// Create Adjustments Summary		
		$wksWorksheet->writeString(8, 0, "Total Credits"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(9, 0, "Total Debits"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString(10, 0, "Credit Total Value"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(11, 0, "Debit Total Value"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(12, 0, "Adjustment Total Value"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(13, 0, "Mean Credit Value"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(14, 0, "Mean Debit Value"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(15, 0, "Mean Customer Total"		, $arrFormat['TextBold']);
		
		$intCol = 3;
		foreach ($arrProfitData as $strPeriod=>$arrData)
		{
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['invoice_run_id']);
	
			$selAdjustmentSummary->Execute($arrData);
			$arrAdjustmentSummary = $selAdjustmentSummary->Fetch();
			$arrAdjustmentSummary['AdjustmentTotalValue']	= $arrAdjustmentSummary['DebitTotal'] - $arrAdjustmentSummary['CreditTotal'];
			$arrAdjustmentSummary['MeanCreditValue']		= ($arrAdjustmentSummary['CreditCount']) ? $arrAdjustmentSummary['CreditTotal'] / $arrAdjustmentSummary['CreditCount'] : 'N/A';
			$arrAdjustmentSummary['MeanDebitValue']			= ($arrAdjustmentSummary['DebitCount']) ? $arrAdjustmentSummary['DebitTotal'] / $arrAdjustmentSummary['DebitCount'] : 'N/A';
			$arrAdjustmentSummary['MeanCustomerTotal']		= $arrAdjustmentSummary['AdjustmentTotalValue'] / $arrData['InvoiceCount'];
			
			$wksWorksheet->writeNumber(8, $intCol, $arrAdjustmentSummary['CreditCount']				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(9, $intCol, $arrAdjustmentSummary['DebitCount']				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(10, $intCol, $arrAdjustmentSummary['CreditTotal']			, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(11, $intCol, $arrAdjustmentSummary['DebitTotal']				, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(12, $intCol, $arrAdjustmentSummary['AdjustmentTotalValue']	, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(13, $intCol, $arrAdjustmentSummary['MeanCreditValue']		, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(14, $intCol, $arrAdjustmentSummary['MeanDebitValue']			, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(15, $intCol, $arrAdjustmentSummary['MeanCustomerTotal']		, $arrFormat['Currency']);
			
			$intCol++;
		}
		
		// Write Adjustment Summary '% Change' Fields
		for ($i = 9; $i <= 16; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 5, "=IF(AND(D$i <> 0, NOT(D$i = \"N/A\")), (D$i - E$i) / ABS(D$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		// Create Credit/Debit Summaries
		$selTypeSummaries->Execute($arrProfitData['ThisMonth']);
		$arrTypes = Array();
		while ($arrType = $selTypeSummaries->Fetch())
		{
			$arrType['Description'] = preg_replace("/\ \d{2}\/\d{2}\/\d{4}\ to\ \d{2}\/\d{2}\/\d{4}$/", '', $arrType['Description']);
			
			$arrTypes[$arrType['Description']]['Description']		= $arrType['Description'];
			$arrTypes[$arrType['Description']]['Nature']			= $arrType['Nature'];
			$arrTypes[$arrType['Description']]['MaxCharge']			= max($arrTypes[$arrType['Description']]['MaxCharge'], $arrType['MaxCharge']);
			$arrTypes[$arrType['Description']]['TotalCharge']		+= $arrType['TotalCharge'];
			$arrTypes[$arrType['Description']]['TotalAdjustments']	+= $arrType['TotalAdjustments'];
			
			if (isset($arrTypes[$arrType['Description']]['MinCharge']))
			{
				$arrTypes[$arrType['Description']]['MinCharge']		= min($arrTypes[$arrType['Description']]['MinCharge'], $arrType['MinCharge']);
			}
			else
			{
				$arrTypes[$arrType['Description']]['MinCharge']		= $arrType['MinCharge'];
			}
		}
		
		$arrAdjustments = Array();
		foreach ($arrTypes as $strDescription=>$arrType)
		{
			@$arrType['MeanCharge']	= $arrType['TotalCharge'] / $arrType['TotalAdjustments'];
			$arrAdjustments[$arrType['Nature']][]	= $arrType;
		}
		
		// Render
		$intRow = 15;
		foreach ($arrAdjustments as $strNature=>$arrTypes)
		{
			switch ($strNature)
			{
				case 'DR':
					$strNoun	= "Debit";
					break;
					
				case 'CR':
					$strNoun	= "Credit";
					break;
				
				default:
					$strNoun	= "DONKEY";
			}
			
			// Add Header to Workseet
			$intRow++;
			for ($i = 0; $i <= 5; $i++)
			{
				$wksWorksheet->writeBlank($intRow, $i, $arrFormat['Spacer']);
				$wksWorksheet->writeBlank($intRow+1, $i, $arrFormat['BlankUnderline']);
			}
			
			$intRow++;
			$wksWorksheet->writeString($intRow, 0, "$strNoun Summary"	, $arrFormat['Title']);
			$intRow++;
			$wksWorksheet->writeString($intRow, 0, "Description"		, $arrFormat['ColTitle']);
			$wksWorksheet->writeString($intRow, 1, "Total {$strNoun}s"	, $arrFormat['ColTitle']);
			$wksWorksheet->writeString($intRow, 2, "Smallest $strNoun"	, $arrFormat['ColTitle']);
			$wksWorksheet->writeString($intRow, 3, "Largest $strNoun"	, $arrFormat['ColTitle']);
			$wksWorksheet->writeString($intRow, 4, "Mean $strNoun"		, $arrFormat['ColTitle']);
			$wksWorksheet->writeString($intRow, 5, "Total {$strNoun}ed"	, $arrFormat['ColTitle']);
			
			foreach ($arrTypes as $arrType)
			{
				// Add to Worksheet
				$intRow++;
				$wksWorksheet->writeString($intRow, 0, $arrType['Description']);
				$wksWorksheet->writeNumber($intRow, 1, $arrType['TotalAdjustments']	, $arrFormat['Integer']);
				$wksWorksheet->writeNumber($intRow, 2, $arrType['MinCharge']		, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intRow, 3, $arrType['MaxCharge']		, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intRow, 4, $arrType['MeanCharge']		, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intRow, 5, $arrType['TotalCharge']		, $arrFormat['Currency']);
			}
		}
		
		// Close Workbook
		for ($i = 0; $i <= 5; $i++)
		{
			$wksWorksheet->writeBlank($intRow+1, $i, $arrFormat['BlankOverline']);
		}
		$wkbWorkbook->close();
		chmod($strFilename, 0777);
		
		return Array($strFilename);
	}
}
?>