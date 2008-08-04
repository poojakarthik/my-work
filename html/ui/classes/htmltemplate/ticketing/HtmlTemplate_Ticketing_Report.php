<?php

class HtmlTemplate_Ticketing_Report extends FlexHtmlTemplate
{
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript("ticketing_report");
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
		
		$strStartDate	= $this->mxdDataToRender['DateRange']['Start'];
		$strEndDate		= $this->mxdDataToRender['DateRange']['End'];
		
		$strYearLowerLimit	= 2008;
		$strYearUpperLimit	= date("Y");
		
		
/*		echo "
<div id='ReportControlsContainer' class='GroupedContent'>
	<div style='width:100%'>
		<div style='width:30%;float:left'>
			<span>Owners</span>
			<select id='OwnersCombo' name='Owners' size='8' multiple='multiple' style='display:block;clear:both;width:95%'>$strOwnerOptions</select>
		</div>	
		<div style='width:30%;float:left'>
			<span>Categories</span>
			<select id='CategoriesCombo' name='Categories' size='8' multiple='multiple' style='display:block;clear:both;width:95%'>$strCategoryOptions</select>
		</div>	
		<div style='width:30%;float:left'>
			<span>Statuses</span>
			<select id='StatusesCombo' name='Statuses' size='8' multiple='multiple' style='display:block;clear:both;width:95%'>$strStatusOptions</select>
		</div>	
	</div>
	<div style='clear:both'></div>
	<div style='width:100%'>
		<span>Earliest Date </span>
		<input type='text' id='StartDate' InputMask='ShortDate' maxlength='10' value='$strStartDate' $strYearLowerLimit style='width:11em'/>
		<a onclick='DateChooser.showChooser(\$ID(\"StartDate\"), \$ID(\"StartingDateCalender\"), $strYearLowerLimit, $strYearUpperLimit, \"d/m/Y\", false, true, true, $strYearLowerLimit);'>
			<img src='img/template/calendar_small.png'/>
		</a>
		<div id='StartingDateCalender' class='date-time select-free' style='display:none; visibility:hidden;'></div>
		
		<span> Latest Date </span>
		<input type='text' id='EndDate' InputMask='ShortDate' maxlength='10' value='$strEndDate' $strYearUpperLimit style='width:11em'/>
		<a onclick='DateChooser.showChooser(\$ID(\"EndDate\"), \$ID(\"EndingDateCalender\"), $strYearLowerLimit, $strYearUpperLimit, \"d/m/Y\", false, true, true, $strYearUpperLimit);'>
			<img src='img/template/calendar_small.png'/>
		</a>
		<div id='EndingDateCalender' class='date-time select-free' style='display:none; visibility:hidden;'></div>
		<div style='clear:both'>
			<span>Output </span>
			<select id='RenderModeCombo'>
				<option id='html' selected='selected'>In page HTML</option>
				<option id='excel'>Excel</option>
			</select>
		</div>
	</div>
</div> <!-- GroupedContent -->
<div class='SmallSeparator'></div>
<div id='ReportInPage' name='ReportInPage'>
</div>
<script type='text/javascript'>Flex.TicketingReport.Initialise()</script>
";*/

echo "
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
			<td class='title'>Earliest Date: </td>
			<td>
				<input type='text' id='StartDate' name='StartDate' InputMask='ShortDate' maxlength='10' value='$strStartDate' $strYearLowerLimit style='width:11em'/>
				<a onclick='DateChooser.showChooser(\$ID(\"StartDate\"), \$ID(\"StartingDateCalender\"), $strYearLowerLimit, $strYearUpperLimit, \"d/m/Y\", false, true, true, $strYearLowerLimit);'>
					<img src='img/template/calendar_small.png'/>
				</a>
				<div id='StartingDateCalender' class='date-time select-free' style='display:none; visibility:hidden;'></div>
			</td>
		</tr>
		<tr class='alt'>
			<td class='title'>Latest Date: </td>
			<td>
				<input type='text' id='EndDate' name='EndDate' InputMask='ShortDate' maxlength='10' value='$strEndDate' $strYearUpperLimit style='width:11em'/>
				<a onclick='DateChooser.showChooser(\$ID(\"EndDate\"), \$ID(\"EndingDateCalender\"), $strYearLowerLimit, $strYearUpperLimit, \"d/m/Y\", false, true, true, $strYearUpperLimit);'>
					<img src='img/template/calendar_small.png'/>
				</a>
				<div id='EndingDateCalender' class='date-time select-free' style='display:none; visibility:hidden;'></div>
			</td>
		</tr>
		<tr class='alt'>
			<td class='title'>Output: </td>
			<td>
				<select id='RenderModeCombo'>
					<option id='html' selected='selected'>In page HTML</option>
					<option id='excel'>Excel</option>
				</select>
			</td>
		</tr>
	</tbody>
	<tfoot class='footer'>
		<tr>
			<th colspan='2'>
				<input type='button' id='submit' name='submit' value='Submit' onclick='$Alert(\"TODO\")'/>
			</th>
		</tr>
	</tfoot>
</table>

";

	}
}

?>
