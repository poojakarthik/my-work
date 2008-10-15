<?php
//----------------------------------------------------------------------------//
// Report_Management_CustomerGroupSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_CustomerGroupSummary
 *
 * Customer Group Summary Management Report
 *
 * @class	Report_Management_CustomerGroupSummary
 */
class Report_Management_CustomerGroupSummary extends Report_Management
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
		//--------------------------------------------------------------------//
		// DATA
		//--------------------------------------------------------------------//
		
		// Statements
		$selCustomerGroups	= new StatementSelect("CustomerGroup", "Id AS CustomerGroup, InternalName");
		
		$selProfitSummary	= new StatementSelect(	"Invoice JOIN Account ON Invoice.Account = Account.Id", 
													"SUM(Invoice.Total) AS TotalInvoiced, SUM(Invoice.Tax) AS TotalTaxed, SUM(Invoice.Total + Invoice.Tax) AS GrandTotalInvoiced",
													"Invoice.invoice_run_id = <invoice_run_id> AND Account.CustomerGroup = <CustomerGroup>");
		
		$selCostSummary		= new StatementSelect(	"ServiceTotal JOIN Account ON Account.Id = ServiceTotal.Account",
													"SUM(UncappedCost + CappedCost) AS TotalCost, SUM(CappedCharge + UncappedCharge) AS TotalRated",
													"invoice_run_id = <invoice_run_id> AND Account.CustomerGroup = <CustomerGroup>");
		
		$selDelivery		= new StatementSelect(	"Invoice JOIN Account ON Account.Id = Invoice.Account",
													"DeliveryMethod, SUM(Invoice.Total) AS RetailValue, COUNT(Invoice.Id) AS InvoiceCount",
													"invoice_run_id = <invoice_run_id> AND CustomerGroup = <CustomerGroup>",
													"DeliveryMethod ASC",
													NULL,
													"DeliveryMethod");
		
		$selDestinations	= new StatementSelect("Account", "DISTINCT State", "1", "State ASC");
		$selDestination		= new StatementSelect(	"Invoice JOIN Account ON Account.Id = Invoice.Account", 
													"Invoice.DeliveryMethod, COUNT(Invoice.Id) AS InvoiceCount, SUM(Invoice.Total) AS RetailValue", 
													"invoice_run_id = <invoice_run_id> AND CustomerGroup = <CustomerGroup> AND State = <State>", 
													"Invoice.DeliveryMethod ASC", 
													NULL, 
													"Invoice.DeliveryMethod");
		
		$arrProfitData['CustomerGroups']	= Array();
		
		// Retrieve list of States
		CliEcho("Getting Destinations...");
		if ($selDestinations->Execute())
		{
			$arrDestinations	= Array();
			while ($arrDestination = $selDestinations->Fetch())
			{
				$arrDestinations[]	= $arrDestination['State'];
			}
			
			// Retrieve Customer Groups
			CliEcho("Getting CustomerGroups...");
			if ($intCustomerGroupCount = $selCustomerGroups->Execute())
			{
				$arrCustomerGroups	= $selCustomerGroups->FetchAll();
				$intColumns			= 1 + ($intCustomerGroupCount * 2) + 1;
				
				// Get Data grouped by Invoice Run
				foreach ($arrProfitData as $strPeriod=>&$arrProfitData)
				{					
					// Get Customer Group data
					foreach ($arrCustomerGroups as $arrCustomerGroup)
					{
						CliEcho("++ {$arrCustomerGroup['InternalName']} ++");
						
						// Get Profit Summary
						CliEcho("Getting Profit Summary...");
						$selProfitSummary->Execute(Array('invoice_run_id' => $arrProfitData['invoice_run_id'], 'CustomerGroup' => $arrCustomerGroup['CustomerGroup']));
						$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['ProfitSummary']	= $selProfitSummary->Fetch();
						
						// Get Cost Summary
						CliEcho("Getting Cost Summary...");
						$selCostSummary->Execute(Array('invoice_run_id' => $arrProfitData['invoice_run_id'], 'CustomerGroup' => $arrCustomerGroup['CustomerGroup']));
						$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['ProfitSummary']	= array_merge($arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['ProfitSummary'], $selCostSummary->Fetch());
						
						// Get Delivery Summary
						CliEcho("Getting Delivery Summary...");
						$selDelivery->Execute(Array('invoice_run_id' => $arrProfitData['invoice_run_id'], 'CustomerGroup' => $arrCustomerGroup['CustomerGroup']));
						while ($arrDeliveryMethod = $selDelivery->Fetch())
						{
							switch ($arrDeliveryMethod['DeliveryMethod'])
							{
								case DELIVERY_METHOD_POST:
									$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['DeliverySummary']['PostTotal']		+= $arrDeliveryMethod['RetailValue'];
									$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['DeliverySummary']['PostCount']		+= $arrDeliveryMethod['InvoiceCount'];
									break;
								
								case DELIVERY_METHOD_DO_NOT_SEND:
									$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['DeliverySummary']['WithheldTotal']	+= $arrDeliveryMethod['RetailValue'];
									$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['DeliverySummary']['WithheldCount']	+= $arrDeliveryMethod['InvoiceCount'];
									break;
								
								case DELIVERY_METHOD_EMAIL:
								case DELIVERY_METHOD_EMAIL_SENT:
									$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['DeliverySummary']['EmailTotal']		+= $arrDeliveryMethod['RetailValue'];
									$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['DeliverySummary']['EmailCount']		+= $arrDeliveryMethod['InvoiceCount'];
									break;
							}
						}
						
						// Get Destination Summaries
						foreach ($arrDestinations as $strState)
						{
							CliEcho("Getting Destination Summary for {$strState}...");
							$selDestination->Execute(Array('State' => $strState, 'invoice_run_id' => $arrProfitData['invoice_run_id'], 'CustomerGroup' => $arrCustomerGroup['CustomerGroup']));
							while ($arrDestination = $selDestination->Fetch())
							{
								switch ($arrDestination['DeliveryMethod'])
								{
									case DELIVERY_METHOD_POST:
										$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['Destinations'][$strState]['PostTotal']		+= $arrDestination['RetailValue'];
										$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['Destinations'][$strState]['PostCount']		+= $arrDestination['InvoiceCount'];
										break;
									
									case DELIVERY_METHOD_DO_NOT_SEND:
										$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['Destinations'][$strState]['WithheldTotal']	+= $arrDestination['RetailValue'];
										$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['Destinations'][$strState]['WithheldCount']	+= $arrDestination['InvoiceCount'];
										break;
									
									case DELIVERY_METHOD_EMAIL:
									case DELIVERY_METHOD_EMAIL_SENT:
										$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['Destinations'][$strState]['EmailTotal']	+= $arrDestination['RetailValue'];
										$arrProfitData['CustomerGroups'][$arrCustomerGroup['CustomerGroup']]['Destinations'][$strState]['EmailCount']	+= $arrDestination['InvoiceCount'];
										break;
								}
							}
						}
					}
				}
			}
			else
			{
				// No CustomerGroups found
				Debug($selCustomerGroups->Error());
				return FALSE;
			}
		}
		else
		{
			// Destinations not found
			Debug($selDestinations->Error());
			return FALSE;
		}
		
		//--------------------------------------------------------------------//
		// OUTPUT
		//--------------------------------------------------------------------//
		
		// Create Workbook
		$strFilename = $strReportBasePath."Customer_Group.xls";
		@unlink($strFilename);
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
		
		// Init formats
		$arrFormat = self::_initExcelFormats($wkbWorkbook);
		
		// Create Worksheet & Header
		$wksWorksheet =& $wkbWorkbook->addWorksheet("Customer Group Summary");
		$wksWorksheet->setLandscape();
		$wksWorksheet->hideGridlines();
		$wksWorksheet->fitToPages(1, 99);
		
		$strPageTitle	= strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Customer Group Summary for {$strCustomerName}");
		
		$arrOutline	= Array();
		$intLine	= 0;
		
		// Headings
		$wksWorksheet->writeString($intLine++, 2, $strPageTitle, $arrFormat['PageTitle']);
		
		// Invoice Run Details
		$arrOutline['BlankUnderline']	[]	= Array('LineNumber' => $intLine, 'ColStart' => 0, 'ColEnd' => $intColumns-1);
		$wksWorksheet->writeString($intLine, $intColumns-3, "This Month"		, $arrFormat['TitleItalic']);
		$wksWorksheet->writeString($intLine++, $intColumns-2, "Last Month"		, $arrFormat['TitleItalic']);
		
		$intHeaderLine	= $intLine;
		$wksWorksheet->writeString($intLine++, 0, "Bill Date"		, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Billing Period"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Invoice Run"		, $arrFormat['TextBold']);
		
		$arrOutline['Spacer']			[]	= Array('LineNumber' => $intLine++, 'ColStart' => 0, 'ColEnd' => $intColumns-1);
		$arrCustomerGroupHeaders		[]	= $intLine;
		$wksWorksheet->writeString($intLine++, 0, "Invoice Profit Summary"	, $arrFormat['Title']);
		
		$intProfitSummaryLine			= $intLine;
		$wksWorksheet->writeString($intLine++, 0, "Total Cost"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Total Rated"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Total Invoiced (ex Tax)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Total Taxed"				, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Total Invoiced (inc Tax)", $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Gross Profit (ex Tax)"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Profit Margin"			, $arrFormat['TextBold']);
		
		$arrOutline['Spacer']			[]	= Array('LineNumber' => $intLine++, 'ColStart' => 0, 'ColEnd' => $intColumns-1);
		$arrCustomerGroupHeaders		[]	= $intLine;
		$wksWorksheet->writeString($intLine++, 0, "Invoice Delivery Summary"	, $arrFormat['Title']);
		
		$intInvoiceDeliveryLine			= $intLine;
		$wksWorksheet->writeString($intLine++, 0, "Total Invoices Posted"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Total Invoices Emailed"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Total Invoices Withheld"			, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Posted Invoices Retail Value"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Emailed Invoices Retail Value"	, $arrFormat['TextBold']);
		$wksWorksheet->writeString($intLine++, 0, "Withheld Invoices Retail Value"	, $arrFormat['TextBold']);
		
		$arrDestinationLine	= Array();
		foreach ($arrDestinations as $strState)
		{
			$arrOutline['Spacer']			[]	= Array('LineNumber' => $intLine++, 'ColStart' => 0, 'ColEnd' => $intColumns-1);
			$arrCustomerGroupHeaders		[]	= $intLine;
			$wksWorksheet->writeString($intLine++, 0, "Invoice Destination: {$strState}"	, $arrFormat['Title']);
			
			$arrDestinationLine[$strState]	= $intLine;
			$wksWorksheet->writeString($intLine++, 0, "Total Invoices Posted"			, $arrFormat['TextBold']);
			$wksWorksheet->writeString($intLine++, 0, "Total Invoices Emailed"			, $arrFormat['TextBold']);
			$wksWorksheet->writeString($intLine++, 0, "Total Invoices Withheld"			, $arrFormat['TextBold']);
		}
		
		$arrOutline['BlankOverline']	[]	= Array('LineNumber' => $intLine, 'ColStart' => 0, 'ColEnd' => $intColumns-1);
		
		// Draw Spacers/Formatting
		foreach ($arrOutline as $strFormat=>$arrLines)
		{
			foreach ($arrLines as $arrLine)
			{
				for ($i = $arrLine['ColStart']; $i < $arrLine['ColEnd']; $i++)
				{
					$wksWorksheet->writeBlank($arrLine['LineNumber'], $i, $arrFormat[$strFormat]);
				}
			}
		}
		
		// CustomerGroup headers
		foreach ($arrCustomerGroupHeaders as $intLine)
		{
			$intCol	= 1;
			foreach ($arrCustomerGroups as $arrCustomerGroup)
			{
				foreach (Array("This Month", "Last Month") as $strPeriod)
				{
					$wksWorksheet->writeString($intLine, $intCol++, $arrCustomerGroup['InternalName']." ({$strPeriod})", $arrFormat['TitleItalic']);
				}
			}
		}
		
		
		$intFirstCol	= 1;
		$intColInit		= $intFirstCol;
		$intColJump		= 2;
		$intHeaderCol	= $intColumns - 3;
		$arrLetters		= range('A', 'Z');
		foreach ($arrProfitData as $strPeriod=>$arrData)
		{
			// Header
			$intLine	= $intHeaderLine;
			$wksWorksheet->writeString($intLine++, $intHeaderCol, date("d/m/Y", strtotime($arrData['BillingDate'])));
			$wksWorksheet->writeString($intLine++, $intHeaderCol, date("F Y", strtotime("-1 month", strtotime($arrData['BillingDate']))));
			$wksWorksheet->writeString($intLine++, $intHeaderCol, $arrData['invoice_run_id']);
			
			// Content
			$intCol	= $intColInit;
			foreach ($arrData['CustomerGroups'] as $arrCustomerGroup)
			{
				// Profit Summary
				$intLine			= $intProfitSummaryLine;
				$arrProfitSummary	= $arrCustomerGroup['ProfitSummary'];
				$fltProfit			= $arrProfitSummary['TotalInvoiced'] - $arrProfitSummary['TotalCost'];
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrProfitSummary['TotalCost']			, $arrFormat['Currency']);
				$intCostRow		= $intLine;
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrProfitSummary['TotalRated']			, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrProfitSummary['TotalInvoiced']		, $arrFormat['Currency']);
				$intInvoicedRow	= $intLine;
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrProfitSummary['TotalTaxed']			, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrProfitSummary['GrandTotalInvoiced']	, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $fltProfit								, $arrFormat['Currency']);
				
				$strColumn		= $arrLetters[$intCol];
				$wksWorksheet->writeFormula($intLine++, $intCol, "=IF(AND({$strColumn}{$intInvoicedRow} <> 0, NOT({$strColumn}{$intInvoicedRow} = \"N/A\")), ({$strColumn}{$intInvoicedRow} - {$strColumn}{$intCostRow}) / ABS({$strColumn}{$intInvoicedRow}), \"N/A\")", $arrFormat['Percentage']);
				
				// Delivery Summary
				$intLine		= $intInvoiceDeliveryLine;
				$arrDelivery	= $arrCustomerGroup['DeliverySummary'];
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrDelivery['PostCount']		, $arrFormat['Integer']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrDelivery['EmailCount']		, $arrFormat['Integer']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrDelivery['WithheldCount']	, $arrFormat['Integer']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrDelivery['PostTotal']		, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrDelivery['EmailTotal']		, $arrFormat['Currency']);
				$wksWorksheet->writeNumber($intLine++, $intCol, $arrDelivery['WithheldTotal']	, $arrFormat['Currency']);
				
				// Destination Summary
				foreach ($arrDestinations as $strState)
				{
					$intLine	= $arrDestinationLine[$strState];
					$wksWorksheet->writeNumber($intLine++, $intCol, $arrCustomerGroup['Destinations'][$strState]['PostCount']		, $arrFormat['Integer']);
					$wksWorksheet->writeNumber($intLine++, $intCol, $arrCustomerGroup['Destinations'][$strState]['EmailCount']		, $arrFormat['Integer']);
					$wksWorksheet->writeNumber($intLine++, $intCol, $arrCustomerGroup['Destinations'][$strState]['WithheldCount']	, $arrFormat['Integer']);
				}
				
				$intCol	+= $intColJump;
			}
			
			$intColInit++;
			$intHeaderCol++;
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