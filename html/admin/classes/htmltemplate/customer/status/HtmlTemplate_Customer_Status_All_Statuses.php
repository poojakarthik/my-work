<?php

class HtmlTemplate_Customer_Status_All_Statuses extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$arrStatuses = $this->mxdDataToRender;
		
		// Build each row
		$strRows = "";
		$bolAltRow = FALSE;
		foreach ($arrStatuses as $objStatus)
		{
			$strName		= htmlspecialchars($objStatus->name);
			$strDescription	= htmlspecialchars($objStatus->description);
			
			$strViewLink = Href()->ViewCustomerStatus($objStatus->id);
			$strEditLink = Href()->EditCustomerStatus($objStatus->id);
			
			$strRowClass	= ($bolAltRow)? "alt" : "";
			$bolAltRow		= !$bolAltRow;
			
			$strRows .= "
		<tr class='$strRowClass'>
			<td>{$objStatus->precedence}</td>
			<td>$strName</td>
			<td>$strDescription</td>
			<td><a href='$strViewLink'>View</a></td>
			<td><a href='$strEditLink'>Edit</a></td>
		</tr>";
		}
		
		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				Customer Statuses
			</div>
			<div id='caption_options' name='caption_options'>
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th>Precedence</th>
			<th>Name</th>
			<th>Criteria</th>
			<th colspan='2'></th>
		</tr>
	</thead>
	<tbody>$strRows
	</tbody>
	<tfoot class='footer'>
		<tr>
			<th colspan='5'></th>
		</tr>
	</tfoot>
</table>
";

	}
}

?>
