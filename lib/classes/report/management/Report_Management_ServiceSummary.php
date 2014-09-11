<?php
//----------------------------------------------------------------------------//
// Report_Management_ServiceSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_ServiceSummary
 *
 * Service Summary Management Report
 *
 * @class	Report_Management_ServiceSummary
 */
class Report_Management_ServiceSummary extends Report_Management
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
		$selServicesClosed		= new StatementSelect(	"Service",
														"Id",
														"Id NOT IN (SELECT Service FROM ServiceTotal WHERE invoice_run_id = <invoice_run_id>)");
														
		$selServicesOpen		= new StatementSelect(	"ServiceTotal",
														"Id",
														"invoice_run_id = <invoice_run_id>");
		
		$selServicesActive		= new StatementSelect(	"ServiceTotal",
														"Id",
														"(ServiceTotal.Debit > 0 OR ServiceTotal.UncappedCharge > 0 OR ServiceTotal.CappedCharge > 0) AND ServiceTotal.Debit IS NOT NULL AND ServiceTotal.invoice_run_id = <invoice_run_id>");

		$selServicesByType		= new StatementSelect("Service LEFT JOIN ServiceTotal ON Service.Id = ServiceTotal.Service", "Service.ServiceType AS ServiceType, COUNT(Service.Id) AS ServiceCount", "ServiceTotal.invoice_run_id = <invoice_run_id>", "Service.ServiceType", NULL, "Service.ServiceType");
		
		$selServicesLost	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.invoice_run_id = <last_invoice_run_id> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.invoice_run_id = <invoice_run_id>)");
		$selServicesGained	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.invoice_run_id = <invoice_run_id> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.invoice_run_id = <last_invoice_run_id>)");
		
		// Create Workbook
		$strFilename = $strReportBasePath."Service_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = self::_initExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Service Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Service Summary for {$strCustomerName}");
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
			$wksWorksheet->writeBlank(13, $i, $arrFormat['Spacer']);
			$wksWorksheet->writeBlank(14, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(19, $i, $arrFormat['BlankOverline']);
		}
		
		$wksWorksheet->writeString(2, 2, "This Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 3, "Last Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 4, "% Change"						, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(7, 0, "Service Status Summary"		, $arrFormat['Title']);
		$wksWorksheet->writeString(7, 2, "This Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 3, "Last Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 4, "% Change"						, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(8, 0, "Currently Tolling Services"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(9, 0, "Currently Open Services"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(10, 0, "Currently Archived Services"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(11, 0, "Services Lost"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString(12, 0, "Services Gained"				, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(14, 0, "Service Type Summary"		, $arrFormat['Title']);
		$wksWorksheet->writeString(14, 2, "This Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(14, 3, "Last Month"					, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(14, 4, "% Change"					, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(15, 0, "Current Land Line Services"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(16, 0, "Current Mobile Services"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(17, 0, "Current Inbound Services"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(18, 0, "Current ADSL Services"		, $arrFormat['TextBold']);
		
		$intCol = 2;
		foreach ($arrProfitData as $arrData)
		{
			// Header Data
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['invoice_run_id']);
			
			// Service Status Summary
			$intServicesLost	= $selServicesLost->Execute($arrData);
			$intServicesGained	= $selServicesGained->Execute($arrData);
			$intServicesOpen	= $selServicesOpen->Execute($arrData);
			$intServicesClosed	= $selServicesClosed->Execute($arrData);
			$intServicesActive	= $selServicesActive->Execute($arrData);
			$wksWorksheet->writeNumber(8, $intCol, $intServicesActive	, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(9, $intCol, $intServicesOpen		, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(10, $intCol, $intServicesClosed	, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(11, $intCol, $intServicesLost	, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(12, $intCol, $intServicesGained	, $arrFormat['Integer']);
			
			// Service Type Summary
			$selServicesByType->Execute($arrData);
			while ($arrServiceType = $selServicesByType->Fetch())
			{
				switch ($arrServiceType['ServiceType'])
				{
					case SERVICE_TYPE_LAND_LINE:
						$wksWorksheet->writeNumber(15, $intCol, $arrServiceType['ServiceCount']	, $arrFormat['Integer']);
						break;
						
					case SERVICE_TYPE_MOBILE:
						$wksWorksheet->writeNumber(16, $intCol, $arrServiceType['ServiceCount']	, $arrFormat['Integer']);
						break;
						
					case SERVICE_TYPE_INBOUND:
						$wksWorksheet->writeNumber(17, $intCol, $arrServiceType['ServiceCount']	, $arrFormat['Integer']);
						break;
					
					case SERVICE_TYPE_ADSL:
						$wksWorksheet->writeNumber(18, $intCol, $arrServiceType['ServiceCount']	, $arrFormat['Integer']);
				}
			}
			
			$intCol++;
		}
		
		// Write '% Change' Fields
		for ($i = 9; $i <= 13; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		for ($i = 16; $i <= 19; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		// Set Column Widths
		$fltExcelWidthRatio = 5;
		$wksWorksheet->setColumn(0, 0, 5.5 * $fltExcelWidthRatio);
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