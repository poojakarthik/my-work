<?php

class HtmlTemplate_Invoice_CDR extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$this->renderCDRDetails($this->mxdDataToRender);
	}	
	
	public static function renderCDRDetails($aDataToRender)
	{
		$strUnitType 	= array_key_exists($aDataToRender['DisplayType'], $GLOBALS['*arrConstant']['DisplayTypeSuffix'])? $GLOBALS['*arrConstant']['DisplayTypeSuffix'][$aDataToRender['DisplayType']]['Description'] : "Unit(s)";
		$strUnits 		= intval($aDataToRender['Units']) ." ". $strUnitType;
		
		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' class='caption_bar'>
			<div id='caption_title' class='caption_title'>
				CDR Details
			</div>
			<div id='caption_options' class='caption_options'>
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
			<td>" . htmlspecialchars($aDataToRender['FNN']) . "</td>
		</tr>
		<tr>
			<td>CDR Id: </td>
			<td>" . htmlspecialchars($aDataToRender['Id']) . "</td>
		</tr>";
		
		if (isset($aDataToRender['InvoiceId']))
		{
			echo"
		<tr>
			<td>Invoice Number: </td>
			<td>" . htmlspecialchars($aDataToRender['InvoiceId']) . "</td>
		</tr>
		<tr>";
		}
		
		echo"
			<td>File: </td>
			<td>" . htmlspecialchars($aDataToRender['FileName']) . "</td>
		</tr>
		<tr>
			<td>Carrier: </td>
			<td>" . htmlspecialchars($aDataToRender['Carrier']) . "</td>
		</tr>
		<tr>
			<td>Carrier Reference: </td>
			<td>" . htmlspecialchars($aDataToRender['CarrierRef']) . "</td>
		</tr>
		<tr>
			<td>Source: </td>
			<td" . ($aDataToRender['Source'] ? '' : ' class="attention"') . ">" . htmlspecialchars($aDataToRender['Source'] ? $aDataToRender['Source'] : "Undefined") . "</td>
		</tr>
		<tr>
			<td>Destination: </td>
			<td" . ($aDataToRender['Destination'] ? '' : ' class="attention"') . ">" . htmlspecialchars($aDataToRender['Destination'] ? $aDataToRender['Destination'] : "Undefined") . "</td>
		</tr>
		<tr>
			<td>Start Date/Time: </td>
			<td>". htmlspecialchars(self::tidyDateTime($aDataToRender['StartDatetime'])) . "</td>
		</tr>
		<tr>
			<td>End Date/Time: </td>
			<td>". htmlspecialchars(self::tidyDateTime($aDataToRender['EndDatetime'])) . "</td>
		</tr>
		<tr>
			<td>Total Units: </td>
			<td>". htmlspecialchars($strUnits) . "</td>
		</tr>
		<tr>
			<td>Status: </td>
			<td>". htmlspecialchars($aDataToRender['Status']) . "</td>
		</tr>
		<tr>
			<td>Description: </td>
			<td>". htmlspecialchars($aDataToRender['Description']) . "</td>
		</tr>
		<tr>
			<td>Destination Code: </td>
			<td>". htmlspecialchars($aDataToRender['DestinationCode']) . "</td>
		</tr>
		<tr>
			<td>Record Type: </td>
			<td>". htmlspecialchars($aDataToRender['RecordType']) . "</td>
		</tr>
		" . 
		
		(Flex::authenticatedUserIsGod() ? ("<tr>
			<td>Cost: </td>
			<td>". htmlspecialchars(self::tidyAmount($aDataToRender['Cost'])) . "</td>
		</tr>") : "")
		
		. "
		<tr>
			<td>Charge: </td>
			<td>". htmlspecialchars(self::tidyAmount($aDataToRender['Charge'])) . "</td>
		</tr>
		<tr>
			<td>Rate: </td>
			<td><a onclick='". Href()->ViewRate($aDataToRender['RateId']) ."' title='View Rate'>". htmlspecialchars($aDataToRender['Rate']) . "</a></td>
		</tr>
		<tr>
			<td>Normalised On: </td>
			<td class='" . ($aDataToRender['NormalisedOn'] ? '' : 'attention') . "'>". htmlspecialchars($aDataToRender['NormalisedOn'] ? self::tidyDateTime($aDataToRender['NormalisedOn']) : 'Not Normalised') . "</td>
		</tr>
		<tr>
			<td>Rated On: </td>
			<td class='" . ($aDataToRender['RatedOn'] ? '' : 'attention') . "'>". htmlspecialchars($aDataToRender['RatedOn'] ? self::tidyDateTime($aDataToRender['RatedOn']) : 'Not Rated') . "</td>
		</tr>";
		
		if (isset($aDataToRender['InvoiceId']))
		{
			echo "
		<tr>
			<td>Invoice Run: </td>
			<td>". htmlspecialchars($aDataToRender['InvoiceRunId']) . "</td>
		</tr>";
		}
		
		echo "
		<tr>
			<td>Sequence Number: </td>
			<td>". htmlspecialchars($aDataToRender['SequenceNo']) . "</td>
		</tr>
		<tr>
			<td>Credit: </td>
			<td class='" . ($aDataToRender['Credit'] == 1 ? 'amount-debit' : 'amount-credit') . "'>". htmlspecialchars($aDataToRender['Credit'] == 1 ? 'Credit' : 'Debit') . "</td>
		</tr>
		<tr>
			<td>Raw CDR: </td>
			<td><code>". htmlspecialchars($aDataToRender['RawCDR']) . "</code></td>
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


	public static function tidyAmount($amount)
	{
		if (strpos($amount, '.') === FALSE)
		{
			$amount .= '.';
		}
		$amount .= '000';
		$amount = substr($amount, 0, strrpos($amount, '.') + 3);
		return '$' . $amount;
	}
	
	public static function tidyDateTime($strDateTime)
	{
		$parts = explode(' ', $strDateTime);
		$date = explode('-', $parts[0]);
		$time = explode(':', $parts[1]);
		return date('D, M d, Y h:i:s a', mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]));
	}

}

?>
