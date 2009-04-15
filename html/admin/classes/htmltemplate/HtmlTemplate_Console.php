<?php

class HtmlTemplate_Console extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$arrMessage = $this->mxdDataToRender['DailyMessage'];
		if ($arrMessage !== NULL)
		{
			// There is a daily message to display
			$strTimestamp	= date("M jS, Y H:i:s", strtotime($arrMessage['Timestamp']));
			$strMessage		= str_replace("\n", "", $arrMessage['Message']);
			
			$strDailyMessageSection = "
<div id='DailyMessageContainer'>
	<table class='reflex'>
		<tbody>
			<thead>
				<th>Message of the day.</th>
			</thead>
			<tfoot>
				<th>Last updated: $strTimestamp</th>
			</tfoot>
			<tr class='alt'>
				<td>&nbsp;</td>
			</tr>
			<tr class='alt'>
				<td>$strMessage</td>
			</tr>
			<tr class='alt'>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
</form>
";
		}
		else
		{
			$strDailyMessageSection = "";
		}
		
		echo "
$strDailyMessageSection
";

	}
}

?>
