<?php

class HtmlTemplate_Employee_Message_Management extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("employee_message");
	}

	public function Render()
	{
		$arrMessages = $this->mxdDataToRender['MessageHistory'];
		
		
		
		
		// Build each row
		$strRows = "";
		$bolAltRow = FALSE;
		
		if (count($arrMessages) == 0)
		{
			// There are no messages
			$strRows = "
<tr>
	<td colspan=2>
		No messages to display
	</td>
</tr>
";
		}
		else
		{
			foreach ($arrMessages as $objMessage)
			{
				$strEffectiveFrom = date("d/m/Y H:i:s", strtotime($objMessage->effectiveOn));
				
				// $strMessage = str_replace("\n", "<br />", htmlspecialchars($objMessage->message));
				$strMessage = $objMessage->message;
				
				$strRowClass	= ($bolAltRow)? "alt" : "";
				$bolAltRow		= !$bolAltRow;
				
				$strRows .= "
			<tr class='$strRowClass'>
				<td valign='top'>$strEffectiveFrom</td>
				<td>$strMessage</td>
			</tr>";
			}
		}
		
		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' class='caption_bar'>
			<div id='caption_title' class='caption_title'>
				Messages
			</div>
			<div id='caption_options' class='caption_options'>
				<a onclick='FlexEmployeeMessage.newMessage()'>New</a>
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th>Effective From</th>
			<th>Message</th>
		</tr>
	</thead>
	<tbody>$strRows
	</tbody>
</table>
";

	}
}

?>
