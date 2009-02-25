<?php

class HtmlTemplate_Invoice_CDR extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		//$this->LoadJavascript("employee_message");
	}

	public function Render()
	{
		$strUnitType = array_key_exists($this->mxdDataToRender['DisplayType'], $GLOBALS['*arrConstant']['DisplayTypeSuffix'])? $GLOBALS['*arrConstant']['DisplayTypeSuffix'][$this->mxdDataToRender['DisplayType']]['Description'] : "Unit(s)";
		$strUnits = intval($this->mxdDataToRender['Units']) ." ". $strUnitType;
		
		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				CDR Details
			</div>
			<div id='caption_options' name='caption_options'>
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th colspan=\"4\">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style=\"width: 20em;\">FNN: </td>
			<td>" . htmlspecialchars($this->mxdDataToRender['FNN']) . "</td>
		</tr>
		<tr>
			<td>CDR Id: </td>
			<td>" . htmlspecialchars($this->mxdDataToRender['Id']) . "</td>
		</tr>
		<tr>
			<td>Invoice Number: </td>
			<td>" . htmlspecialchars($this->mxdDataToRender['InvoiceId']) . "</td>
		</tr>
		<tr>
			<td>File: </td>
			<td>" . htmlspecialchars($this->mxdDataToRender['FileName']) . "</td>
		</tr>
		<tr>
			<td>Carrier: </td>
			<td>" . htmlspecialchars($this->mxdDataToRender['Carrier']) . "</td>
		</tr>
		<tr>
			<td>Carrier Reference: </td>
			<td>" . htmlspecialchars($this->mxdDataToRender['CarrierRef']) . "</td>
		</tr>
		<tr>
			<td>Source: </td>
			<td" . ($this->mxdDataToRender['Source'] ? '' : ' class="attention"') . ">" . htmlspecialchars($this->mxdDataToRender['Source'] ? $this->mxdDataToRender['Source'] : "Undefined") . "</td>
		</tr>
		<tr>
			<td>Destination: </td>
			<td" . ($this->mxdDataToRender['Destination'] ? '' : ' class="attention"') . ">" . htmlspecialchars($this->mxdDataToRender['Destination'] ? $this->mxdDataToRender['Destination'] : "Undefined") . "</td>
		</tr>
		<tr>
			<td>Start Date/Time: </td>
			<td>". htmlspecialchars($this->tidyDateTime($this->mxdDataToRender['StartDatetime'])) . "</td>
		</tr>
		<tr>
			<td>End Date/Time: </td>
			<td>". htmlspecialchars($this->tidyDateTime($this->mxdDataToRender['EndDatetime'])) . "</td>
		</tr>
		<tr>
			<td>Total Units: </td>
			<td>". htmlspecialchars($strUnits) . "</td>
		</tr>
		<tr>
			<td>Status: </td>
			<td>". htmlspecialchars($this->mxdDataToRender['Status']) . "</td>
		</tr>
		<tr>
			<td>Description: </td>
			<td>". htmlspecialchars($this->mxdDataToRender['Description']) . "</td>
		</tr>
		<tr>
			<td>Destination Code: </td>
			<td>". htmlspecialchars($this->mxdDataToRender['DestinationCode']) . "</td>
		</tr>
		<tr>
			<td>Record Type: </td>
			<td>". htmlspecialchars($this->mxdDataToRender['RecordType']) . "</td>
		</tr>
		" . 
		
		(Flex::authenticatedUserIsGod() ? ("<tr>
			<td>Cost: </td>
			<td>". htmlspecialchars($this->tidyAmount($this->mxdDataToRender['Cost'])) . "</td>
		</tr>") : "")
		
		. "
		<tr>
			<td>Charge: </td>
			<td>". htmlspecialchars($this->tidyAmount($this->mxdDataToRender['Charge'])) . "</td>
		</tr>
		<tr>
			<td>Rate: </td>
			<td><a onclick='". Href()->ViewRate($this->mxdDataToRender['RateId']) ."' title='View Rate'>". htmlspecialchars($this->mxdDataToRender['Rate']) . "</a></td>
		</tr>
		<tr>
			<td>Normalised On: </td>
			<td class='" . ($this->mxdDataToRender['NormalisedOn'] ? '' : 'attention') . "'>". htmlspecialchars($this->mxdDataToRender['NormalisedOn'] ? $this->tidyDateTime($this->mxdDataToRender['NormalisedOn']) : 'Not Normalised') . "</td>
		</tr>
		<tr>
			<td>Rated On: </td>
			<td class='" . ($this->mxdDataToRender['RatedOn'] ? '' : 'attention') . "'>". htmlspecialchars($this->mxdDataToRender['RatedOn'] ? $this->tidyDateTime($this->mxdDataToRender['RatedOn']) : 'Not Rated') . "</td>
		</tr>
		<tr>
			<td>Invoice Run: </td>
			<td>". htmlspecialchars($this->mxdDataToRender['InvoiceRunId']) . "</td>
		</tr>
		<tr>
			<td>Sequence Number: </td>
			<td>". htmlspecialchars($this->mxdDataToRender['SequenceNo']) . "</td>
		</tr>
		<tr>
			<td>Credit: </td>
			<td class='" . ($this->mxdDataToRender['Credit'] == 1 ? 'amount-debit' : 'amount-credit') . "'>". htmlspecialchars($this->mxdDataToRender['Credit'] == 1 ? 'Credit' : 'Debit') . "</td>
		</tr>
		<tr>
			<td>Raw CDR: </td>
			<td><code>". htmlspecialchars($this->mxdDataToRender['RawCDR']) . "</code></td>
		</tr>
	</tbody>
</table>
<br/>
		";
		/*<!-- CDR -->
		<!-- TODO!flame! display raw CDR
				use functions from normalisation modules to split the cdr
				display key=>value from array into a text box
		<tr>
			<th class="JustifiedWidth">
				<xsl:call-template name="Label">
					<xsl:with-param name="entity" select="string('CDR')" />
					<xsl:with-param name="field" select="string('CDR')" />
				</xsl:call-template>
			</th>
			<td><xsl:value-of select="/Response/CDR/CDR" /></td>
		</tr>
		-->*/
	}


	private function tidyAmount($amount)
	{
		if (strpos($amount, '.') === FALSE)
		{
			$amount .= '.';
		}
		$amount .= '000';
		$amount = substr($amount, 0, strrpos($amount, '.') + 3);
		return '$' . $amount;
	}
	
	private function tidyDateTime($strDateTime)
	{
		$parts = explode(' ', $strDateTime);
		$date = explode('-', $parts[0]);
		$time = explode(':', $parts[1]);
		return date('D, M d, Y h:i:s a', mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]));
	}

}

?>
