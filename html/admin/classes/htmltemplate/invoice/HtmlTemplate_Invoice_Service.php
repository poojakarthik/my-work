<?php

class HtmlTemplate_Invoice_Service extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		//$this->LoadJavascript("employee_message");
	}

	public function Render()
	{
		$this->renderInvoiceDetails($this->mxdDataToRender['Invoice']);

		$this->renderAdjustments($this->mxdDataToRender['Adjustments']);

		$this->renderCDRs($this->mxdDataToRender['CDRs'], $this->mxdDataToRender['RecordTypes'], $this->mxdDataToRender['filter'], $this->mxdDataToRender['Invoice']);
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

	private function renderCDRs($arrCDRs, $arrRecordTypes, $arrCurrentFilter, $arrInvoice)
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
			$recordTypes .= '<option value="' . $recordType['Id'] . '"' . $selected . '>' . htmlspecialchars($recordType['Name']) . '</option>';
		}
		$recordTypes .= '</select></span></form>';

		echo "
<table class='reflex highlight-rows'>
	<caption>
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				Usage Charges
			</div>
			<div id='caption_options' name='caption_options'>$recordTypes
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th>#</th>
			<th>Record Type</th>
			<th>Start Date/Time</th>
			<th>Calling Party</th>
			<th>Duration</th>
			<th class='amount'>Charge</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>";

		$nr = $arrCurrentFilter['offset'];
		
		// RecordType DisplayType Source Destination Duration StartDatetime Currency Charge
		foreach ($arrCDRs as $cdr)
		{
			$className = $className ? '' : ' class="alt"';
			//$date = explode('-', $adjustment['StartDatetime']);
			//$date  = date('l, M d, Y', mktime(0, 0, 0, $date[1], $date[2], $date[0]));
			$nr++;
			$url = Href()->ViewInvoicedCDR($arrInvoice['ServiceTotal'], $arrInvoice['InvoiceRunId'], $cdr['Id']);
			echo "
		<tr$className>
			<td>" . htmlspecialchars($nr) . "</td>
			<td>" . htmlspecialchars($cdr['DisplayType']) . "</td>";

			switch ($cdr['RecordTypeId'])
			{
				case RECORD_DISPLAY_SUFFIX_S_AND_E:
					echo "
			<td colspan=3>" . htmlspecialchars($cdr['Description']) . "</td>";
					break;

				case RECORD_DISPLAY_SUFFIX_DATA:
					echo "
			<td colspan=3>GPRS Data</td>";
					break;

				case RECORD_DISPLAY_SUFFIX_SMS:
					echo "
			<td>" . htmlspecialchars($this->tidyDateTime($cdr['StartDatetime'])) . "</td>
			<td>" . htmlspecialchars($cdr['Destination']) . "</td>
			<td>SMS</td>";
					break;

				case RECORD_DISPLAY_SUFFIX_CALL:
				default:
					echo "
			<td>" . htmlspecialchars($this->tidyDateTime($cdr['StartDatetime'])) . "</td>
			<td>" . htmlspecialchars(($arrInvoice['ServiceType'] == SERVICE_TYPE_INBOUND) ? $cdr['Source'] : $cdr['Destination']) . "</td>
			<td>" . htmlspecialchars(number_format($cdr['Duration'], 0)) . "</td>";
					break;
			}

			echo "
			<td class='amount'>" . htmlspecialchars($this->tidyAmount($cdr['Charge'])) . "</td>
			<td><a href = \"$url\">View</a></td>
		</tr>
			";
		}
		if (!count($arrCDRs))
		{
			echo "
		<tr>
			<td colspan='7'>" . htmlspecialchars("There were no results matching your search. Please change your search and try again.") . "</td>
		</tr>
			";
		}

		echo "
	</tbody>
</table>
<br/>		";	}

	private function renderAdjustments($arrAdjustmentDetails)
	{
		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				Adjustments
			</div>
			<div id='caption_options' name='caption_options'>
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th>Id</th>
			<th>Code</th>
			<th>Description</th>
			<th>Service</th>
			<th>Adjustment Date</th>
			<th class='amount'>Amount</th>
			<th>Nature</th>
		</tr>
	</thead>
	<tbody>";

		foreach ($arrAdjustmentDetails as $adjustment)
		{
			$date = explode('-', $adjustment['Date']);
			$date  = date('l, M d, Y', mktime(0, 0, 0, $date[1], $date[2], $date[0]));
			$className = $className ? '' : ' class="alt"';
			echo "
		<tr$className>
			<td>" . htmlspecialchars($adjustment['ChargeId']) . "</td>
			<td>" . htmlspecialchars($adjustment['ChargeType']) . "</td>
			<td>" . htmlspecialchars($adjustment['Description']) . "</td>
			<td><a href='" . Href()->ViewService($adjustment['ServiceId']) . "'>" . htmlspecialchars($adjustment['FNN']) . "</a></td>
			<td>" . htmlspecialchars($date) . "</td>
			<td class='amount'>" . htmlspecialchars($this->tidyAmount($adjustment['Amount'])) . "</td>
			<td class='" . ($adjustment['Nature'] == 'CR' ? 'amount-credit' : 'amount-debit') . "'>" . htmlspecialchars($adjustment['Nature'] == 'CR' ? 'Credit' : 'Debit') . "</td>
		</tr>
			";
		}
		if (!count($arrAdjustmentDetails))
		{
			echo "
		<tr>
			<td colspan='7'>" . htmlspecialchars("The are no adjustments for this service and invoice.") . "</td>
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
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				Invoice Details
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
