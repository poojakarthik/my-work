<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_reports
//----------------------------------------------------------------------------//
/**
 * module_reports
 *
 * Module for Management Reports
 *
 * Module for Management Reports
 *
 * @file		module_reports.php
 * @language	PHP
 * @package		billing
 * @author		Rich 'Waste' Davis
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// BillingModuleReports
//----------------------------------------------------------------------------//
/**
 * BillingModuleReports
 *
 * Module for Management Reports
 *
 * Module for Management Reports
 *
 * @prefix		bil
 *
 * @package		billing
 * @class		BillingModuleReports
 */
class BillingModuleReports
{
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for BillingModuleReports
	 *
	 * Constructor method for BillingModuleReports
	 * 
	 * @param	array		$arrProfitData			Data from CalculateProfitData()
	 *
	 * @return		BillingModuleReports
	 *
	 * @method
	 */
 	function __construct($arrProfitData)
 	{
 		if (!$arrProfitData)
 		{
 			// No Profit Data
 			return FALSE;
 		}
 		
 		$this->_arrProfitData = $arrProfitData;
 		
 		
 		// Common Statements
 		$this->_selIsTempInvoice	= new StatementSelect("InvoiceTemp", "Id", "InvoiceRun = <InvoiceRun>");
 	}
 	
 	
	//------------------------------------------------------------------------//
	// CreateReport()
	//------------------------------------------------------------------------//
	/**
	 * CreateReport()
	 *
	 * Create a Management Report and return the path to the file
	 *
	 * Create a Management Report and return the path to the file
	 * 
	 * @param	string		$strReportName			Name of the Report to run
	 *
	 * @return	mixed								Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
 	function CreateReport($strReportName)
 	{
 		if (method_exists($this, "_Report$strReportName"))
 		{
 			return call_user_method("_Report$strReportName", $this);
 		}
 		else
 		{
 			// Bad Report Name
 			return FALSE;
 		}
 	}
 	
 	
	//------------------------------------------------------------------------//
	// InitExcelFormats
	//------------------------------------------------------------------------//
	/**
	 * InitExcelFormats()
	 *
	 * Initialises Number Formats for Excel Export
	 *
	 * Initialises Number Formats for Excel Export
	 *
	 * @param	Spreadsheet_Excel_Writer	$wkbWorkbook	Workbook to create formats for
	 *
	 * @return	array										Associative Array of Formats
	 *
	 * @method
	 */
	protected function _InitExcelFormats($wkbWorkbook)
	{
		$arrFormat = Array();
		
		// Integer format (make sure it doesn't show exponentials for large ints)
		$fmtInteger =& $wkbWorkbook->addFormat();
		$fmtInteger->setNumFormat('0');
		$arrFormat['Integer']		= $fmtInteger;
		
		// Bold Integer format (make sure it doesn't show exponentials for large ints)
		$fmtIntegerBold =& $wkbWorkbook->addFormat();
		$fmtIntegerBold->setNumFormat('0');
		$fmtIntegerBold->SetBold();
		$arrFormat['IntegerBold']		= $fmtIntegerBold;
		
		// Total Integer format (make sure it doesn't show exponentials for large ints)
		$fmtIntegerTotal =& $wkbWorkbook->addFormat();
		$fmtIntegerTotal->setNumFormat('0');
		$fmtIntegerTotal->setBold();
		$fmtIntegerTotal->setTopColor('black');
		$fmtIntegerTotal->setTop(1);
		$arrFormat['IntegerTotal']		= $fmtIntegerTotal;
		
		
		
		// Bold Text
		$fmtBold		= $wkbWorkbook->addFormat();
		$fmtBold->setBold();
		$arrFormat['TextBold']		= $fmtBold;
		
		// Bold Italics Text
		$fmtBoldItalic	= $wkbWorkbook->addFormat();
		$fmtBoldItalic->setBold();
		$fmtBoldItalic->setItalic();
		$arrFormat['TextBoldItalic']	= $fmtBoldItalic;
		
		// Title Row
		$fmtTitle =& $wkbWorkbook->addFormat();
		$fmtTitle->setBold();
		$fmtTitle->setSize(12);
		$fmtTitle->setBottom(1);
		$arrFormat['Title']			= $fmtTitle;
		
		// Column Title Row
		$fmtColTitle =& $wkbWorkbook->addFormat();
		$fmtColTitle->setBold();
		$fmtColTitle->setBottom(1);
		$arrFormat['ColTitle']		= $fmtColTitle;
		
		// Alternate Column Title Row
		$fmtAltColTitle =& $wkbWorkbook->addFormat();
		$fmtAltColTitle->setBold();
		$fmtAltColTitle->setBottom(1);
		$fmtAltColTitle->setTop(1);
		$arrFormat['AltColTitle']	= $fmtAltColTitle;
		
		// Title Italic
		$fmtTitleItalic	= $wkbWorkbook->addFormat();
		$fmtTitleItalic->setBold();
		$fmtTitleItalic->setItalic();
		$fmtTitleItalic->setBottom(1);
		$arrFormat['TitleItalic']	= $fmtTitleItalic;
		
		// Total Text Cell
		$fmtTotalText	= $wkbWorkbook->addFormat();
		$fmtTotalText->setTopColor('black');
		$fmtTotalText->setTop(1);
		$fmtTotalText->setBold();
		$arrFormat['TotalText']		= $fmtTotalText;
		
		// Page Title
		$fmtPageTitle	= $wkbWorkbook->addFormat();
		$fmtPageTitle->setSize(12);
		$fmtPageTitle->setBold();
		$fmtPageTitle->setUnderline(1);
		$fmtPageTitle->setHAlign('center');
		$arrFormat['PageTitle']		= $fmtPageTitle;
		
		
		// Currency
		$fmtCurrency	= $wkbWorkbook->addFormat();
		$fmtCurrency->setNumFormat('$#,##0.00;-$#,##0.00');
		$fmtCurrency->setAlign('right');
		$arrFormat['Currency']		= $fmtCurrency;
		
		// Bold Currency
		$fmtCurrencyBold	= $wkbWorkbook->addFormat();
		$fmtCurrencyBold->setNumFormat('$#,##0.00;-$#,##0.00');
		$fmtCurrencyBold->setBold();
		$arrFormat['CurrencyBold']	= $fmtCurrencyBold;
		
		// Total Currency
		$fmtTotal		= $wkbWorkbook->addFormat();
		$fmtTotal->setNumFormat('$#,##0.00;-$#,##0.00');
		$fmtTotal->setBold();
		$fmtTotal->setTopColor('black');
		$fmtTotal->setTop(1);
		$arrFormat['CurrencyTotal']	= $fmtTotal;
		
		
		
		// Credit/Debit
		$fmtCreditDebit	= $wkbWorkbook->addFormat();
		$fmtCreditDebit->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$arrFormat['CreditDebit']		= $fmtCreditDebit;
		
		
		
		// Percentage
		$fmtPercentage	= $wkbWorkbook->addFormat();
		$fmtPercentage->setNumFormat('0.00%;[red]-0.00%');
		$fmtPercentage->setAlign('right');
		$arrFormat['Percentage']	= $fmtPercentage;
		
		// Bold Percentage
		$fmtPCBold		= $wkbWorkbook->addFormat();
		$fmtPCBold->setNumFormat('0.00%;-0.00%');
		$fmtPCBold->setBold();
		$fmtPercentage->setAlign('right');
		$arrFormat['PercentageBold']	= $fmtPCBold;
		
		// Total Percentage
		$fmtPCTotal		= $wkbWorkbook->addFormat();
		$fmtPCTotal->setNumFormat('0.00%;-0.00%');
		$fmtPCTotal->setBold();
		$fmtPCTotal->setTopColor('black');
		$fmtPCTotal->setTop(1);
		$fmtPercentage->setAlign('right');
		$arrFormat['PercentageTotal']	= $fmtPCTotal;
		
		
		
		// Short Date
		$fmtShortDate	= $wkbWorkbook->addFormat();
		$fmtShortDate->setNumFormat('DD/MM/YYYY');
		$arrFormat['ShortDate']			= $fmtShortDate;
		
		// Month/Year
		$fmtMonthYear	= $wkbWorkbook->addFormat();
		$fmtMonthYear->setNumFormat('MMMM YYYY');
		$arrFormat['MonthYear']			= $fmtMonthYear;
		
		// Time
		$fmtTime	= $wkbWorkbook->addFormat();
		$fmtTime->setNumFormat('[HH]:MM:SS');
		$arrFormat['Time']				= $fmtTime;
			
		
		
		// Kilobytes
		$fmtKB	= $wkbWorkbook->addFormat();
		$fmtKB->setNumFormat('0 KB');
		$arrFormat['Kilobytes']			= $fmtKB;
		
		
		
		// FNN
		$fmtFNN			= $wkbWorkbook->addFormat();
		$fmtFNN->setNumFormat('0000000000');
		$arrFormat['FNN']				= $fmtFNN;
		
		
		
		// Overline Bold
		$fmtOverlineBold	= $wkbWorkbook->addFormat();
		$fmtOverlineBold->setTop(1);
		$arrFormat['OverlineBold']		= $fmtOverlineBold;
		
		
		
		// Blank Underline
		$fmtBlankUnderline	= $wkbWorkbook->addFormat();
		$fmtBlankUnderline->setBottom(1);
		$arrFormat['BlankUnderline']	= $fmtBlankUnderline;
		
		// Blank Overline
		$fmtBlankOverline	= $wkbWorkbook->addFormat();
		$fmtBlankOverline->setTop(1);
		$arrFormat['BlankOverline']		= $fmtBlankOverline;
		
		// Line Spacer
		$fmtSpacer			= $wkbWorkbook->addFormat();
		$fmtSpacer->setTop(1);
		$fmtSpacer->setBottom(1);
		$arrFormat['Spacer']			= $fmtSpacer;
		
		// Left Spacer
		$fmtLeftSpacer		= $wkbWorkbook->addFormat();
		$fmtLeftSpacer->setRight(1);
		$arrFormat['LeftSpacer']		= $fmtLeftSpacer;	
		
		return $arrFormat;
	}
 	
 	
 	
	//------------------------------------------------------------------------//
	// _ReportServiceSummary()
	//------------------------------------------------------------------------//
	/**
	 * _ReportServiceSummary()
	 *
	 * Create a Service Summary Management Report and return the path to the file
	 *
	 * Create a Service Summary Management Report and return the path to the file
	 *
	 * @return	mixed								Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
	protected function _ReportServiceSummary()
	{
		$selServicesClosed		= new StatementSelect(	"Service",
														"Id",
														"Id NOT IN (SELECT Service FROM ServiceTotal WHERE InvoiceRun = <InvoiceRun>)");
														
		$selServicesOpen		= new StatementSelect(	"ServiceTotal",
														"Id",
														"InvoiceRun = <InvoiceRun>");
		
		$selServicesActive		= new StatementSelect(	"ServiceTotal",
														"Id",
														"(ServiceTotal.Debit > 0 OR ServiceTotal.UncappedCharge > 0 OR ServiceTotal.CappedCharge > 0) AND ServiceTotal.Debit IS NOT NULL AND ServiceTotal.InvoiceRun = <InvoiceRun>");
		
		$selServicesTolling		= new StatementSelect(	"CDR",
														"DISTINCT Service",
														"InvoiceRun = <InvoiceRun> AND <RatePlan> = (SELECT RatePlan FROM ServiceRatePlan WHERE <BillingDate> BETWEEN StartDatetime AND EndDatetime AND Service = CDR.Service ORDER BY CreatedOn DESC LIMIT 1)");
		
		$selChurns				= new StatementSelect(	"ProvisioningResponse",
														"Id",
														"Status = 402 AND EffectiveDate >= <LastBillingDate> AND EffectiveDate < <BillingDate> AND Type = 910");
		
		$selWinbacks			= new StatementSelect(	"ProvisioningResponse",
														"DISTINCT Service",
														"Status = 402 AND EffectiveDate >= <LastBillingDate> AND EffectiveDate < <BillingDate> AND Type = 910 AND " .
														"900 = (SELECT Type FROM ProvisioningResponse PR2 WHERE Service = ProvisioningResponse.Service AND Status = 402 AND EffectiveDate >= <LastBillingDate> AND EffectiveDate < <BillingDate> AND Type IN (900, 910) ORDER BY EffectiveDate DESC LIMIT 1)");
		
		$selOldWinbacks			= new StatementSelect(	"Service",
														"DISTINCT Id",
														"910 = (SELECT Type FROM ProvisioningResponse WHERE Service.Id = Service AND Status = 402 AND EffectiveDate < <LastBillingDate> AND Type IN (900, 910) ORDER BY EffectiveDate DESC LIMIT 1) AND " .
														"900 = (SELECT Type FROM ProvisioningResponse WHERE Service.Id = Service AND Status = 402 AND EffectiveDate >= <LastBillingDate> AND EffectiveDate < <BillingDate> AND Type IN (900, 910) ORDER BY EffectiveDate DESC LIMIT 1)");
		
		$selNewServices			= new StatementSelect(	"Service",
														"Id",
														"CreatedOn >= <LastBillingDate> AND CreatedOn < <BillingDate>");
		
		$selNewTollingServices	= new StatementSelect(	"Service JOIN CDR ON Service.Id = CDR.Service",
														"DISTINCT Service.Id",
														"CreatedOn >= <LastBillingDate> AND CreatedOn < <BillingDate> AND InvoiceRun = <InvoiceRun> AND Credit = 0");
		
		$selFirstTollingServices	= new StatementSelect(	"Service",
															"DISTINCT Id",
															"CreatedOn < <LastBillingDate> AND " .
															"<InvoiceRun> = (SELECT InvoiceRun FROM ServiceTypeTotal WHERE Service = Service.Id ORDER BY InvoiceRun ASC LIMIT 1)");

		$selServicesByType		= new StatementSelect("Service LEFT JOIN ServiceTotal ON Service.Id = ServiceTotal.Service", "Service.ServiceType AS ServiceType, Service.Status, COUNT(Service.Id) AS ServiceCount", "ServiceTotal.InvoiceRun = <InvoiceRun>", "Service.ServiceType", NULL, "Service.ServiceType, Service.Status");
		
		$selServicesLost	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.InvoiceRun = <LastInvoiceRun> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.InvoiceRun = <InvoiceRun>)");
		$selServicesGained	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.InvoiceRun = <InvoiceRun> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.InvoiceRun = <LastInvoiceRun>)");
		
		// Create Workbook
		$strFilename = "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()))."Service_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Service Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])))." Service Summary for Telco Blue");
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
		//$wksWorksheet->writeString(9, 0, "Currently Open Services"		, $arrFormat['TextBold']);
		//$wksWorksheet->writeString(10, 0, "Currently Archived Services"	, $arrFormat['TextBold']);
		//$wksWorksheet->writeString(11, 0, "Services Lost"				, $arrFormat['TextBold']);
		//$wksWorksheet->writeString(12, 0, "Services Gained"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString(9, 0, "Total Churns"					, $arrFormat['TextBold']);
		$wksWorksheet->writeString(10, 0, "Winbacks (Lost This Month)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(11, 0, "Winbacks (Lost Previously)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(12, 0, "Total Lost"					, $arrFormat['TextBold']);
		$wksWorksheet->writeString(13, 0, "Total New Services"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(14, 0, "New Tolling Services"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(15, 0, "First Tolling Services"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(16, 0, "Total New Tolling Services"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(17, 0, "Net Service Gain"			, $arrFormat['TextBold']);
		
		$intRow					= 17;
		$arrSummaryStructure	= Array();
		foreach ($GLOBALS['**arrConstant']['ServiceType'] as $intServiceType=>$arrServiceType)
		{			
			$intRow++;
			$wksWorksheet->writeString($intRow, 0, "{$arrServiceType['Description']} Summary"	, $arrFormat['Title']);
			$wksWorksheet->writeString($intRow, 2, "This Month"									, $arrFormat['TitleItalic']);
			$wksWorksheet->writeString($intRow, 3, "Last Month"									, $arrFormat['TitleItalic']);
			$wksWorksheet->writeString($intRow, 4, "% Change"									, $arrFormat['TitleItalic']);
			
			foreach ($GLOBALS['**arrConstant']['Service'] as $intStatus=>$arrStatus)
			{
				$intRow++;
				$arrSummaryStructure[$intServiceType][$intStatus]	= $intRow;
				$wksWorksheet->writeString($intRow, 0, "Current {$arrStatus['Description']} Services"				, $arrFormat['TextBold']);
			}
			$intRow++;
		}
		
		//$wksWorksheet->writeString(18, 0, "Service Type Summary"		, $arrFormat['Title']);
		//$wksWorksheet->writeString(18, 2, "This Month"					, $arrFormat['TitleItalic']);
		//$wksWorksheet->writeString(18, 3, "Last Month"					, $arrFormat['TitleItalic']);
		//$wksWorksheet->writeString(18, 4, "% Change"					, $arrFormat['TitleItalic']);
		
		//$wksWorksheet->writeString(19, 0, "Current Land Line Services"	, $arrFormat['TextBold']);
		//$wksWorksheet->writeString(20, 0, "Current Mobile Services"		, $arrFormat['TextBold']);
		//$wksWorksheet->writeString(21, 0, "Current Inbound Services"	, $arrFormat['TextBold']);
		//$wksWorksheet->writeString(22, 0, "Current ADSL Services"		, $arrFormat['TextBold']);
		
		$intCol = 2;
		foreach ($this->_arrProfitData as $strPeriod=>$arrData)
		{
			// Header Data
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['InvoiceRun']);
			
			// Service Status Summary
			$intServicesLost			= $selServicesLost->Execute($arrData);
			$intServicesGained			= $selServicesGained->Execute($arrData);
			$intServicesOpen			= $selServicesOpen->Execute($arrData);
			$intServicesClosed			= $selServicesClosed->Execute($arrData);
			$intServicesActive			= $selServicesActive->Execute($arrData);
			$intServicesTolling			= $selServicesTolling->Execute($arrData);
			$intChurns					= $selChurns->Execute($arrData);
			$intWinbacks				= $selWinbacks->Execute($arrData);
			$intOldWinbacks				= $selOldWinbacks->Execute($arrData);
			$intNewServices				= $selNewServices->Execute($arrData);
			$intNewTollingServices		= $selNewTollingServices->Execute($arrData);
			$intFirstTollingServices	= $selFirstTollingServices->Execute($arrData);
			$intTotalLost				= $intChurns - ($intWinbacks + $intOldWinbacks);
			$intTotalNewTolling			= $intNewTollingServices + $intFirstTollingServices;
			$intNetServicesGained		= $intTotalNewTolling - $intTotalLost;
			
			$wksWorksheet->writeNumber(8, $intCol, $intServicesTolling			, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(9, $intCol, $intChurns					, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(10, $intCol, $intWinbacks				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(11, $intCol, $intOldWinbacks				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(12, $intCol, $intTotalLost				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(13, $intCol, $intNewServices				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(14, $intCol, $intNewTollingServices		, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(15, $intCol, $intFirstTollingServices	, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(16, $intCol, $intTotalNewTolling			, $arrFormat['Integer']);
			$wksWorksheet->writeNumber(17, $intCol, $intNetServicesGained		, $arrFormat['Integer']);
			
			$wksWorksheet->writeString(9, 0, "Total Churns"					, $arrFormat['TextBold']);
			$wksWorksheet->writeString(10, 0, "Winbacks (Lost This Month)"	, $arrFormat['TextBold']);
			$wksWorksheet->writeString(11, 0, "Winbacks (Lost Previously)"	, $arrFormat['TextBold']);
			$wksWorksheet->writeString(12, 0, "Total Lost"					, $arrFormat['TextBold']);
			$wksWorksheet->writeString(13, 0, "Total New Services"			, $arrFormat['TextBold']);
			$wksWorksheet->writeString(14, 0, "New Tolling Services"		, $arrFormat['TextBold']);
			$wksWorksheet->writeString(15, 0, "First Tolling Services"		, $arrFormat['TextBold']);
			$wksWorksheet->writeString(16, 0, "Total New Tolling Services"	, $arrFormat['TextBold']);
			$wksWorksheet->writeString(17, 0, "Net Service Gain"			, $arrFormat['TextBold']);
			
			// Service Type Summary
			$selServicesByType->Execute($arrData);
			while ($arrServiceType = $selServicesByType->Fetch())
			{
				$intSummaryRow	= $arrSummaryStructure[$arrServiceType['ServiceType']][$arrServiceType['Status']];
				
				// Only show value if rendering this month's data
				if ($strPeriod == "ThisMonth")
				{
					$wksWorksheet->writeNumber($intSummaryRow, $intCol, $arrServiceType['ServiceCount']	, $arrFormat['Integer']);
				}
				else
				{
					$wksWorksheet->writeString($intSummaryRow, $intCol, "N/A");
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
	
	//------------------------------------------------------------------------//
	// _ReportPlanSummary()
	//------------------------------------------------------------------------//
	/**
	 * _ReportPlanSummary()
	 *
	 * Create a Plan Summary Management Report and return the path to the file
	 *
	 * Create a Plan Summary Management Report and return the path to the file
	 *
	 * @return	mixed							Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
	protected function _ReportPlanSummary()
	{		
		// Statements
		$selCustomersToPlan		= new StatementSelect(	"ServiceTotal",
														"DISTINCT Account",
														"InvoiceRun = <InvoiceRun> AND RatePlan = <RatePlan> AND " .
														"(SELECT COUNT(Id) FROM ServiceTotal ST2 WHERE InvoiceRun = <LastInvoiceRun> AND Service = <Service> AND RatePlan != <RatePlan>) = 1");
		
		$selCustomersFromPlan	= new StatementSelect(	"ServiceTotal",
														"DISTINCT Account",
														"InvoiceRun = <LastInvoiceRun> AND RatePlan = <RatePlan> AND " .
														"(SELECT COUNT(Id) FROM ServiceTotal ST2 WHERE InvoiceRun = <InvoiceRun> AND Service = <Service> AND RatePlan != <RatePlan>) = 1");
		
		$selNewCustomers		= new StatementSelect(	"ServiceTotal",
														"DISTINCT Account",
														"InvoiceRun = <InvoiceRun> AND RatePlan = <RatePlan> AND " .
														"<InvoiceRun> = (SELECT InvoiceRun FROM Invoice WHERE Account = ServiceTotal.Account ORDER BY CreatedOn ASC LIMIT 1)");
		
		$selNewCustomersTemp	= new StatementSelect(	"ServiceTotal",
														"DISTINCT Account",
														"InvoiceRun = <InvoiceRun> AND RatePlan = <RatePlan> AND " .
														"<InvoiceRun> = (SELECT InvoiceRun FROM InvoiceTemp WHERE Account = ServiceTotal.Account ORDER BY CreatedOn ASC LIMIT 1)");
		
		$selExistingNewLine		= new StatementSelect(	"ServiceTotal",
														"DISTINCT Account",
														"InvoiceRun = <InvoiceRun> AND 0 < (SELECT COUNT(Id) FROM Invoice WHERE InvoiceRun != <InvoiceRun> AND Account = ServiceTotal.Account) AND " .
														"0 = (SELECT COUNT(Id) FROM ServiceTotal WHERE Service = <Service> AND InvoiceRun != <InvoiceRun>)");
		
		$selChurned				= new StatementSelect(	"Service",
														"DISTINCT Account",
														"910 = (SELECT Account FROM ProvisioningResponse WHERE" .
														" <RatePlan> = (SELECT RatePlan FROM ServiceRatePlan WHERE Service = ProvisioningResponse.Service AND <BillingDate> BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn DESC LIMIT 1)" .
														" AND Type IN (900, 910) AND Status = 402 AND Service = Service.Id ORDER BY EffectiveDate DESC LIMIT 1)");
		
		$qryMeanCustomerSpend	= new Query();
		
		$arrCols = Array();
		$arrCols['CurrentCustomers']	= "COUNT(DISTINCT ServiceTotal.Account)";
		$arrCols['CurrentServices']		= "COUNT(ServiceTotal.Id)";
		$arrCols['MeanCustomerSpend']	= "AVG(TotalCharge)";
		$arrCols['TotalCost']			= "SUM(ServiceTotal.CappedCost + ServiceTotal.UncappedCost)";
		$arrCols['TotalRated']			= "SUM(ServiceTotal.CappedCharge + ServiceTotal.UncappedCharge)";
		$arrCols['TotalBilled']			= "SUM(TotalCharge)";
		$selPlanSummary = new StatementSelect(	"Service JOIN ServiceTotal ON Service.Id = ServiceTotal.Service",
												$arrCols,
												"ServiceTotal.RatePlan = <RatePlan> AND ServiceTotal.InvoiceRun = <InvoiceRun>");
		$selServicesLost	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.InvoiceRun = <LastInvoiceRun> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.InvoiceRun = <InvoiceRun> AND ST2.RatePlan = <RatePlan>) AND ST.RatePlan = <RatePlan>");
		$selServicesGained	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.InvoiceRun = <InvoiceRun> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.InvoiceRun = <LastInvoiceRun> AND ST2.RatePlan = <RatePlan>) AND ST.RatePlan = <RatePlan>");
		$selCallTypes		= new StatementSelect(	"(RatePlanRateGroup RPRG JOIN RateGroup RG ON RPRG.RateGroup = RG.Id) JOIN RecordType ON RG.RecordType = RecordType.Id",
													"RPRG.RatePlan AS RatePlan, RecordType.DisplayType AS DisplayType, RG.Id AS RateGroup, RecordType.Id AS RecordType, RecordType.Description AS Description",
													"RPRG.RatePlan = <RatePlan>",
													"RecordType.Description");
		/*$selCallTypes		= new StatementSelect(	"RecordType",
													"*, Id AS RecordType",
													"ServiceType = <ServiceType>");*/
		$arrCols = Array();
		$arrCols['MeanCallDuration']	= "AVG(CDR.Units)";
		$arrCols['MeanCallCost']		= "AVG(CDR.Cost)";
		$arrCols['MeanCallCharge']		= "AVG(CDR.Charge)";
		$arrCols['TotalCallDuration']	= "SUM(CDR.Units)";
		$arrCols['TotalCallCount']		= "COUNT(CDR.Id)";
		$arrCols['TotalCost']			= "SUM(CDR.Cost)";
		$arrCols['TotalCharged']		= "SUM(CDR.Charge)";
		$selCallSummary	= new StatementSelect("CDR JOIN ServiceTotal ON ServiceTotal.Service = CDR.Service", $arrCols, "ServiceTotal.RatePlan = <RatePlan> AND CDR.InvoiceRun = <InvoiceRun> AND ServiceTotal.InvoiceRun = <InvoiceRun> AND CDR.RecordType = <RecordType> AND CDR.Credit = 0");
		
		$selCallCredits	= new StatementSelect("CDR JOIN ServiceTotal ON ServiceTotal.Service = CDR.Service", $arrCols, "ServiceTotal.RatePlan = <RatePlan> AND CDR.InvoiceRun = <InvoiceRun> AND ServiceTotal.InvoiceRun = <InvoiceRun> AND CDR.Credit = 1");
		
		$selRatePlans	= new StatementSelect("RatePlan", "*", "Archived = 0", "ServiceType");
		$selMeanSpend	= new StatementSelect("ServiceTypeTotal STT JOIN ServiceTotal ST USING (InvoiceRun, Service)", "AVG(STT.Charge) AS MeanServiceSpend", "ST.InvoiceRun = <InvoiceRun> AND ST.RatePlan = <RatePlan> AND STT.RecordType = <RecordType>");
		
		$selMeanCredit	= new StatementSelect("CDR JOIN ServiceTotal ST USING (InvoiceRun, Service)", "SUM(CDR.Charge) AS TotalServiceCredit", "ST.InvoiceRun = <InvoiceRun> AND ST.RatePlan = <RatePlan> AND CDR.RecordType = <RecordType> AND Credit = 1");
		
		// For each non-Archived RatePlan
		$intPlanCount = $selRatePlans->Execute();
		$arrServiceTypes = Array();
		while ($arrRatePlan = $selRatePlans->Fetch())
		{
			$arrServiceTypes[$arrRatePlan['ServiceType']][]	= $arrRatePlan;
		}
		
		$intI = 0;
		$arrFilenames = Array();
		foreach ($arrServiceTypes as $intServiceType=>$arrRatePlans)
		{
			// Create new Workbook
			$strServiceType = str_replace(' ', '_', GetConstantDescription($intServiceType, 'service_type'));
			$strServiceType = str_replace('/', '_', $strServiceType);
			$strServiceType = str_replace('\\', '_', $strServiceType);
			$strFilename	= "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()))."Plan_Summary_with_Breakdown_($strServiceType).xls";
			$arrFilenames[]	= $strFilename;
			@unlink($strFilename);
			$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
			
			// Init formats
			$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
			
			foreach ($arrRatePlans as $arrRatePlan)
			{
				$intI++;
				
				// Add new Worksheet
				$wksWorksheet =& $wkbWorkbook->addWorksheet($arrRatePlan['Description']);
				$wksWorksheet->setLandscape();
				$wksWorksheet->hideGridlines();
				$wksWorksheet->fitToPages(1, 99);
				
				// Set Columns sizes
				$fltExcelWidthRatio = 5;
				$wksWorksheet->setColumn(0, 0, 5.65 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(1, 1, 3.56 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(2, 2, 2.87 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(3, 3, 3.31 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(4, 4, 3.66 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(5, 5, 3.50 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(6, 6, 3.06 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(7, 7, 2.24 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(8, 8, 2.71 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(9, 9, 2.82 * $fltExcelWidthRatio);
				$wksWorksheet->setColumn(10, 10, 2.82 * $fltExcelWidthRatio);
				
				// Write Worksheet Header
				$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])))." Plan Summary for Telco Blue");
				$wksWorksheet->writeString(0, 4, $strPageTitle, $arrFormat['PageTitle']);
				
				for ($i = 0; $i <= 9; $i++)
				{
					$wksWorksheet->writeBlank(1, $i, $arrFormat['BlankUnderline']);
					$wksWorksheet->writeBlank(5, $i, $arrFormat['Spacer']);
				}
				
				$wksWorksheet->writeString(2, 0, "Customer"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(3, 0, "Service Type"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(4, 0, "Plan"				, $arrFormat['TextBold']);
				$wksWorksheet->writeString(2, 9, "Bill Date"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(3, 9, "Billing Period"	, $arrFormat['TextBold']);
				$wksWorksheet->writeString(4, 9, "Invoice Run"		, $arrFormat['TextBold']);
				
				$wksWorksheet->writeString(2, 1, "Telco Blue");										// FIXME: Use Customer Name
				$wksWorksheet->writeString(3, 1, GetConstantDescription($arrRatePlan['ServiceType'], 'service_type'));
				$wksWorksheet->writeString(4, 1, $arrRatePlan['Description']);
				$wksWorksheet->writeString(2, 10, date("d/m/Y", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])));
				$wksWorksheet->writeString(3, 10, date("F Y", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate']))));
				$wksWorksheet->writeString(4, 10, $this->_arrProfitData['ThisMonth']['InvoiceRun']);
				
				$wksWorksheet->writeString(6, 0, "Plan Summary"		, $arrFormat['Title']);
				$wksWorksheet->writeString(6, 8, "This Month"		, $arrFormat['TitleItalic']);
				$wksWorksheet->writeString(6, 9, "Last Month"		, $arrFormat['TitleItalic']);
				$wksWorksheet->writeString(6, 10, "% Change"			, $arrFormat['TitleItalic']);
				
				for ($i = 1; $i <= 7; $i++)
				{
					$wksWorksheet->writeBlank(6, $i, $arrFormat['BlankUnderline']);
				}
				
				$wksWorksheet->writeString(7, 0, "Current Customers"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(8, 0, "Current Services"			, $arrFormat['TextBold']);
				//$wksWorksheet->writeString(9, 0, "Services Gained"			, $arrFormat['TextBold']);
				//$wksWorksheet->writeString(10, 0, "Services Lost"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(9, 0, "Changed To This Plan"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(10, 0, "Changed From This Plan"	, $arrFormat['TextBold']);
				$wksWorksheet->writeString(11, 0, "New Customers"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(12, 0, "Existing Customer (New Service)"	, $arrFormat['TextBold']);
				$wksWorksheet->writeString(13, 0, "Customer Churn"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(14, 0, "Mean Customer Spend"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(15, 0, "Total Cost"				, $arrFormat['TextBold']);
				$wksWorksheet->writeString(16, 0, "Total Rated"				, $arrFormat['TextBold']);
				$wksWorksheet->writeString(17, 0, "Total Billed"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(18, 0, "Total Profit"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(19, 0, "Profit Margin"			, $arrFormat['TextBold']);
				
				for ($i = 0; $i <= 10; $i++)
				{
					$wksWorksheet->writeBlank(17, $i, $arrFormat['Spacer']);
				}
				
				// Write Plan Summary
				$intCol = 8;
				foreach ($this->_arrProfitData as $strMonth=>$arrData)
				{
					// Get Plan Summary Info
					$arrData['RatePlan'] = $arrRatePlan['Id'];
					$selPlanSummary->Execute($arrData);
					$arrPlanSummary = $selPlanSummary->Fetch();
					
					$intCustomersToPlan		= $selCustomersToPlan->Execute($arrData);
					$intCustomersFromPlan	= $selCustomersFromPlan->Execute($arrData);
					$intExistingNewLine		= $selExistingNewLine->Execute($arrData);
					$intChurned				= $selChurned->Execute($arrData);
					
					if ($this->_selIsTempInvoice->Execute($arrData))
					{
						$intNewCustomers	= $selNewCustomersTemp->Execute($arrData);
					}
					else
					{
						$intNewCustomers	= $selNewCustomers->Execute($arrData);
					}
					
					// Services Gained and Lost
					$arrPlanSummary['ServicesLost']		= $selServicesLost->Execute($arrData);
					$arrPlanSummary['ServicesGained']	= $selServicesGained->Execute($arrData);
					
					$qryMeanCustomerSpend->Execute(	"SELECT AVG(GrandTotal) AS MeanCustomerSpend " .
													"FROM (" .
														"SELECT SUM(TotalCharge) AS GrandTotal " .
														"FROM ServiceTotal WHERE InvoiceRun = {$arrData['InvoiceRun']})");
					$arrMeanCustomerSpend	= $qryMeanCustomerSpend->Fetch();
					
					// Write to Worksheet
					$fltProfit	= $arrPlanSummary['TotalBilled'] - $arrPlanSummary['TotalCost'];
					$wksWorksheet->writeNumber(7	, $intCol, $arrPlanSummary['CurrentCustomers']			, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(8	, $intCol, $arrPlanSummary['CurrentServices']			, $arrFormat['Integer']);
					//$wksWorksheet->writeNumber(9	, $intCol, $arrPlanSummary['ServicesGained']			, $arrFormat['Integer']);
					//$wksWorksheet->writeNumber(10	, $intCol, $arrPlanSummary['ServicesLost']				, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(9	, $intCol, $intCustomersToPlan							, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(10	, $intCol, $intCustomersFromPlan						, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(11	, $intCol, $intNewCustomers								, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(12	, $intCol, $intExistingNewLine							, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(13	, $intCol, $intChurned									, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(14	, $intCol, $arrMeanCustomerSpend['MeanCustomerSpend']	, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(15	, $intCol, $arrPlanSummary['TotalCost']					, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(16	, $intCol, $arrPlanSummary['TotalRated']				, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(17	, $intCol, $arrPlanSummary['TotalBilled']				, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(18	, $intCol, $fltProfit									, $arrFormat['CreditDebit']);
					
					if ($intCol == 7)
					{
						$wksWorksheet->writeFormula(19	, $intCol, "=IF(AND(H15 <> 0, NOT(H15 = \"N/A\")), (H15 - H13) / ABS(H15), \"N/A\")"			, $arrFormat['Percentage']);
					}
					else
					{
						$wksWorksheet->writeFormula(19	, $intCol, "=IF(AND(I15 <> 0, NOT(I15 = \"N/A\")), (I15 - I13) / ABS(I15), \"N/A\")"			, $arrFormat['Percentage']);
					}					
					$intCol++;
				}
				
				// Write Plan Summary '% Change' Fields
				for ($i = 8; $i <= 17; $i++)
				{
					$wksWorksheet->writeFormula($i-1, 10, "=IF(AND(H$i <> 0, NOT(H$i = \"N/A\")), (H$i - I$i) / ABS(H$i), \"N/A\")", $arrFormat['Percentage']);
				}
				
				// Write Call Type Breakdown
				$wksWorksheet->writeString(18, 0, "Call Type Summary"		, $arrFormat['Title']);
				
				for ($i = 1; $i <= 10; $i++)
				{
					$wksWorksheet->writeBlank(18, $i, $arrFormat['BlankUnderline']);
				}
				
				$intRow = 19;
				$wksWorksheet->writeString($intRow, 0, "Call Type"			, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 1, "Mean Call Duration"	, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 2, "Mean Call Cost"		, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 3, "Mean Call Charge"	, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 4, "Mean Service Spend"	, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 5, "Total Call Duration", $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 6, "Total Call Count"	, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 7, "Total Cost"			, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 8, "Total Rated"		, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 9, "Profit"				, $arrFormat['ColTitle']);
				$wksWorksheet->writeString($intRow, 10, "Profit Margin"		, $arrFormat['ColTitle']);
			
				$selCallTypes->Execute(Array('RatePlan' => $arrRatePlan['Id']));
				$arrCallTypes	= $selCallTypes->FetchAll();
				$arrCallTypes[]	= Array('Credit' => TRUE, 'Description' => "Call Credits", 'DisplayType' => RECORD_DISPLAY_CALL);
				foreach ($arrCallTypes as $arrCallType)
				{
					$intRow++;
					$i = $intRow + 1;
					
					// Get Call Type Summary
					$arrCallType['InvoiceRun'] = $this->_arrProfitData['ThisMonth']['InvoiceRun'];
					if (!$arrCallType['Credit'])
					{
						$selCallSummary->Execute($arrCallType);
						$arrCallSummary = $selCallSummary->Fetch();
						
						$selMeanSpend->Execute($arrCallType);
						$arrCallSummary = array_merge($arrCallSummary, $selMeanSpend->Fetch());
					}
					else
					{
						// Credit Totals
						$intCallCredits			= $selCallCredits->Execute($arrCallType);
						$intTotalCredits		= $selMeanCredit->Execute($arrCallType);
						$arrCallSummary			= $selCallCredits->Fetch($arrCallType);
						$arrCallSummary['MeanServiceSpend']	= 0;
						while ($arrTotalCredit = $selMeanCredit->Fetch())
						{
							$arrCallSummary['MeanServiceSpend'] -= $arrTotalCredit['TotalServiceCredit'];
						}
						$arrCallSummary['MeanServiceSpend'] /= $intTotalCredits;
						$arrCallSummary['MeanCallCost']		= 0 - $arrCallSummary['MeanCallCost'];
						$arrCallSummary['MeanCallCharge']	= 0 - $arrCallSummary['MeanCallCharge'];
						$arrCallSummary['TotalCost']		= 0 - $arrCallSummary['TotalCost'];
						$arrCallSummary['TotalCharged']		= 0 - $arrCallSummary['TotalCharged'];
					}
					
					// Profit
					$fltProfit	= $arrCallSummary['TotalCharged'] - $arrCallSummary['TotalCost'];
				
					$wksWorksheet->writeString($intRow, 0, $arrCallType['Description']);
					$wksWorksheet->writeNumber($intRow, 2, $arrCallSummary['MeanCallCost']		, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 3, $arrCallSummary['MeanCallCharge']	, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 4, $arrCallSummary['MeanServiceSpend']	, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 6, $arrCallSummary['TotalCallCount']	, $arrFormat['Integer']);
					$wksWorksheet->writeNumber($intRow, 7, $arrCallSummary['TotalCost']			, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 8, $arrCallSummary['TotalCharged']		, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 9, $fltProfit							, $arrFormat['CreditDebit']);
					$wksWorksheet->writeFormula($intRow, 10, "=IF(AND(J$i <> 0, NOT(J$i = \"N/A\")), (J$i - I$i) / ABS(J$i), \"N/A\")", $arrFormat['Percentage']);
					
					// Display Types
					switch ($arrCallType['DisplayType'])
					{
						case RECORD_DISPLAY_CALL:
							$wksWorksheet->writeNumber($intRow, 1, $arrCallSummary['MeanCallDuration'] / 86400	, $arrFormat['Time']);
							$wksWorksheet->writeNumber($intRow, 5, $arrCallSummary['TotalCallDuration']	/ 86400	, $arrFormat['Time']);
							break;
						
						case RECORD_DISPLAY_DATA:
							$wksWorksheet->writeNumber($intRow, 1, $arrCallSummary['MeanCallDuration']	, $arrFormat['Kilobytes']);
							$wksWorksheet->writeNumber($intRow, 5, $arrCallSummary['TotalCallDuration']	, $arrFormat['Kilobytes']);
							break;
						
						default:
							$wksWorksheet->writeNumber($intRow, 1, $arrCallSummary['MeanCallDuration']	, $arrFormat['Integer']);
							$wksWorksheet->writeNumber($intRow, 5, $arrCallSummary['TotalCallDuration']	, $arrFormat['Integer']);
					}
				}
				
				for ($i = 0; $i <= 10; $i++)
				{
					$wksWorksheet->writeBlank($intRow+1, $i, $arrFormat['BlankOverline']);
				}
			}
		
			// Close file and change permissions
			$wkbWorkbook->close();
			chmod($strFilename, 0777);
		}
		
		return $arrFilenames;
	}
	
	
	//------------------------------------------------------------------------//
	// _ReportAdjustmentSummary()
	//------------------------------------------------------------------------//
	/**
	 * _ReportAdjustmentSummary()
	 *
	 * Create a Adjustment Summary Management Report and return the path to the file
	 *
	 * Create a Adjustment Summary Management Report and return the path to the file
	 *
	 * @return	mixed							Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
	protected function _ReportAdjustmentSummary()
	{
		$arrCols = Array();
		$arrCols['CreditTotal']			= "SUM(CASE WHEN Nature = 'CR' THEN Amount ELSE 0 END)";
		$arrCols['DebitTotal']			= "SUM(CASE WHEN Nature = 'DR' THEN Amount ELSE 0 END)";
		$arrCols['CreditCount']			= "COUNT(CASE WHEN Nature = 'CR' THEN Id ELSE NULL END)";
		$arrCols['DebitCount']			= "COUNT(CASE WHEN Nature = 'DR' THEN Id ELSE NULL END)";
		$selAdjustmentSummary	= new StatementSelect("Charge", $arrCols, "InvoiceRun = <InvoiceRun>");
		
		$arrCols = Array();
		$arrCols['Nature']				= "Charge.Nature";
		$arrCols['Description']			= "Charge.Description";
		$arrCols['TotalAdjustments']	= "COUNT(Charge.Id)";
		$arrCols['MinCharge']			= "MIN(Charge.Amount)";
		$arrCols['MaxCharge']			= "MAX(Charge.Amount)";
		$arrCols['TotalCharge']			= "SUM(Charge.Amount)";
		$selTypeSummaries	= new StatementSelect("Charge LEFT JOIN Employee ON Employee.Id = Charge.CreatedBy", $arrCols, "Charge.InvoiceRun = <InvoiceRun>", "Charge.Description", NULL, "Charge.Description");
		
		
		// Create Workbook
		$strFilename = "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()))."Adjustment_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Adjustment Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])))." Adjustment Summary for Telco Blue");
		$wksWorksheet->writeString(0, 2, $strPageTitle, $arrFormat['PageTitle']);
		
		$wksWorksheet->writeString(3, 0, "Bill Date"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(4, 0, "Billing Period"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(5, 0, "Invoice Run"		, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(3, 4, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(4, 4, "N/A"				, $arrFormat['Percentage']);
		$wksWorksheet->writeString(5, 4, "N/A"				, $arrFormat['Percentage']);
		
		for ($i = 0; $i <= 4; $i++)
		{
			$wksWorksheet->writeBlank(2, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(7, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(6, $i, $arrFormat['Spacer']);
		}
		
		$wksWorksheet->writeString(2, 2, "This Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 3, "Last Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(2, 4, "% Change"			, $arrFormat['TitleItalic']);
		
		$wksWorksheet->writeString(7, 0, "Adjustment Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString(7, 2, "This Month"			, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 3, "Last Month"			, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString(7, 4, "% Change"				, $arrFormat['TitleItalic']);
		
		// Set Column Widths
		$fltExcelWidthRatio = 5;
		$wksWorksheet->setColumn(0, 0, 9 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(1, 1, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(2, 2, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(3, 3, 3 * $fltExcelWidthRatio);
		$wksWorksheet->setColumn(4, 4, 3 * $fltExcelWidthRatio);
		
		// Create Adjustments Summary		
		$wksWorksheet->writeString(8, 0, "Total Credits"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString(9, 0, "Total Debits"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString(10, 0, "Credit Total Value"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(11, 0, "Debit Total Value"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(12, 0, "Adjustment Total Value"	, $arrFormat['TextBold']);
		$//wksWorksheet->writeString(13, 0, "Mean Credit Value"		, $arrFormat['TextBold']);
		$//wksWorksheet->writeString(14, 0, "Mean Debit Value"		, $arrFormat['TextBold']);
		//$wksWorksheet->writeString(15, 0, "Mean Customer Total"		, $arrFormat['TextBold']);
		
		$intCol = 2;
		foreach ($this->_arrProfitData as $strPeriod=>$arrData)
		{
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['InvoiceRun']);
	
			$selAdjustmentSummary->Execute($arrData);
			$arrAdjustmentSummary = $selAdjustmentSummary->Fetch();
			$arrAdjustmentSummary['AdjustmentTotalValue']	= $arrAdjustmentSummary['DebitTotal'] - $arrAdjustmentSummary['CreditTotal'];
			$arrAdjustmentSummary['MeanCreditValue']		= $arrAdjustmentSummary['CreditTotal'] / $arrAdjustmentSummary['CreditCount'];
			$arrAdjustmentSummary['MeanDebitValue']			= $arrAdjustmentSummary['DebitTotal'] / $arrAdjustmentSummary['DebitCount'];
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
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		// Create Credit/Debit Summaries
		$selTypeSummaries->Execute($this->_arrProfitData['ThisMonth']);
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
		$intRow = 12;
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
			for ($i = 0; $i <= 4; $i++)
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
			//$wksWorksheet->writeString($intRow, 4, "Mean $strNoun"		, $arrFormat['ColTitle']);
			$wksWorksheet->writeString($intRow, 4, "Total {$strNoun}ed"	, $arrFormat['ColTitle']);
			
			foreach ($arrTypes as $arrType)
			{
				// Add to Worksheet
				$intRow++;
				$wksWorksheet->writeString($intRow, 0, $arrType['Description']);
				$wksWorksheet->writeNumber($intRow, 1, $arrType['TotalAdjustments']	, $arrFormat['Integer']);
				$wksWorksheet->writeNumber($intRow, 2, $arrType['MinCharge']		, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intRow, 3, $arrType['MaxCharge']		, $arrFormat['Currency']);
				//$wksWorksheet->writeNumber($intRow, 4, $arrType['MeanCharge']		, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intRow, 4, $arrType['TotalCharge']		, $arrFormat['Currency']);
			}
		}
		
		// Close Workbook
		for ($i = 0; $i <= 4; $i++)
		{
			$wksWorksheet->writeBlank($intRow+1, $i, $arrFormat['BlankOverline']);
		}
		$wkbWorkbook->close();
		chmod($strFilename, 0777);
		
		return Array($strFilename);
	}
	
	
	//------------------------------------------------------------------------//
	// _ReportCustomerSummary()
	//------------------------------------------------------------------------//
	/**
	 * _ReportCustomerSummary()
	 *
	 * Create a Customer Summary Management Report and return the path to the file
	 *
	 * Create a Customer Summary Management Report and return the path to the file
	 *
	 * @return	mixed							Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
	protected function _ReportCustomerSummary()
	{
		$selServiceCount	= new StatementSelect(	"(ServiceTotal JOIN Service ON Service.Id = ServiceTotal.Service) JOIN Account ON Account.Id = Service.Account",
													"Service.Account, COUNT(ServiceTotal.Id) AS ServiceCount",
													"InvoiceRun = <InvoiceRun> AND (Service.Status = ".SERVICE_ACTIVE." OR Service.ClosedOn < <BillingDate>) AND Account.Archived = 0",
													"ServiceCount",
													NULL,
													"Service.Account");
		
		$selInvoiceTemp 	= new StatementSelect("InvoiceTemp", "Id", "InvoiceRun = <InvoiceRun>");
		$selCustomersGained	= new StatementSelect("Account", "Id", "CreatedOn BETWEEN <LastBillingDate> AND <BillingDate> AND Archived = 0");
		
		$selBarred			= new StatementSelect(	"Request JOIN Service ON Service.Id = Request.Service",
													"DISTINCT Account",
													"RequestType IN (902, 908) AND RequestDateTime < <BillingDate> AND RequestDateTime >= <LastBillingDate>");
		
		$selTotalBarred		= new StatementSelect(	"Service",
													"DISTINCT Account",
													"ClosedOn IS NULL AND (SELECT RequestType FROM Request WHERE Service = Service.Id AND RequestType IN (902, 903, 908, 909)) IN (902, 908)");
		
		$selCustomerCountTotal	= new StatementSelect(	"Account",
														"Id",
														"Archived = <Archived>");
		
		$selNonArchived		= new StatementSelect(	"Account", "Id", "Archived != 1");
		
		// Create Workbook
		$strFilename = "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()))."Customer_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Customer Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])))." Customer Summary for Telco Blue");
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
			
		$intRow	= $intStartingRow = 8;
		foreach ($GLOBALS['**arrConstant']['Account'] as $intStatus=>$arrProperties)
		{
			$wksWorksheet->writeString($intRow, 0, "Currently {$arrProperties['Description']} Customers", $arrFormat['TextBold']);
			$wksWorksheet->writeString($intRow+1, 0, "New {$arrProperties['Description']} Customers"	, $arrFormat['TextBold']);
			$intRow	+= 2;
		}
		
		$wksWorksheet->writeString($intRow+1, 0, "Total Non-Archived Customers"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intRow+2, 0, "New Customers"						, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intRow+3, 0, "Customers Archived"					, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intRow+4, 0, "Customers Unarchived"					, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intRow+5, 0, "New Barred Customers"					, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intRow+6, 0, "Total Barred Customers"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intRow+7, 0, "Mean Customer Spend (80 Percentile)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intRow+8, 0, "Average Services per Customer"		, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString($intRow+10, 0, "Customers with X Services Summary"	, $arrFormat['Title']);
		$wksWorksheet->writeString($intRow+10, 2, "This Month"							, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString($intRow+10, 3, "Last Month"							, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString($intRow+10, 4, "% of Total"							, $arrFormat['TitleItalic']);
		
		$intCol = 2;
		$arrServiceCount = Array();
		foreach ($this->_arrProfitData as $strBillPeriod=>$arrData)
		{
			// Is it a Temp Invoice?
			if ($selInvoiceTemp->Execute($arrData))
			{
				// InvoiceTemp
				$strTable = "InvoiceTemp";
				$selCustomersLost		= new StatementSelect("Invoice JOIN Account ON Account.Id = Invoice.Account", "Invoice.Id", "Account.Archived = 1 AND Invoice.InvoiceRun = <LastInvoiceRun> AND Invoice.Account NOT IN (SELECT I2.Account FROM InvoiceTemp I2 WHERE I2.InvoiceRun = <InvoiceRun>)");
				$selCustomersOpened		= new StatementSelect("InvoiceTemp", "InvoiceTemp.Id", "InvoiceTemp.InvoiceRun = <InvoiceRun> AND InvoiceTemp.Account NOT IN (SELECT I2.Account FROM Invoice I2 WHERE I2.InvoiceRun = <LastInvoiceRun>)");
				$selTopBottom10			= new StatementSelect("InvoiceTemp", "ROUND(COUNT(Id) / 10) AS Bottom10, ROUND((COUNT(Id) / 100) * 90) AS Top10", "Total != 0 AND InvoiceRun = <InvoiceRun>");
				$selCustomersActive		= new StatementSelect("InvoiceTemp", "Id", "InvoiceRun = <InvoiceRun>");
				$selCustomersArchived	= new StatementSelect("Account", "Id", "Id NOT IN (SELECT Account FROM InvoiceTemp WHERE InvoiceRun = <InvoiceRun>) AND CreatedOn < <BillingDate>");
				$selUnarchived			= new StatementSelect("InvoiceTemp", "Id", "InvoiceRun = <InvoiceRun> AND Account NOT IN (SELECT I2.Account FROM Invoice I2 WHERE I2.InvoiceRun = <LastInvoiceRun>) AND Account IN (SELECT I3.Account FROM Invoice I3 WHERE I3.InvoiceRun != <InvoiceRun>)");
			}
			else
			{
				// Invoice
				$strTable = "Invoice";
				$selCustomersLost		= new StatementSelect("Invoice JOIN Account ON Account.Id = Invoice.Account", "Invoice.Id", "Account.Archived = 1 AND Invoice.InvoiceRun = <LastInvoiceRun> AND Invoice.Account NOT IN (SELECT I2.Account FROM Invoice I2 WHERE I2.InvoiceRun = <InvoiceRun>)");
				$selCustomersOpened		= new StatementSelect("Invoice", "Invoice.Id", "Invoice.InvoiceRun = <InvoiceRun> AND Invoice.Account NOT IN (SELECT I2.Account FROM Invoice I2 WHERE I2.InvoiceRun = <LastInvoiceRun>)");
				$selTopBottom10			= new StatementSelect("Invoice", "ROUND(COUNT(Id) / 10) AS Bottom10, ROUND((COUNT(Id) / 100) * 90) AS Top10", "Total != 0 AND InvoiceRun = <InvoiceRun>");
				$selCustomersActive		= new StatementSelect("Invoice", "Id", "InvoiceRun = <InvoiceRun>");
				$selCustomersArchived	= new StatementSelect("Account", "Id", "Id NOT IN (SELECT Account FROM Invoice WHERE InvoiceRun = <InvoiceRun>) AND CreatedOn < <BillingDate>");
				$selUnarchived			= new StatementSelect("Invoice", "Id", "InvoiceRun = <InvoiceRun> AND Account NOT IN (SELECT I2.Account FROM Invoice I2 WHERE I2.InvoiceRun = <LastInvoiceRun>) AND Account IN (SELECT I3.Account FROM Invoice I3 WHERE I3.InvoiceRun != <InvoiceRun>)");
			}
			
			// Header Data
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['InvoiceRun']);
			
			// Customer Summary Data
			$intCustomersLost		= $selCustomersLost->Execute($arrData);
			$intCustomersGained		= $selCustomersGained->Execute($arrData);
			$intCustomersReOpened	= $selCustomersOpened->Execute($arrData) - $intCustomersGained;
			$intActive				= $selCustomersActive->Execute($arrData);
			$intArchived			= $selCustomersArchived->Execute($arrData);
			$intUnarchived			= $selUnarchived->Execute($arrData);
			$intTotalBarred			= $selTotalBarred->Execute($arrData);
			$intBarred				= $selBarred->Execute($arrData);
			$selNonArchived			= $selNonArchived->Execute();
			
			$intRow	= $intStartingRow;
			foreach ($GLOBALS['**arrConstant']['Account'] as $intStatus=>$arrProperties)
			{
				if ($strBillPeriod == 'ThisMonth')
				{
					$wksWorksheet->writeNumber($intRow, $intCol, $selCustomerCountTotal->Execute(Array('Archived' => $intStatus)), $arrFormat['Integer']);
					$wksWorksheet->writeString($intRow+1, $intCol, "N/A");
				}
				else
				{
					$wksWorksheet->writeString($intRow, $intCol, "N/A");
					$wksWorksheet->writeString($intRow+1, $intCol, "N/A");
				}
				$intRow	+= 2;
			}
			
			$wksWorksheet->writeNumber($intRow+1, $intCol, $intCustomersGained				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber($intRow+2, $intCol, $intCustomersGained				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber($intRow+3, $intCol, $intCustomersLost				, $arrFormat['Integer']);
			$wksWorksheet->writeNumber($intRow+4, $intCol, "N/A"							, $arrFormat['Integer']);
			$wksWorksheet->writeNumber($intRow+5, $intCol, $intBarred						, $arrFormat['Integer']);
			$wksWorksheet->writeNumber($intRow+6, $intCol, $intTotalBarred					, $arrFormat['Integer']);
			
			// 80 Percentile
			$selTopBottom10->Execute($arrData);
			$arrTopBottom10		= $selTopBottom10->Fetch();
			$sel80Percentile	= new StatementSelect($strTable, "Total + Tax AS GrandTotal", "Total != 0 AND InvoiceRun = <InvoiceRun>", "(Total + Tax)", "{$arrTopBottom10['Bottom10']}, {$arrTopBottom10['Top10']}");
			$sel80Percentile->Execute($arrData);
			$arrGrandTotals		= $sel80Percentile->FetchAll();
			
			$fltGrandGrandTotal = 0;
			foreach ($arrGrandTotals as $arrGrandTotal)
			{
				$fltGrandGrandTotal += $arrGrandTotal['GrandTotal'];
			}
			$flt80Percentile = $fltGrandGrandTotal / count($arrGrandTotals);
			
			$wksWorksheet->writeNumber($intRow+7, $intCol, $flt80Percentile					, $arrFormat['Currency']);
			
			
			
			// Service Count Data
			$intAccounts = $selServiceCount->Execute($arrData);
			$arrServices = Array();
			while ($arrCount = $selServiceCount->Fetch())
			{
				$arrServices[$intCol] += $arrCount['ServiceCount'];
				$arrServiceCount[$arrCount['ServiceCount']][$intCol]++;
			}
			
			$wksWorksheet->writeNumber($intRow+8, $intCol, round($arrServices[$intCol] / $intAccounts), 2);
			
			$intCol++;
		}
		
		$intRow	= $intStartingRow+11;
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
		for ($i = $intStartingRow+1; $i <= $intStartingRow+9; $i++)
		{
			$wksWorksheet->writeFormula($i-1, 4, "=IF(AND(C$i <> 0, NOT(C$i = \"N/A\")), (C$i - D$i) / ABS(C$i), \"N/A\")", $arrFormat['Percentage']);
		}
		
		for ($i = $intStartingRow+12; $i <= $intRow; $i++)
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
	
	
	//------------------------------------------------------------------------//
	// _ReportRecurringAdjustmentsSummary()
	//------------------------------------------------------------------------//
	/**
	 * _ReportRecurringAdjustmentsSummary()
	 *
	 * Create a Recurring Adjustments Summary Management Report and return the path to the file
	 *
	 * Create a Recurring Adjustments Summary Management Report and return the path to the file
	 *
	 * @return	mixed							Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
	protected function _ReportRecurringAdjustmentsSummary()
	{
		$selActiveCharges	= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "(RecurringCharge.LastChargedOn > SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND RecurringCharge.LastChargedOn <= <BillingDate>) OR (RecurringCharge.MinCharge > RecurringCharge.TotalCharged AND RecurringCharge.Archived = 0) AND Account.Archived = 0");
		$selTotalCharged	= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "SUM(RecurringCharge.RecursionCharge) AS Total", "(LastChargedOn > SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND LastChargedOn <= <BillingDate>)");
		$selChargeFinished	= new StatementSelect("RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "(LastChargedOn > SUBDATE(<BillingDate>, INTERVAL 1 MONTH) AND LastChargedOn <= <BillingDate>) AND TotalCharged >= MinCharge");
		$selChargeCancelled	= new StatementSelect("(RecurringCharge JOIN Charge USING (Account, ChargeType)) JOIN Account ON Account.Id = RecurringCharge.Account", "RecurringCharge.Id", "Charge.Service <=> RecurringCharge.Service AND Charge.Description = CONCAT('CANCELLATION: ', RecurringCharge.Description) AND InvoiceRun = <InvoiceRun>");
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
		$strFilename = "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()))."Recurring_Adjustment_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Recurring Adjustment Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])))." Recurring Adjustment Summary for Telco Blue");
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
		Debug("Writing Recurring Adjustments Summary...");
		
		$intCol = 8;
		foreach ($this->_arrProfitData as $arrData)
		{
			// Header Data
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['InvoiceRun']);
			
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
		$selBreakdown->Execute($this->_arrProfitData['ThisMonth']);
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
	
	
	//------------------------------------------------------------------------//
	// _ReportAdjustmentsByEmployeeSummary()
	//------------------------------------------------------------------------//
	/**
	 * _ReportAdjustmentsByEmployeeSummary()
	 *
	 * Create a Adjustments By Employee Summary Management Report and return the path to the file
	 *
	 * Create a Adjustments By Employee Summary Management Report and return the path to the file
	 *
	 * @return	mixed							Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
	protected function _ReportAdjustmentsByEmployeeSummary()
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
		$selAdjustments	= new StatementSelect("Charge JOIN Employee ON Charge.CreatedBy = Employee.Id", $arrCols, "InvoiceRun = <InvoiceRun>", "Charge.Nature", NULL, "Charge.CreatedBy, Charge.Description");
		
		// Create Workbook
		$strFilename = "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()))."Adjustments_by_Employee_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Adjustments by Employee Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])))." Adjustment by Employee Summary for Telco Blue");
		$wksWorksheet->writeString(0, 2, $strPageTitle, $arrFormat['PageTitle']);
		
		$wksWorksheet->writeString(3, 0, "Bill Date"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString(4, 0, "Billing Period"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString(5, 0, "Invoice Run"		, $arrFormat['TextBold']);
		
		$wksWorksheet->writeString(3, 6, date("d/m/Y", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])));
		$wksWorksheet->writeString(4, 6, date("F Y", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate']))));
		$wksWorksheet->writeString(5, 6, $this->_arrProfitData['ThisMonth']['InvoiceRun']);
		
		for ($i = 0; $i <= 6; $i++)
		{
			$wksWorksheet->writeBlank(2, $i, $arrFormat['BlankUnderline']);
			$wksWorksheet->writeBlank(6, $i, $arrFormat['Spacer']);
		}
		
		// Breakdown	
		$selAdjustments->Execute($this->_arrProfitData['ThisMonth']);
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
					$strNoun	= "Credit";
					$strAbbr	= 'CR';
				}
				else
				{
					$strNoun	= "Debit";
					$strAbbr	= 'DR';
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
				
				// Add in Grand Totals
				$wksWorksheet->writeBlank($intRow, 0, $arrFormat['LeftSpacer']);
				$wksWorksheet->writeBlank($intRow, 1, $arrFormat['BlankOverline']);
				$wksWorksheet->writeBlank($intRow, 2, $arrFormat['BlankOverline']);
				$wksWorksheet->writeBlank($intRow, 3, $arrFormat['BlankOverline']);
				$wksWorksheet->writeBlank($intRow, 4, $arrFormat['BlankOverline']);
				$wksWorksheet->writeString($intRow, 5, "Grand Total $strAbbr", $arrFormat['OverlineBold']);
				$wksWorksheet->writeNumber($intRow, 6, $arrFormat['BlankOverline']);
				$intRow++;
				
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
	
	
	//------------------------------------------------------------------------//
	// _ReportInvoiceSummary()
	//------------------------------------------------------------------------//
	/**
	 * _ReportInvoiceSummary()
	 *
	 * Create a Invoice Summary Management Report and return the path to the file
	 *
	 * Create a Invoice Summary Management Report and return the path to the file
	 *
	 * @return	mixed							Array of paths to the management report XLS files or FALSE on error
	 *
	 * @method
	 */
	protected function _ReportInvoiceSummary()
	{
		$selInvoiceTemp 		= new StatementSelect("InvoiceTemp", "Id", "InvoiceRun = <InvoiceRun>");
		$selLastInvoiceTotal	= new StatementSelect("InvoiceRun", "BillInvoiced+BillTax AS GrandTotal", "InvoiceRun = <LastInvoiceRun>");
		
		// Create Workbook
		$strFilename = "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()))."Invoice_Summary.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Invoice Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($this->_arrProfitData['ThisMonth']['BillingDate'])))." Invoice Summary for Telco Blue");
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
			$wksWorksheet->writeBlank(29, $i, $arrFormat['BlankOverline']);
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
		
		$intCol = 2;
		foreach ($this->_arrProfitData as $arrData)
		{
			if ($selInvoiceTemp->Execute($arrData))
			{
				$strTable	= "InvoiceTemp";
			}
			else
			{
				$strTable	= "Invoice";
			}
			$arrCols = Array();
			$arrCols['InvoiceCount']	= "COUNT(Id)";
			$arrCols['PostedCount']		= "COUNT(CASE WHEN DeliveryMethod = 0 THEN Id ELSE NULL END)";
			$arrCols['EmailedCount']	= "COUNT(CASE WHEN DeliveryMethod IN (1, 3) THEN Id ELSE NULL END)";
			$arrCols['WithheldCount']	= "COUNT(CASE WHEN DeliveryMethod = 2 THEN Id ELSE NULL END)";
			$arrCols['PostedTotal']		= "SUM(CASE WHEN DeliveryMethod = 0 THEN Total+Tax ELSE 0 END)";
			$arrCols['EmailedTotal']	= "SUM(CASE WHEN DeliveryMethod IN (1, 3) THEN Total+Tax ELSE 0 END)";
			$arrCols['WithheldTotal']	= "SUM(CASE WHEN DeliveryMethod = 2 THEN Total+Tax ELSE 0 END)";
			$selDeliveryBreakdown	= new StatementSelect($strTable, $arrCols, "InvoiceRun = <InvoiceRun>");
			
			// Header
			$wksWorksheet->writeString(3, $intCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString(4, $intCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString(5, $intCol, $arrData['InvoiceRun']);
			
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
		for ($i = 27; $i <= 29; $i++)
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