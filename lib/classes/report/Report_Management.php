<?php
//----------------------------------------------------------------------------//
// Report_Management
//----------------------------------------------------------------------------//
/**
 * Report_Management
 *
 * Management Report Base Class and Handler
 *
 * @class	Report_Management
 */
abstract class Report_Management
{
	//------------------------------------------------------------------------//
	// run
	//------------------------------------------------------------------------//
	/**
	 * run()
	 *
	 * Runs the Management Report for a given Invoice Run
	 *
	 * @param	Invoice_Run	objInvoiceRun					The Invoice Run who's Management Reports we're generating
	 *
	 * @return	void
	 *
	 * @constructor
	 */
 	public static abstract function run($arrProfitData, $strReportBasePath, $strCustomerName);
 	
	//------------------------------------------------------------------------//
	// runAll
	//------------------------------------------------------------------------//
	/**
	 * runAll()
	 *
	 * Runs the Management Reports for a given Invoice Run
	 *
	 * @param	Invoice_Run	objInvoiceRun					The Invoice Run who's Management Reports we're generating
	 *
	 * @return	void
	 *
	 * @constructor
	 */
 	public static function runAll(Invoice_Run $objInvoiceRun)
 	{
 		// Retrieve Profit Data
 		$arrProfitData	= self::_retrieveProfitData($objInvoiceRun);
		
		// Customer Name
 		$strCustomerName	= GetConstantDescription($objInvoiceRun->customer_group_id, 'CustomerGroup');
		Cli_App_Billing::debug("Customer Name\t: {$strCustomerName}");
 		
 		// Billing Period
		$strBillingPeriodEndMonth	= date("F", strtotime("-1 day", strtotime($objInvoiceRun->BillingDate)));
		$strBillingPeriodEndYear	= date("Y", strtotime("-1 day", strtotime($objInvoiceRun->BillingDate)));
		$strBillingPeriodStartMonth	= date("F", strtotime("-1 month", strtotime($objInvoiceRun->BillingDate)));
		$strBillingPeriodStartYear	= date("Y", strtotime("-1 month", strtotime($objInvoiceRun->BillingDate)));
		$strBillingPeriodMonth		= date("m", strtotime("-1 month", strtotime($objInvoiceRun->BillingDate)));
		$strBillingPeriodYear		= date("Y", strtotime("-1 month", strtotime($objInvoiceRun->BillingDate)));
		
		$strBillingPeriod				= $strBillingPeriodStartMonth;
		
		if ($strBillingPeriodStartYear !== $strBillingPeriodEndYear)
		{
			$strBillingPeriod			.= " {$strBillingPeriodStartYear} / {$strBillingPeriodEndMonth} {$strBillingPeriodEndYear}";
		}
		elseif ($strBillingPeriodStartMonth !== $strBillingPeriodEndMonth)
		{
			$strBillingPeriod			.= " / {$strBillingPeriodEndMonth} {$strBillingPeriodEndYear}";
		}
		else
		{
			$strBillingPeriod			.= " {$strBillingPeriodStartYear}";
		}
		Cli_App_Billing::debug("Billing Period\t: {$strBillingPeriod}");
 		
 		// Base Path
 		$strReportBasePath	= FILES_BASE_PATH."reports/management/{$strBillingPeriodYear}/{$strBillingPeriodMonth}/";
 		$strReportBasePath	.= ($objInvoiceRun->customer_group_id) ? strtolower(str_replace(' ', '', $strCustomerName))."/" : '';
 		@mkdir($strReportBasePath, 0777, TRUE);
		Cli_App_Billing::debug("Output Path\t: {$strReportBasePath}");
 		
 		// Run each Management Report
 		$arrClassFiles	= glob(FLEX_BASE_PATH."lib/classes/report/management/*.php");
 		foreach ($arrClassFiles as $strClassPath)
 		{
 			$strClassName	= basename($strClassPath, '.php');
			Cli_App_Billing::debug("Generating {$strClassName}...");
 			eval("{$strClassName}::run(\$arrProfitData, \$strReportBasePath, \$strCustomerName);");
 		}
 		
 		return;
 	}
	
	//------------------------------------------------------------------------//
	// _retrieveProfitData
	//------------------------------------------------------------------------//
	/**
	 * _retrieveProfitData()
	 *
	 * Retrieves the Profit Data for the given Invoice Run
	 *
	 * @param	Invoice_Run	objInvoiceRun					The Invoice Run who's Management Reports we're generating
	 *
	 * @return	array										Profit Data
	 *
	 * @method
	 */
	private static function _retrieveProfitData(Invoice_Run $objInvoiceRun)
	{
		static	$arrProfitData;
		
		if (!isset($arrProfitData))
		{
			$selProfitData = new StatementSelect("InvoiceRun", "*, Id AS invoice_run_id", "customer_group_id = <customer_group_id> AND BillingDate < <BillingDate>", "BillingDate DESC", 1);
			
			$arrProfitData['ThisMonth']	= $objInvoiceRun->toArray();
			
			$selProfitData->Execute($arrProfitData['ThisMonth']);
			$arrLastMonth				= $selProfitData->Fetch();
			if ($arrLastMonth)
			{
				$arrProfitData['LastMonth']	= $arrLastMonth;
				
				$selProfitData->Execute($arrProfitData['LastMonth']);
				$arrMonthBeforeLast			= $selProfitData->Fetch();
				
				$arrProfitData['ThisMonth']['last_invoice_run_id']	= $arrProfitData['LastMonth']['invoice_run_id'];
				$arrProfitData['ThisMonth']['LastBillingDate']		= $arrProfitData['LastMonth']['BillingDate'];
				
				if ($arrMonthBeforeLast)
				{
					$arrProfitData['LastMonth']['last_invoice_run_id']	= $arrMonthBeforeLast['invoice_run_id'];
					$arrProfitData['LastMonth']['LastBillingDate']		= $arrMonthBeforeLast['BillingDate'];
				}
			}
			
		}
		
		return $arrProfitData;
	}
	
	//------------------------------------------------------------------------//
	// _initExcelFormats
	//------------------------------------------------------------------------//
	/**
	 * _initExcelFormats()
	 *
	 * Initialises Number Formats for Excel Export
	 *
	 * @param	object	$wkbWorkbook						Workbook to create formats for
	 *
	 * @return	array										Associative Array of Formats
	 *
	 * @method
	 */
	protected static function _initExcelFormats($wkbWorkbook)
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
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by Invoice
	 *
	 * Access a Static Cache of Prepared Statements used by Invoice
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	public static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selProfitData':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"InvoiceRun", "*", "customer_group_id <=> <customer_group_id> AND BillingDate < <BillingDate>", "BillingDate DESC", 1);
					break;

				// INSERTS

				// UPDATE BY IDS

				// UPDATES

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>