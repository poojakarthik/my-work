<?php

class HtmlTemplate_Invoice_Service extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$this->renderInvoiceDetails($this->mxdDataToRender['Invoice']);

		$this->renderCharges($this->mxdDataToRender['Charges']);

		$this->renderCDRs(
			$this->mxdDataToRender['CDRs'], 
			$this->mxdDataToRender['RecordTypes'], 
			$this->mxdDataToRender['filter'], 
			$this->mxdDataToRender['Invoice'],
			$this->mxdDataToRender['ServiceType'],
			$this->mxdDataToRender['ServiceType']['ServiceId']
		);
	}

	public static function tidyAmount($amount, $bolIsCredit=false)
	{
		if ($bolIsCredit && ($amount > 0.000000))
		{
			$amount = $amount * (-1);
		}

		if (strpos($amount, '.') === FALSE)
		{
			$amount .= '.';
		}
		$amount .= '000';
		$amount = substr($amount, 0, strrpos($amount, '.') + 3);
		
		return $amount;
	}
	
	public static function tidyDateTime($strDateTime)
	{
		$parts = explode(' ', $strDateTime);
		$date = explode('-', $parts[0]);
		$time = explode(':', $parts[1]);
		return date('D, M d, Y h:i:s a', mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]));
	}

	public static function renderCDRs($arrCDRs, $arrRecordTypes, $arrCurrentFilter, $arrInvoice=null, $iServiceType, $iServiceId)
	{
		$rt = $arrCurrentFilter['recordType'] === NULL ? NULL : intval($arrCurrentFilter['recordType']);

		$strRange = '';

		$paginated = $arrCurrentFilter['limit'] < $arrCurrentFilter['recordCount'];
		$firstPage = $arrCurrentFilter['offset'] > 0 ? '<button onclick="return goto(0);">&#124;&lt;- First</button>' : '<button disabled="disabled">&#124;&lt;- First</button>';
		$previousPage = $arrCurrentFilter['offset'] > 0 ? '<button onclick="return goto(' . ($arrCurrentFilter['offset'] - $arrCurrentFilter['limit']) . ');">&lt;- Back</button>' : '<button disabled="disabled">&lt;- Back</button>';
		
		$onLast = ($arrCurrentFilter['offset'] + $arrCurrentFilter['limit']) > $arrCurrentFilter['recordCount'];
		$nextPage = $onLast ? '<button disabled="disabled">Next -&gt;</button>' : '<button onclick="return goto(' . ($arrCurrentFilter['offset'] + $arrCurrentFilter['limit']) . ');">Next -&gt;</button>';
		$lastPageOffset = ($arrCurrentFilter['recordCount'] - ($arrCurrentFilter['recordCount'] % $arrCurrentFilter['limit']));
		if ($lastPageOffset == $arrCurrentFilter['recordCount'])
		{
			$lastPageOffset = $arrCurrentFilter['recordCount'] - $arrCurrentFilter['limit'];
		}
		$lastPage = $onLast ? '<button disabled="disabled">Last -&gt;&#124;</button>' : '<button onclick="return goto(' . $lastPageOffset . ');">Last -&gt;&#124;</button>';

		if ($arrCurrentFilter['recordCount'] > 0)
		{
			$strRange = "<div style='float: left; display: inline;'>";
			if ($paginated)
			{
				$strRange .= '<script>function goto(offset) {document.getElementById("xoffset").value=offset;document.getElementById("cdrForm").submit();document.getElementById("recordType").disabled=true;return false;}</script>' .
							$firstPage . '&nbsp;' . $previousPage . "&nbsp;";
			}
			$strRange .= "Showing " . ($arrCurrentFilter['offset'] + 1) . " to " . (($arrCurrentFilter['limit'] + $arrCurrentFilter['offset']) >= $arrCurrentFilter['recordCount'] ? $arrCurrentFilter['recordCount'] : ($arrCurrentFilter['limit'] + $arrCurrentFilter['offset'])) . " of " . $arrCurrentFilter['recordCount'];
			if ($paginated) 
			{
				$strRange .= "&nbsp;" . $nextPage . '&nbsp;' . $lastPage;
			}
			$strRange .= "</div>";
		}

		$recordTypes = '<form method="POST" id="cdrForm">' .
				'<input type="hidden" name="offset" value="0" id="xoffset" />' .
				$strRange .
				'<span id="recordTypes">Record type: <select id="recordType" name="recordType" onchange="this.form.submit();this.disabled=true;">' .
				'<option value="">All types</option>' .
				'';
		foreach ($arrRecordTypes as $recordType)
		{
			$selected = ($recordType['Id'] == $rt) ? " selected" : "";
			$recordTypes .= '<option value="' . $recordType['Id'] . '"' . $selected . '>' . htmlspecialchars($recordType['Description']) . '</option>';
		}
		$recordTypes .= '</select></span></form>';

		echo "
<table class='reflex highlight-rows cdr-list'>
	<caption>
		<div id='caption_bar' class='caption_bar'>
			<div id='caption_title' class='caption_title'>
				Usage Charges
			</div>
			<div id='caption_options' class='caption_options'>$recordTypes
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th>#</th>
			<th>Record Type</th>
			<th>Start Date/Time</th>
			<th>Calling Party</th>
			<th style='text-align:right'>Duration</th>
			<th>&nbsp;</th>
			<th class='amount'>Charge (\$)</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>";

		$nr = $arrCurrentFilter['offset'];
		
		// RecordType DisplayType Source Destination Duration StartDatetime Currency Charge
		foreach ($arrCDRs as $cdr)
		{
			$className = $className ? '' : ' class="alt"';
			$nr++;
			
			if (is_null($arrInvoice))
			{
				$url	= Href()->ViewCDRDetails($iServiceId, $cdr['Id']); 
			}
			else
			{
				$url 	= Href()->ViewInvoicedCDR($arrInvoice['ServiceTotal'], $arrInvoice['InvoiceRunId'], $cdr['Id']);
			}
			
			echo "
		<tr$className>
			<td>" . htmlspecialchars($nr) . "</td>
			<td>" . htmlspecialchars($arrRecordTypes[$cdr['RecordTypeId']]['Description']) . "</td>";

			// The way we display the record depends on the display type of the record type
			switch ($arrRecordTypes[$cdr['RecordTypeId']]['DisplayType'])
			{
				case RECORD_DISPLAY_S_AND_E:
					echo "
			<td colspan=4>" . htmlspecialchars($cdr['Description']) . "</td>";
					break;

				case RECORD_DISPLAY_DATA:
					echo "
			<td colspan=2>". htmlspecialchars(self::tidyDateTime($cdr['StartDatetime'])) ."</td>
			<td style='text-align:right'>{$cdr['Units']}</td>
			<td>". GetConstantDescription($arrRecordTypes[$cdr['RecordTypeId']]['DisplayType'], 'DisplayTypeSuffix') ."</td>";
					break;

				default:
					echo "
			<td>" . htmlspecialchars(self::tidyDateTime($cdr['StartDatetime'])) . "</td>
			<td>" . htmlspecialchars(($iServiceType == SERVICE_TYPE_INBOUND) ? $cdr['Source'] : $cdr['Destination']) . "</td>
			<td style='text-align:right'>{$cdr['Units']}</td>
			<td>". GetConstantDescription($arrRecordTypes[$cdr['RecordTypeId']]['DisplayType'], 'DisplayTypeSuffix') ."</td>";
					break;
			}

			$bolIsCredit = ($cdr['Credit'] == 1)? true : false;
			
			echo "
			<td class='amount'>" . htmlspecialchars(self::tidyAmount($cdr['Charge'], $bolIsCredit)) . "</td>
			<td><a href = \"$url\">View</a></td>
		</tr>
			";
		}
		if (!count($arrCDRs))
		{
			echo "
		<tr>
			<td colspan='8'>" . htmlspecialchars("There were no results matching your search. Please change your search and try again.") . "</td>
		</tr>
			";
		}

		echo "
	</tbody>
</table>
<br/>		";	
	}

	public static function renderCharges($arrChargeDetails)
	{
		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' class='caption_bar'>
			<div id='caption_title' class='caption_title'>
				Charges
			</div>
			<div id='caption_options' class='caption_options'>
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th>Id</th>
			<th>Code</th>
			<th>Description</th>
			<th>Service</th>
			<th>Charge Date</th>
			<th class='amount'>Amount</th>
			<th>Nature</th>
		</tr>
	</thead>
	<tbody>";

		foreach ($arrChargeDetails as $charge)
		{
			$date = explode('-', $charge['Date']);
			$date  = date('l, M d, Y', mktime(0, 0, 0, $date[1], $date[2], $date[0]));
			$className = $className ? '' : ' class="alt"';
			echo "
		<tr$className>
			<td>" . htmlspecialchars($charge['ChargeId']) . "</td>
			<td>" . htmlspecialchars($charge['ChargeType']) . "</td>
			<td>" . htmlspecialchars($charge['Description']) . "</td>
			<td><a href='" . Href()->ViewService($charge['ServiceId']) . "'>" . htmlspecialchars($charge['FNN']) . "</a></td>
			<td>" . htmlspecialchars($date) . "</td>
			<td class='amount'>" . htmlspecialchars(self::tidyAmount($charge['Amount'])) . "</td>
			<td class='" . ($charge['Nature'] == 'CR' ? 'amount-credit' : 'amount-debit') . "'>" . htmlspecialchars($charge['Nature'] == 'CR' ? 'Credit' : 'Debit') . "</td>
		</tr>
			";
		}
		if (!count($arrChargeDetails))
		{
			echo "
		<tr>
			<td colspan='7'>" . htmlspecialchars("There are no charges for this service.") . "</td>
		</tr>
			";
		}

		echo "
	</tbody>
</table>
<br/>		";
	}

	private function renderInvoiceDetails($arrInvoiceDetails)
	{
		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' class='caption_bar'>
			<div id='caption_title' class='caption_title'>
				Invoice Details
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
			<td style=\"width: 20em;\">Account Id: </td>
			<td>" . htmlspecialchars($arrInvoiceDetails['AccountId']) . "</td>
		</tr>
		<tr>
			<td>Business Name: </td>
			<td>" . htmlspecialchars($arrInvoiceDetails['BusinessName']) . "</td>
		</tr>
		<tr>
			<td>Invoice Number: </td>
			<td>" . htmlspecialchars($arrInvoiceDetails['InvoiceId']) . "</td>
		</tr>
		<tr>
			<td>Service Number: </td>
			<td>" . htmlspecialchars($arrInvoiceDetails['FNN']) . "</td>
		</tr>
		
	</tbody>
</table>
<br/>
		";
	}
}

?>
