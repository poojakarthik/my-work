<?php

class HtmlTemplate_Sale_Report extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("sp/sales_report");
		$this->LoadJavascript("date_time_picker_dynamic");
		$this->LoadJavascript("validation");
		$this->LoadJavascript("input_masks");
		
	}
	
	// We don't stick any of the dealer records in DealerControl.  That is handled by javascript
	public function buildDealerControl($strName)
	{
		$strControl = "
<select id='$strName' name='$strName' class='required' multiple='multiple' size='10'></select>
<br />
<input type='checkbox' id='{$strName}ToggleRootLevelDealers' name='{$strName}ToggleRootLevelDealers'></input>
<label for='{$strName}ToggleRootLevelDealers'>Only show dealers with no upline manager</label>
";

		return $strControl;
	}
	
	public function buildDateControl($strName, $strDefaultValue, $strFromYear, $strToYear, $bolIncludeTime=FALSE, $intDefaultYear=NULL, $intDefaultMonth=NULL, $intDefaultDay=NULL, $strFormat=NULL)
	{
		if ($strFormat === NULL)
		{
			$strFormat = ($bolIncludeTime) ? "H:i:s d/m/Y" : "d/m/Y";
		}
		
		$strIncludeTime = $bolIncludeTime ? "true" : "false";
		
		$intDefaultYear		= ($intDefaultYear !== NULL)? $intDefaultYear : date("Y");
		$intDefaultMonth	= ($intDefaultMonth !== NULL)? $intDefaultMonth : date("n");
		$intDefaultDay		= ($intDefaultDay !== NULL)? $intDefaultDay : date("j");
		
		$strControl = "
<input type='text' id='$strName' name='$strName' value='$strDefaultValue' class='required' />
<a href='javascript:DateChooser.showChooser(\"$strName\", {$strFromYear}, {$strToYear}, \"$strFormat\", $strIncludeTime, true, true, $intDefaultYear, $intDefaultMonth, $intDefaultDay);'>
	<img src='img/template/calendar_small.png' width='16' height='16' title='Calendar time picker' />
</a>
";
		return $strControl;
	}
	
	public function buildStatusControl($strName, $arrAllStatuses, $arrStatusesToInclude, $intSize=8)
	{
		$strOptions = "";
		foreach ($arrStatusesToInclude as $intId)
		{
			$strOptions .= "\t<option value='$intId'>". htmlspecialchars($arrAllStatuses[$intId]->name) ."</option>\n";
		}
		$strControl = "
<select id='$strName' name='$strName' size='$intSize' multiple='multiple' class='required'>
$strOptions
</select>
";
		return $strControl;
	}

	public function Render()
	{
		$arrDealers				= $this->mxdDataToRender['Dealers'];
		$arrSortedDealerIds		= $this->mxdDataToRender['SortedDealerIds'];
		$strReportType			= $this->mxdDataToRender['ReportType'];
		$strReportName			= $this->mxdDataToRender['ReportName'];
		$arrSaleStatuses		= $this->mxdDataToRender['SaleStatuses'];
		$arrSaleItemStatuses	= $this->mxdDataToRender['SaleItemStatuses'];
		$strEarliestTimestamp	= $this->mxdDataToRender['EarliestTimestamp'];
		
		$intEarliestYear	= date("Y", strtotime($strEarliestTimestamp));
		$intLatestYear		= date("Y");
		
		
		$strEarliestTimestampFormatted	= date("H:i:s d/m/Y", strtotime($strEarliestTimestamp));
		$strLatestTimestampFormatted	= date("23:59:59 d/m/Y");
		
		$arrConstraints = array();
		
		switch ($strReportType)
		{
			case Sales_Report::REPORT_TYPE_COMMISSIONS:
				$arrConstraints[] = array(	"Label"		=> "Dealers",
											"Control"	=> $this->buildDealerControl("dealers")
										);
				$arrConstraints[] = array(	"Label"		=> "Earliest Verification Time",
											"Control"	=> $this->buildDateControl("earliestTime", $strEarliestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "00:00:00 d/m/Y")
										);
				$arrConstraints[] = array(	"Label"		=> "Latest Verification Time",
											"Control"	=> $this->buildDateControl("latestTime", $strLatestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "23:59:59 d/m/Y")
										);
				break;
				
			case Sales_Report::REPORT_TYPE_OUTSTANDING_SALES:
				$arrStatuses = array(	DO_Sales_SaleStatus::SUBMITTED,
										DO_Sales_SaleStatus::VERIFIED,
										DO_Sales_SaleStatus::AWAITING_DISPATCH,
										DO_Sales_SaleStatus::MANUAL_INTERVENTION,
										DO_Sales_SaleStatus::DISPATCHED
									);
				$arrConstraints[] = array(	"Label"		=> "Current Statuses",
											"Control"	=> $this->buildStatusControl("statuses", $arrSaleStatuses, $arrStatuses, 6)
										);
				break;
				
			case Sales_Report::REPORT_TYPE_SALE_ITEM_SUMMARY:
				$arrConstraints[] = array(	"Label"		=> "Dealers",
											"Control"	=> $this->buildDealerControl("dealers")
										);
				$arrConstraints[] = array(	"Label"		=> "Earliest Verification Time",
											"Control"	=> $this->buildDateControl("earliestTime", $strEarliestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "00:00:00 d/m/Y")
										);
				$arrConstraints[] = array(	"Label"		=> "Latest Verification Time",
											"Control"	=> $this->buildDateControl("latestTime", $strLatestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "23:59:59 d/m/Y")
										);
				break;
				
			case Sales_Report::REPORT_TYPE_SALE_ITEM_STATUS:
				$arrStatuses = array(	DO_Sales_SaleItemStatus::VERIFIED,
										DO_Sales_SaleItemStatus::AWAITING_DISPATCH,
										DO_Sales_SaleItemStatus::MANUAL_INTERVENTION,
										DO_Sales_SaleItemStatus::DISPATCHED,
										DO_Sales_SaleItemStatus::COMPLETED,
										DO_Sales_SaleItemStatus::CANCELLED
									);
				$arrConstraints[] = array(	"Label"		=> "Status changes to track",
											"Control"	=> $this->buildStatusControl("statuses", $arrSaleItemStatuses, $arrStatuses, 7)
										);
				$arrConstraints[] = array(	"Label"		=> "Earliest Time of Change",
											"Control"	=> $this->buildDateControl("earliestTime", $strEarliestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "00:00:00 d/m/Y")
										);
				$arrConstraints[] = array(	"Label"		=> "Latest Time of Change",
											"Control"	=> $this->buildDateControl("latestTime", $strLatestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "23:59:59 d/m/Y")
										);
				break;
				
			case Sales_Report::REPORT_TYPE_SALE_ITEM_HISTORY:
				$arrStatuses = array(	DO_Sales_SaleItemStatus::SUBMITTED,
										DO_Sales_SaleItemStatus::REJECTED,
										DO_Sales_SaleItemStatus::VERIFIED,
										DO_Sales_SaleItemStatus::AWAITING_DISPATCH,
										DO_Sales_SaleItemStatus::MANUAL_INTERVENTION,
										DO_Sales_SaleItemStatus::DISPATCHED,
										DO_Sales_SaleItemStatus::COMPLETED,
										DO_Sales_SaleItemStatus::CANCELLED
									);
				$arrConstraints[] = array(	"Label"		=> "Status changes to track",
											"Control"	=> $this->buildStatusControl("statuses", $arrSaleItemStatuses, $arrStatuses, 9)
										);
				$arrConstraints[] = array(	"Label"		=> "Earliest Time of Change",
											"Control"	=> $this->buildDateControl("earliestTime", $strEarliestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "00:00:00 d/m/Y")
										);
				$arrConstraints[] = array(	"Label"		=> "Latest Time of Change",
											"Control"	=> $this->buildDateControl("latestTime", $strLatestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "23:59:59 d/m/Y")
										);
				break;
				
			case Sales_Report::REPORT_TYPE_SALE_HISTORY:
				$arrStatuses = array(	DO_Sales_SaleStatus::SUBMITTED,
										DO_Sales_SaleStatus::REJECTED,
										DO_Sales_SaleStatus::VERIFIED,
										DO_Sales_SaleStatus::AWAITING_DISPATCH,
										DO_Sales_SaleStatus::MANUAL_INTERVENTION,
										DO_Sales_SaleStatus::DISPATCHED,
										DO_Sales_SaleStatus::COMPLETED,
										DO_Sales_SaleStatus::CANCELLED
									);
				$arrConstraints[] = array(	"Label"		=> "Status changes to track",
											"Control"	=> $this->buildStatusControl("statuses", $arrSaleStatuses, $arrStatuses, 9)
										);
				$arrConstraints[] = array(	"Label"		=> "Earliest Time of Change",
											"Control"	=> $this->buildDateControl("earliestTime", $strEarliestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "00:00:00 d/m/Y")
										);
				$arrConstraints[] = array(	"Label"		=> "Latest Time of Change",
											"Control"	=> $this->buildDateControl("latestTime", $strLatestTimestampFormatted, $intEarliestYear, $intLatestYear, FALSE, NULL, NULL, NULL, "23:59:59 d/m/Y")
										);
				break;
		}

		$jsonDealers			= JSON_Services::encode($arrDealers);
		$jsonSortedDealerIds	= JSON_Services::encode($arrSortedDealerIds);
		

echo "
<form id='FormReportVariables'>
	<table class='reflex'>
		<caption>
			<div id='caption_bar' name='caption_bar'>
				<div id='caption_title' name='caption_title'>
					$strReportName
				</div>
				<div id='caption_options' name='caption_options'>
				</div>
			</div>
		</caption>
		<thead class='header'>
			<tr>
				<th colspan='2'>Constraints</th>
			</tr>
		</thead>
		<tbody>";
		foreach ($arrConstraints as $arrConstraint)
		{
echo "
			<tr class='alt'>
				<td class='title'>{$arrConstraint['Label']}</td>
				<td>{$arrConstraint['Control']}</td>
			</tr>";
		}
echo "		
		</tbody>
		<tfoot class='footer'>
			<tr>
				<th colspan='2'>
					<input type='button' id='buildReport' name='buildReport' class='reflex-button' value='Build Report' />
				</th>
			</tr>
		</tfoot>
	</table>
</form>

<script type='text/javascript'>FlexSalesReport.initialise('$strReportType', '$strReportName', $jsonDealers, $jsonSortedDealerIds)</script>
";



	}
}

?>
