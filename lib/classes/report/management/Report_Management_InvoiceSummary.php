<?php
//----------------------------------------------------------------------------//
// Report_Management_InvoiceSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_InvoiceSummary
 *
 * Invoice Summary Management Report
 *
 * @class	Report_Management_InvoiceSummary
 */
class Report_Management_InvoiceSummary extends Report_Management
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
		$selLastInvoiceTotal	= new StatementSelect("InvoiceRun", "BillInvoiced+BillTax AS GrandTotal", "Id = <last_invoice_run_id>");
		
		// Create Workbook
		$strFilename = $strReportBasePath."Invoice_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = self::_initExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Invoice Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Invoice Summary for {$strCustomerName}");
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
			$wksWorksheet->writeBlank(15, $i, $arrFormat['Spacer']);
			$wksWorksheet->writeBlank(16, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(24, $i, $arrFormat['Spacer']);
			$wksWorksheet->writeBlank(25, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(30, $i, $arrFormat['BlankOverline']);
		}
		
		$wksWorksheet->writeString(2, 2, "This Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 3, "Last Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 4, "% Change"		, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(7, 0, "Invoice Delivery Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString(7, 2, "This Month"				, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 3, "Last Month"				, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 4, "% Change"				, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(8, 0, "Total Invoices Generated"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(9, 0, "Total Invoices Posted"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(10, 0, "Total Invoices Emailed"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(11, 0, "Total Invoices Withheld"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(12, 0, "Posted Invoices Retail Value"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(13, 0, "Emailed Invoices Retail Value"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(14, 0, "Withheld Invoices Retail Value"	, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(16, 0, "Invoice Profit Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString(16, 2, "This Month"				, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(16, 3, "Last Month"				, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(16, 4, "% Change"				, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(17, 0, "Total Cost"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString(18, 0, "Total Rated"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString(19, 0, "Total Invoiced (ex Tax)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(20, 0, "Total Taxed"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString(21, 0, "Total Invoiced (inc Tax)", $arrFormat['TextBold']);
		$wksWorksheet->writeString(22, 0, "Gross Profit (ex Tax)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(23, 0, "Profit Margin"			, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(25, 0, "Invoice Receivables Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString(25, 2, "This Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(25, 3, "Last Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(25, 4, "% Change"					, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(26, 0, "Total Outstanding"					, $arrFormat['TextBold']);
		$wksWorksheet->writeString(27, 0, "Total Outstanding (ex This Invoice)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(28, 0, "% Received of Previous Invoice"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(29, 0, "Total Payments Received"				, $arrFormat['TextBold']);
		
		$intCol = 2;
		foreach ($arrProfitData as $arrData)
		{
			$arrCols = Array();
			$arrCols['InvoiceCount']	= "COUNT(Id)";
			$arrCols['PostedCount']		= "COUNT(CASE WHEN DeliveryMethod = 0 THEN Id ELSE NULL END)";
			$arrCols['EmailedCount']	= "COUNT(CASE WHEN DeliveryMethod IN (1, 3) THEN Id ELSE NULL END)";
			$arrCols['WithheldCount']	= "COUNT(CASE WHEN DeliveryMethod = 2 THEN Id ELSE NULL END)";
			$arrCols['PostedTotal']		= "SUM(CASE WHEN DeliveryMethod = 0 THEN Total+Tax ELSE 0 END)";
			$arrCols['EmailedTotal']	= "SUM(CASE WHEN DeliveryMethod IN (1, 3) THEN Total+Tax ELSE 0 END)";
			$arrCols['WithheldTotal']	= "SUM(CASE WHEN DeliveryMethod = 2 THEN Total+Tax ELSE 0 END)";
			$selDeliveryBreakdown	= new StatementSelect("Invoice", $arrCols, "invoice_run_id = <invoice_run_id>");
			
			// Header
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['invoice_run_id']);
			
			// Delivery Summary
			$selDeliveryBreakdown->Execute($arrData);
			$arrDelivery = $selDeliveryBreakdown->Fetch();
			
			$wksWorksheet->writeNumber(8, $intCol, $arrDelivery['InvoiceCount']		, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(9, $intCol, $arrDelivery['PostedCount']		, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(10, $intCol, $arrDelivery['EmailedCount']	, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(11, $intCol, $arrDelivery['WithheldCount']	, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(12, $intCol, $arrDelivery['PostedTotal']		, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(13, $intCol, $arrDelivery['EmailedTotal']	, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(14, $intCol, $arrDelivery['WithheldTotal']	, $arrFormat['Currency']);
			
			// Profit Summary
			$fltProfit = $arrData['BillInvoiced'] - $arrData['BillCost'];
			$wksWorksheet->writeNumber(17, $intCol, $arrData['BillCost']	, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(18, $intCol, $arrData['BillRated']	, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(19, $intCol, $arrData['BillInvoiced'], $arrFormat['Currency']);
			$wksWorksheet->writeNumber(20, $intCol, $arrData['BillTax']		, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(21, $intCol, $arrData['BillInvoiced']+$arrData['BillTax']		, $arrFormat['Currency']);
			$wksWorksheet->writeNumber(22, $intCol, $fltProfit				, $arrFormat['Currency']);
			
			if ($intCol == 2)
			{
				$wksWorksheet->writeFormula(23	, $intCol, "=IF(AND(C20 <> 0, NOT(C20 = \"N/A\")), (C20 - C18) / ABS(C20), \"N/A\")"			, $arrFormat['Percentage']);
			}
			else
			{
				$wksWorksheet->writeFormula(23	, $intCol, "=IF(AND(D20 <> 0, NOT(D20 = \"N/A\")), (D20 - D18) / ABS(D20), \"N/A\")"			, $arrFormat['Percentage']);
			}
			
			// Receivables Summary
			if (!$arrBalanceData = unserialize($arrData['BalanceData']))
			{
				// Don't have the required back-data
				$arrBalanceData = Array();
				$fltTotalOutstanding			= "N/A";
				$fltTotalOutstandingExInvoice	= "N/A";
				$fltReceived					= "N/A";
			}
			else
			{
				// Have Back-data
				$selLastInvoiceTotal->Execute($arrData);
				$arrLastInvoiceTotal = $selLastInvoiceTotal->Fetch();
				$fltTotalOutstanding			= $arrBalanceData['TotalBalance'] + $arrBalanceData['TotalOutstanding'];
				$fltTotalOutstandingExInvoice	= $arrBalanceData['TotalOutstanding'];
				$fltReceived					= ($arrLastInvoiceTotal['GrandTotal'] - $arrBalanceData['PreviousBalance']) / $arrLastInvoiceTotal['GrandTotal'];
			}
			
			$wksWorksheet->write(26, $intCol, $fltTotalOutstanding			, $arrFormat['Currency']);
			$wksWorksheet->write(27, $intCol, $fltTotalOutstandingExInvoice	, $arrFormat['Currency']);
			$wksWorksheet->write(28, $intCol, $fltReceived					, $arrFormat['Percentage']);
			
			$intBillingDate			= strtotime($arrData['BillingDate']);
			$strPaymentPeriodEnd	= date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-01", $intBillingDate))));
			$strPaymentPeriodStart	= date("Y-m-d", strtotime("-1 month", strtotime(date("Y-m-01", $intBillingDate))));
			$selPaymentsReceived	= new StatementSelect("Payment", "SUM(Amount) AS Total", "Status IN (101, 103, 150) AND PaidOn BETWEEN '$strPaymentPeriodStart' AND '$strPaymentPeriodEnd'");
			if ($selPaymentsReceived->Execute() === FALSE)
			{
				Debug($selPaymentsReceived->Error());
			}
			else
			{
				$arrPaymentsReceived	= $selPaymentsReceived->Fetch();
			}
			
			$wksWorksheet->write(29, $intCol, (float)$arrPaymentsReceived['Total']	, $arrFormat['Currency']);
			
			$intCol++;
		}
		
		// Write '% Change' Fields
		for ($i = 9; $i <= 15; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		for ($i = 18; $i <= 24; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		for ($i = 27; $i <= 30; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		// Set Column Widths
		$fltExcelWidthRatio = 5;
		$wksWorksheet->setColumn(0, 0, 6.5 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(1, 1, 10 * $fltExcelWidthRatio);
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