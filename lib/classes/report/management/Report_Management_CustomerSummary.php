<?php
//----------------------------------------------------------------------------//
// Report_Management_CustomerSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_CustomerSummary
 *
 * Customer Summary Management Report
 *
 * @class	Report_Management_CustomerSummary
 */
class Report_Management_CustomerSummary extends Report_Management
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
		$selServiceCount	= new StatementSelect("ServiceTotal", "Account, COUNT(Id) AS ServiceCount", "invoice_run_id = <invoice_run_id>", "ServiceCount", NULL, "Account");
		$selInvoice 		= new StatementSelect("Invoice", "Id", "invoice_run_id = <invoice_run_id>");
		$selCustomersGained	= new StatementSelect("Account", "Id", "CreatedOn BETWEEN <LastBillingDate> AND <BillingDate> AND CustomerGroup = <customer_group_id>");
		
		// Create Workbook
		$strFilename = $strReportBasePath."Customer_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = self::_initExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Customer Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Customer Summary for {$strCustomerName}");
		$wksWorksheet->writeString(0, 1, $strPageTitle, $arrFormat['PageTitle']);
		
		$wksWorksheet->writeString(3, 0, "Bill Date"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(4, 0, "Billing Period"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(5, 0, "Invoice Run"		, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(3, 4, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(4, 4, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(5, 4, "N/A"				, $arrFormat['Percentage']);
		
		for ($i = 0; $i <= 4; $i++)
		{
			$wksWorksheet->writeBlank(2, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(6, $i, $arrFormat['Spacer']);
			$wksWorksheet->writeBlank(7, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(14, $i, $arrFormat['Spacer']);
			$wksWorksheet->writeBlank(15, $i, $arrFormat['BlankUnderline']);
		}
		
		$wksWorksheet->writeString(2, 2, "This Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 3, "Last Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 4, "% Change"						, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(7, 0, "Customer Status Summary"		, $arrFormat['Title']);
		$wksWorksheet->writeString(7, 2, "This Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 3, "Last Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 4, "% Change"						, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(8, 0, "Currently Active Accounts"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(9, 0, "Currently Archived Accounts"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(10, 0, "New Customers"						, $arrFormat['TextBold']);
		$wksWorksheet->writeString(11, 0, "Customers Archived"					, $arrFormat['TextBold']);
		$wksWorksheet->writeString(12, 0, "Mean Customer Spend (80 Percentile)"	, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(15, 0, "Customers with X Services Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString(15, 2, "This Month"							, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(15, 3, "Last Month"							, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(15, 4, "% of Total"							, $arrFormat['TitleItalic']);
		
		$intCol = 2;
		$arrServiceCount = Array();
		foreach ($arrProfitData as $arrData)
		{
			$selCustomersLost		= new StatementSelect("Invoice", "Invoice.Id", "Invoice.invoice_run_id = <last_invoice_run_id> AND Invoice.Account NOT IN (SELECT I2.Account FROM Invoice I2 WHERE I2.invoice_run_id = <invoice_run_id>)");
			$selCustomersOpened		= new StatementSelect("Invoice", "Invoice.Id", "Invoice.invoice_run_id = <invoice_run_id> AND Invoice.Account NOT IN (SELECT I2.Account FROM Invoice I2 WHERE I2.invoice_run_id = <last_invoice_run_id>)");
			$selTopBottom10			= new StatementSelect("Invoice", "ROUND(COUNT(Id) / 10) AS Bottom10, ROUND((COUNT(Id) / 100) * 90) AS Top10", "Total != 0 AND invoice_run_id = <invoice_run_id>");
			$selCustomersActive		= new StatementSelect("Invoice", "Id", "invoice_run_id = <invoice_run_id>");
			$selCustomersArchived	= new StatementSelect("Account", "Id", "Id NOT IN (SELECT Account FROM Invoice WHERE invoice_run_id = <invoice_run_id>) AND CreatedOn < <BillingDate>");
			
			// Header Data
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['invoice_run_id']);
			
			// Customer Summary Data
			$intCustomersLost		= $selCustomersLost->Execute($arrData);
			$intCustomersGained		= $selCustomersGained->Execute($arrData);
			$intCustomersReOpened	= $selCustomersOpened->Execute($arrData) - $intCustomersGained;
			$intActive				= $selCustomersActive->Execute($arrData);
			$intArchived			= $selCustomersArchived->Execute($arrData);
			$wksWorksheet->writeNumber(8, $intCol, $intActive							, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(9, $intCol, $intArchived							, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(10, $intCol, $intCustomersGained					, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(11, $intCol, $intCustomersLost					, $arrFormat['Integer']);
			
			// 80 Percentile
			$selTopBottom10->Execute($arrData);
			$arrTopBottom10		= $selTopBottom10->Fetch();
			$sel80Percentile	= new StatementSelect("Invoice", "Total + Tax AS GrandTotal", "Total != 0 AND invoice_run_id = <invoice_run_id>", "(Total + Tax)", "{$arrTopBottom10['Bottom10']}, {$arrTopBottom10['Top10']}");
			$sel80Percentile->Execute($arrData);
			$arrGrandTotals		= $sel80Percentile->FetchAll();
			
			$fltGrandGrandTotal = 0;
			foreach ($arrGrandTotals as $arrGrandTotal)
			{
				$fltGrandGrandTotal += $arrGrandTotal['GrandTotal'];
			}
			$flt80Percentile = (count($arrGrandTotals)) ? $fltGrandGrandTotal / count($arrGrandTotals) : 'N/A';
			
			$wksWorksheet->writeNumber(12, $intCol, $flt80Percentile					, $arrFormat['Currency']);
			
			
			
			// Service Count Data
			$intAccounts = $selServiceCount->Execute($arrData);
			$arrServices = Array();
			while ($arrCount = $selServiceCount->Fetch())
			{
				$arrServices[$intCol] += $arrCount['ServiceCount'];
				$arrServiceCount[$arrCount['ServiceCount']][$intCol]++;
			}
			
			$wksWorksheet->writeString(13, 0, "Average Services per Customer"		, $arrFormat['TextBold']);
			$wksWorksheet->writeNumber(13, $intCol, ($intAccounts) ? $arrServices[$intCol] / $intAccounts : 'N/A');
			
			$intCol++;
		}
		
		$intRow	= 16;
		$intS	= '';
		ksort($arrServiceCount, SORT_NUMERIC);
		foreach ($arrServiceCount as $intCount=>$arrCols)
		{
			$wksWorksheet->writeNumber($intRow, 2, 0, $arrFormat['Integer']);
			$wksWorksheet->writeNumber($intRow, 3, 0, $arrFormat['Integer']);
			
			if ($intCount > 1)
			{
				$intS = 's';
			}
			
			foreach ($arrCols as $intCol=>$intCustomers)
			{
				$wksWorksheet->writeString($intRow, 0, "Customers with $intCount Service$intS"	, $arrFormat['TextBold']);
				$wksWorksheet->writeNumber($intRow, $intCol, $intCustomers					, $arrFormat['Integer']);
			}
			$intRow++;
		}
		
		for ($i = 0; $i <= 4; $i++)
		{
			$wksWorksheet->writeBlank($intRow, $i, $arrFormat['BlankOverline']);
		}
		
		// Write '% Change' Fields
		for ($i = 9; $i <= 14; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		for ($i = 17; $i <= $intRow; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=C$i / SUM(C17:C$intRow)", $arrFormat['Percentage']);
		}
		
		// Set Column Widths
		$fltExcelWidthRatio = 5;
		$wksWorksheet->setColumn(0, 0, 8 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(1, 1, 11 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(2, 2, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(3, 3, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(4, 4, 3 * $fltExcelWidthRatio);
		
		// Close Workbook
		$wkbWorkbook->close();
		chmod($strFilename, 0777);
		
		return Array($strFilename);
	}
}
?>