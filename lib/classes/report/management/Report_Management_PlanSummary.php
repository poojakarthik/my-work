<?php
//----------------------------------------------------------------------------//
// Report_Management_PlanSummary
//----------------------------------------------------------------------------//
/**
 * Report_Management_PlanSummary
 *
 * Plan Summary Management Report
 *
 * @class	Report_Management_PlanSummary
 */
class Report_Management_PlanSummary extends Report_Management
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
		// Statements
		$arrCols = Array();
		$arrCols['CurrentCustomers']	= "COUNT(DISTINCT ServiceTotal.Account)";
		$arrCols['CurrentServices']		= "COUNT(ServiceTotal.Id)";
		$arrCols['MeanCustomerSpend']	= "AVG(TotalCharge)";
		$arrCols['TotalCost']			= "SUM(ServiceTotal.CappedCost + ServiceTotal.UncappedCost)";
		$arrCols['TotalRated']			= "SUM(ServiceTotal.CappedCharge + ServiceTotal.UncappedCharge)";
		$arrCols['TotalBilled']			= "SUM(TotalCharge)";
		$selPlanSummary = new StatementSelect(	"Service JOIN ServiceTotal ON Service.Id = ServiceTotal.Service",
												$arrCols,
												"ServiceTotal.RatePlan = <RatePlan> AND ServiceTotal.invoice_run_id = <invoice_run_id>");
		$selServicesLost	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.invoice_run_id = <last_invoice_run_id> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.invoice_run_id = <invoice_run_id> AND ST2.RatePlan = <RatePlan>) AND ST.RatePlan = <RatePlan>");
		$selServicesGained	= new StatementSelect("ServiceTotal ST", "ST.Id", "ST.invoice_run_id = <invoice_run_id> AND ST.Service NOT IN (SELECT ST2.Service FROM ServiceTotal ST2 WHERE ST2.invoice_run_id = <last_invoice_run_id> AND ST2.RatePlan = <RatePlan>) AND ST.RatePlan = <RatePlan>");
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
		$selCallSummary	= new StatementSelect("CDR JOIN ServiceTotal ON ServiceTotal.Service = CDR.Service", $arrCols, "ServiceTotal.RatePlan = <RatePlan> AND CDR.invoice_run_id = <invoice_run_id> AND ServiceTotal.invoice_run_id = <invoice_run_id> AND CDR.RecordType = <RecordType> AND CDR.Credit = 0");
		
		$selCallCredits	= new StatementSelect("CDR JOIN ServiceTotal ON ServiceTotal.Service = CDR.Service", $arrCols, "ServiceTotal.RatePlan = <RatePlan> AND CDR.invoice_run_id = <invoice_run_id> AND ServiceTotal.invoice_run_id = <invoice_run_id> AND CDR.Credit = 1");
		
		$selRatePlans	= new StatementSelect("(RatePlan JOIN ServiceTotal ON RatePlan.Id = ServiceTotal.RatePlan) JOIN Account ON Account.Id = ServiceTotal.Account", "DISTINCT RatePlan.*", "CustomerGroup = <customer_group_id>", "RatePlan.ServiceType");
		$selMeanSpend	= new StatementSelect("ServiceTypeTotal STT JOIN ServiceTotal ST USING (invoice_run_id, Service)", "AVG(STT.Charge) AS MeanServiceSpend", "ST.invoice_run_id = <invoice_run_id> AND ST.RatePlan = <RatePlan> AND STT.RecordType = <RecordType>");
		
		$selMeanCredit	= new StatementSelect("CDR JOIN ServiceTotal ST USING (invoice_run_id, Service)", "SUM(CDR.Charge) AS TotalServiceCredit", "ST.invoice_run_id = <invoice_run_id> AND ST.RatePlan = <RatePlan> AND CDR.RecordType = <RecordType> AND CDR.Credit = 1");
		
		// For each non-Archived RatePlan
		$intPlanCount = $selRatePlans->Execute($arrProfitData['ThisMonth']);
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
			$strServiceType	= preg_replace("/\W+/misU", "_", GetConstantDescription($intServiceType, 'service_type'));
			$strFilename	= $strReportBasePath."Plan_Summary_with_Breakdown_($strServiceType).xls";
			$arrFilenames[]	= $strFilename;
			@unlink($strFilename);
			$wkbWorkbook = new Spreadsheet_Excel_Writer($strFilename);
			
			// Init formats
			$arrFormat = self::_initExcelFormats($wkbWorkbook);
			
			foreach ($arrRatePlans as $arrRatePlan)
			{
				$intI++;
				
				// Add new Worksheet
				$strWorksheet = substr(preg_replace("/\W+/misU", "_", $arrRatePlan['Name']), 0, 31);
				Cli_App_Billing::debug($strWorksheet);
				$wksWorksheet =& $wkbWorkbook->addWorksheet($strWorksheet);
				
				if (PEAR::isError($wksWorksheet))
				{
					throw new Exception($wksWorksheet->toString());
				}
				
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
				
				// Write Worksheet Header
				$strPageTitle = strtoupper(date("F", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate'])))." Plan Summary for {$strCustomerName}");
				$wksWorksheet->writeString(0, 4, $strPageTitle, $arrFormat['PageTitle']);
				
				for ($i = 0; $i <= 9; $i++)
				{
					$wksWorksheet->writeBlank(1, $i, $arrFormat['BlankUnderline']);
					$wksWorksheet->writeBlank(5, $i, $arrFormat['Spacer']);
				}
				
				$wksWorksheet->writeString(2, 0, "Customer Group"	, $arrFormat['TextBold']);
				$wksWorksheet->writeString(3, 0, "Service Type"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(4, 0, "Plan"				, $arrFormat['TextBold']);
				$wksWorksheet->writeString(2, 8, "Bill Date"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(3, 8, "Billing Period"	, $arrFormat['TextBold']);
				$wksWorksheet->writeString(4, 8, "Invoice Run"		, $arrFormat['TextBold']);
				
				$wksWorksheet->writeString(2, 1, ($arrRatePlan['CustomerGroup'] === NULL) ? 'All' : Customer_Group::getForId($arrRatePlan['CustomerGroup'])->externalName);
				$wksWorksheet->writeString(3, 1, GetConstantDescription($arrRatePlan['ServiceType'], 'service_type'));
				$wksWorksheet->writeString(4, 1, $arrRatePlan['Description']);
				$wksWorksheet->writeString(2, 9, date("d/m/Y", strtotime($arrProfitData['ThisMonth']['BillingDate'])));
				$wksWorksheet->writeString(3, 9, date("F Y", strtotime("-1 month", strtotime($arrProfitData['ThisMonth']['BillingDate']))));
				$wksWorksheet->writeString(4, 9, $arrProfitData['ThisMonth']['invoice_run_id']);
				
				$wksWorksheet->writeString(6, 0, "Plan Summary"		, $arrFormat['Title']);
				$wksWorksheet->writeString(6, 7, "This Month"		, $arrFormat['TitleItalic']);
				$wksWorksheet->writeString(6, 8, "Last Month"		, $arrFormat['TitleItalic']);
				$wksWorksheet->writeString(6, 9, "% Change"			, $arrFormat['TitleItalic']);
				
				for ($i = 1; $i <= 6; $i++)
				{
					$wksWorksheet->writeBlank(6, $i, $arrFormat['BlankUnderline']);
				}
				
				$wksWorksheet->writeString(7, 0, "Current Customers"	, $arrFormat['TextBold']);
				$wksWorksheet->writeString(8, 0, "Current Services"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(9, 0, "Services Gained"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(10, 0, "Services Lost"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(11, 0, "Mean Customer Spend"	, $arrFormat['TextBold']);
				$wksWorksheet->writeString(12, 0, "Total Cost"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(13, 0, "Total Rated"			, $arrFormat['TextBold']);
				$wksWorksheet->writeString(14, 0, "Total Billed"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(15, 0, "Total Profit"		, $arrFormat['TextBold']);
				$wksWorksheet->writeString(16, 0, "Profit Margin"		, $arrFormat['TextBold']);
				
				for ($i = 0; $i <= 9; $i++)
				{
					$wksWorksheet->writeBlank(17, $i, $arrFormat['Spacer']);
				}
				
				// Write Plan Summary
				$intCol = 7;
				foreach ($arrProfitData as $strMonth=>$arrData)
				{
					// Get Plan Summary Info
					$arrData['RatePlan'] = $arrRatePlan['Id'];
					$selPlanSummary->Execute($arrData);
					$arrPlanSummary = $selPlanSummary->Fetch();
					
					// Services Gained and Lost
					$arrPlanSummary['ServicesLost']		= $selServicesLost->Execute($arrData);
					$arrPlanSummary['ServicesGained']	= $selServicesGained->Execute($arrData);
					
					// Write to Worksheet
					$fltProfit	= $arrPlanSummary['TotalBilled'] - $arrPlanSummary['TotalCost'];
					$wksWorksheet->writeNumber(7	, $intCol, $arrPlanSummary['CurrentCustomers']	, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(8	, $intCol, $arrPlanSummary['CurrentServices']	, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(9	, $intCol, $arrPlanSummary['ServicesGained']	, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(10	, $intCol, $arrPlanSummary['ServicesLost']		, $arrFormat['Integer']);
					$wksWorksheet->writeNumber(11	, $intCol, $arrPlanSummary['MeanCustomerSpend']	, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(12	, $intCol, $arrPlanSummary['TotalCost']			, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(13	, $intCol, $arrPlanSummary['TotalRated']		, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(14	, $intCol, $arrPlanSummary['TotalBilled']		, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber(15	, $intCol, $fltProfit							, $arrFormat['CreditDebit']);
					
					if ($intCol == 7)
					{
						$wksWorksheet->writeFormula(16	, $intCol, "=IF(AND(H15 <> 0, NOT(H15 = \"N/A\")), (H15 - H13) / ABS(H15), \"N/A\")"			, $arrFormat['Percentage']);
					}
					else
					{
						$wksWorksheet->writeFormula(16	, $intCol, "=IF(AND(I15 <> 0, NOT(I15 = \"N/A\")), (I15 - I13) / ABS(I15), \"N/A\")"			, $arrFormat['Percentage']);
					}
					
					$intCol++;
				}
				
				// Write Plan Summary '% Change' Fields
				for ($i = 8; $i <= 17; $i++)
				{
					$wksWorksheet->writeFormula($i-1, 9, "=IF(AND(H$i <> 0, NOT(H$i = \"N/A\")), (H$i - I$i) / ABS(H$i), \"N/A\")", $arrFormat['Percentage']);
				}
				
				// Write Call Type Breakdown
				$wksWorksheet->writeString(18, 0, "Call Type Summary"		, $arrFormat['Title']);
				
				for ($i = 1; $i <= 9; $i++)
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
				$wksWorksheet->writeString($intRow, 9, "Profit Margin"		, $arrFormat['ColTitle']);
			
				$selCallTypes->Execute(Array('RatePlan' => $arrRatePlan['Id']));
				$arrCallTypes	= $selCallTypes->FetchAll();
				$arrCallTypes[]	= Array('Credit' => TRUE, 'Description' => "Call Credits", 'DisplayType' => RECORD_DISPLAY_CALL);
				foreach ($arrCallTypes as $arrCallType)
				{
					$intRow++;
					$i = $intRow + 1;
					
					// Get Call Type Summary
					$arrCallType['invoice_run_id'] = $arrProfitData['ThisMonth']['invoice_run_id'];
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
				
					$wksWorksheet->writeString($intRow, 0, $arrCallType['Description']);
					$wksWorksheet->writeNumber($intRow, 2, $arrCallSummary['MeanCallCost']		, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 3, $arrCallSummary['MeanCallCharge']	, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 4, $arrCallSummary['MeanServiceSpend']	, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 6, $arrCallSummary['TotalCallCount']	, $arrFormat['Integer']);
					$wksWorksheet->writeNumber($intRow, 7, $arrCallSummary['TotalCost']			, $arrFormat['CreditDebit']);
					$wksWorksheet->writeNumber($intRow, 8, $arrCallSummary['TotalCharged']		, $arrFormat['CreditDebit']);
					$wksWorksheet->writeFormula($intRow, 9, "=IF(AND(I$i <> 0, NOT(I$i = \"N/A\")), (I$i - H$i) / ABS(I$i), \"N/A\")", $arrFormat['Percentage']);
					
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
				
				for ($i = 0; $i <= 9; $i++)
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
}
?>