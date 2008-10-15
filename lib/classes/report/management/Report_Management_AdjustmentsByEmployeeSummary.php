<?php
//----------------------------------------------------------------------------//
// Report_Management_AdjustmentsByEmployeeSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_AdjustmentsByEmployeeSummary
 *
 * Adjustments By Employee Summary Management Report
 *
 * @class	Report_Management_AdjustmentsByEmployeeSummary
 */
class Report_Management_AdjustmentsByEmployeeSummary extends Report_Management
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
		$arrCols['Employee']	= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
		$arrCols['Description']	= "Charge.Description";
		$arrCols['Instances']	= "COUNT(Charge.Id)";
		$arrCols['Min']			= "MIN(Charge.Amount)";
		$arrCols['Max']			= "MAX(Charge.Amount)";
		$arrCols['Mean']		= "AVG(Charge.Amount)";
		$arrCols['TotalCharge']	= "SUM(Charge.Amount)";
		$arrCols['Nature']		= "Charge.Nature";
		$selAdjustments	= new StatementSelect("Charge JOIN Employee ON Charge.CreatedBy = Employee.Id", $arrCols, "invoice_run_id = <invoice_run_id>", "Charge.Nature", NULL, "Charge.CreatedBy, Charge.Description");
		
		// Create Workbook
		$strFilename = $strReportBasePath."Adjustments_by_Employee_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = self::_initExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Adjustments by Employee Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Adjustment by Employee Summary for {$strCustomerName}");
		$wksWorksheet->writeString(0, 2, $strPageTitle, $arrFormat['PageTitle']);
		
		$wksWorksheet->writeString(3, 0, "Bill Date"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(4, 0, "Billing Period"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(5, 0, "Invoice Run"		, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(3, 6, date("d/m/Y", strtotime($arrProfitData['ThisMonth']['BillingDate'])));
		$wksWorksheet->writeString(4, 6, date("F Y", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate']))));
		$wksWorksheet->writeString(5, 6, $arrProfitData['ThisMonth']['invoice_run_id']);
		
		for ($i = 0; $i <= 6; $i++)
		{
			$wksWorksheet->writeBlank(2, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(6, $i, $arrFormat['Spacer']);
		}
		
		// Breakdown	
		$selAdjustments->Execute($arrProfitData['ThisMonth']);
		$arrAdjustments = $selAdjustments->FetchAll();
		
		$arrEmployees = Array();
		foreach ($arrAdjustments as $arrAdjustment)
		{
			$arrEmployees[$arrAdjustment['Employee']][$arrAdjustment['Nature']][$arrAdjustment['Description']]	= $arrAdjustment;
		}
		
		$intRow = 6;
		ksort($arrEmployees, SORT_STRING);
		foreach ($arrEmployees as $strEmployee=>$arrEmployee)
		{
			for ($i = 0; $i <= 6; $i++)
			{
				$wksWorksheet->writeBlank($intRow, $i, $arrFormat['BlankOverline']);
				//$wksWorksheet->writeBlank($intRow+1, $i, $arrFormat['BlankUnderline']);
				$wksWorksheet->writeBlank($intRow+2, $i, $arrFormat['BlankUnderline']);
			}
			
			$intRow	+= 2;
			$wksWorksheet->writeString($intRow, 0, $strEmployee			, $arrFormat['Title']);
			$intRow++;
			
			$fmtTitleFormat = $arrFormat['ColTitle'];
			foreach ($arrEmployee as $strNature=>$arrSummaries)
			{		
				if ($strNature == 'CR')
				{
					$strNoun = "Credit";
				}
				else
				{
					$strNoun = "Debit";
				}
				
				// Title Row
				$wksWorksheet->writeString($intRow, 0, "{$strNoun}s"			, $fmtTitleFormat);
				$wksWorksheet->writeString($intRow, 1, "Description"			, $fmtTitleFormat);
				$wksWorksheet->writeString($intRow, 2, "Instances"				, $fmtTitleFormat);
				$wksWorksheet->writeString($intRow, 3, "Smallest $strNoun"		, $fmtTitleFormat);
				$wksWorksheet->writeString($intRow, 4, "Largest $strNoun"		, $fmtTitleFormat);
				$wksWorksheet->writeString($intRow, 5, "Average $strNoun"		, $fmtTitleFormat);
				$wksWorksheet->writeString($intRow, 6, "Total {$strNoun}ed"		, $fmtTitleFormat);
				
				$intRow++;
				foreach ($arrSummaries as $strDescription=>$arrSummary)
				{
					if ($strNature == 'CR')
					{
						$arrSummary['Min']			= 0 - $arrSummary['Min'];
						$arrSummary['Max']			= 0 - $arrSummary['Max'];
						$arrSummary['Mean']			= 0 - $arrSummary['Mean'];
						$arrSummary['TotalCharge']	= 0 - $arrSummary['TotalCharge'];
					}
					
					$wksWorksheet->writeBlank($intRow, 0, $arrFormat['LeftSpacer']);
					$wksWorksheet->writeString($intRow, 1, $strDescription);
					$wksWorksheet->writeNumber($intRow, 2, $arrSummary['Instances']		, $arrFormat['Integer']);
					$wksWorksheet->writeNumber($intRow, 3, $arrSummary['Min']			, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 4, $arrSummary['Max']			, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 5, $arrSummary['Mean']			, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 6, $arrSummary['TotalCharge']	, $arrFormat['CreditDebit']);
					
					$intRow++;
				}
				
				// Switch to alternate title
				$fmtTitleFormat = $arrFormat['AltColTitle'];
			}
		}
		
		// Bottom Line
		for ($i = 0; $i <= 6; $i++)
		{
			$wksWorksheet->writeBlank($intRow, $i, $arrFormat['BlankOverline']);
		}
		
		// Set Column Widths
		$fltExcelWidthRatio = 5;
		$wksWorksheet->setColumn(0, 0, 4 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(1, 1, 9 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(2, 2, 2 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(3, 3, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(4, 4, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(5, 5, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(6, 6, 3 * $fltExcelWidthRatio);
		
		// Close Workbook
		$wkbWorkbook->close();
		chmod($strFilename, 0777);
		
		return Array($strFilename);
 	}
}
?>