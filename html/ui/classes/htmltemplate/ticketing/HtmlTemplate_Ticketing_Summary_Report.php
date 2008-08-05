<?php

class HtmlTemplate_Ticketing_Summary_Report extends FlexHtmlTemplate
{
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript("ticketing_summary_report");
		$this->LoadJavascript("date_time_picker_xy");
		$this->LoadJavascript("validation");
		$this->LoadJavascript("input_masks");
	}
	
	public function Render()
	{
		// Build list of Ticket Owners
		$strOwnerOptions = "";
		foreach ($this->mxdDataToRender['Owners'] as $mixOwnerId=>$arrOwner)
		{
			$strOwnerOptions .= "<option value='$mixOwnerId'>". htmlspecialchars($arrOwner['Name'], ENT_QUOTES) ."</option>\n";
		}
		
		// Build list of Ticket Categories
		$strCategoryOptions = "";
		foreach ($this->mxdDataToRender['Categories'] as $mixCategoryId=>$arrCategory)
		{
			$strCategoryOptions .= "<option value='$mixCategoryId'>". htmlspecialchars($arrCategory['Name'], ENT_QUOTES) ."</option>\n";
		}
		
		// Build list of Ticket Statuses
		$strStatusOptions = "";
		foreach ($this->mxdDataToRender['Statuses'] as $mixStatusId=>$arrStatus)
		{
			$strStatusOptions .= "<option value='$mixStatusId'>". htmlspecialchars($arrStatus['Name'], ENT_QUOTES) ."</option>\n";
		}
		
		$strEarliestTime	= $this->mxdDataToRender['TimeRange']['Earliest'];
		$strLatestTime		= $this->mxdDataToRender['TimeRange']['Latest'];
		

echo "
<form id='FormReportVariables'>
	<table class='reflex'>
		<caption>
			<div id='caption_bar' name='caption_bar'>
				<div id='caption_title' name='caption_title'>
					Ticketing Report
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
				<td class='title'>Owners: </td>
				<td>
					<select id='Owners' name='Owners' size='8' multiple='multiple'>$strOwnerOptions</select>
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Categories: </td>
				<td>
					<select id='Categories' name='Categories' size='8' multiple='multiple' >$strCategoryOptions</select>
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Status: </td>
				<td>
					<select id='Statuses' name='Statuses' size='8' multiple='multiple' >$strStatusOptions</select>
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Earliest Time: </td>
				<td>
					<input type='text' id='EarliestTime' name='EarliestTime' InputMask='DateTime' maxlength='19' value='$strEarliestTime' />
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Latest Time: </td>
				<td>
					<input type='text' id='LatestTime' name='LatestTime' InputMask='DateTime' maxlength='19' value='$strLatestTime' />
				</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Output: </td>
				<td>
					<select id='RenderMode' name='RenderMode'>
						<option id='html' selected='selected' value='html'>In page HTML</option>
						<option id='excel' value='excel'>Excel</option>
					</select>
				</td>
			</tr>
		</tbody>
		<tfoot class='footer'>
			<tr>
				<th colspan='2'>
					<input type='button' class='reflex-button' value='Submit' onclick='Flex.TicketingSummaryReport.Run()'/>
				</th>
			</tr>
		</tfoot>
	</table>
</form>
<script type='text/javascript'>Flex.TicketingSummaryReport.Initialise()</script>

";

	}
}

?>
