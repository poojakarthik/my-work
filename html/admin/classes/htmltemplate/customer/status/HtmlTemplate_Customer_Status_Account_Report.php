<?php

class HtmlTemplate_Customer_Status_Account_Report extends FlexHtmlTemplate
{
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript("customer_status_reports");
	}
	
	public function Render()
	{
		// Build list of Customer Groups
		$strCustomerGroupOptions = "";
		foreach ($this->mxdDataToRender['CustomerGroups'] as $arrCustomerGroup)
		{
			$strCustomerGroupOptions .= "<option value='{$arrCustomerGroup['Id']}' selected='selected'>". htmlspecialchars($arrCustomerGroup['Name']) ."</option>\n";
		}
		
		// Build list of Customer Statuses
		$strCustomerStatusOptions = "";
		foreach ($this->mxdDataToRender['CustomerStatuses'] as $arrCustomerStatus)
		{
			$strCustomerStatusOptions .= "<option value='{$arrCustomerStatus['Id']}' selected='selected'>". htmlspecialchars($arrCustomerStatus['Name']) ."</option>\n";
		}
		
		// Build list of Invoice Runs
		$strInvoiceRunOptions = "";
		foreach ($this->mxdDataToRender['InvoiceRuns'] as $arrInvoiceRun)
		{
			$strCustomerGroup = ($arrInvoiceRun['CustomerGroup'] !== NULL)? htmlspecialchars($arrInvoiceRun['CustomerGroup']) : "All Customer Groups";
			$strName = date("d/m/Y", strtotime($arrInvoiceRun['BillingDate'])). " - Id: {$arrInvoiceRun['Id']} - $strCustomerGroup";
			$strInvoiceRunOptions .= "<option value='{$arrInvoiceRun['Id']}'>$strName</option>\n";
		}

echo "
<form id='FormReportVariables'>
	<table class='reflex'>
		<caption>
			<div id='caption_bar' name='caption_bar'>
				<div id='caption_title' name='caption_title'>
					Customer Status Account Report
				</div>
				<div id='caption_options' name='caption_options'>
				</div>
			</div>
		</caption>
		<thead class='header'>
			<tr>
				<th colspan='2'>Boundary Conditions</th>
			</tr>
		</thead>
		<tbody>
			<tr class='alt'>
				<td class='title'>Customer Groups: </td>
				<td>
					<select id='CustomerGroups' name='CustomerGroups' size='8' multiple='multiple' class='required'>$strCustomerGroupOptions</select>
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Customer Statuses: </td>
				<td>
					<select id='CustomerStatuses' name='CustomerStatuses' size='8' multiple='multiple' class='required'>$strCustomerStatusOptions</select>
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Invoice Runs: </td>
				<td>
					<select id='InvoiceRuns' name='InvoiceRuns' size='8' class='required'>$strInvoiceRunOptions</select>
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Output: </td>
				<td>
					<select id='RenderMode' name='RenderMode'>
						<option id='excel' selected='selected' value='excel'>Excel</option>
					</select>
				</td>
			</tr>
		</tbody>
		<tfoot class='footer'>
			<tr>
				<th colspan='2'>
					<input type='button' class='reflex-button' value='Submit' onclick='Flex.CustomerStatusReports.RunAccountReport()'/>
				</th>
			</tr>
		</tfoot>
	</table>
</form>

<!-- Inline report results go here -->
<div id='ReportResultsContainer' style='margin-bottom: 15em'>
</div>
<script type='text/javascript'>Flex.CustomerStatusReports.InitialiseAccountReport()</script>

";

	}
}

?>
