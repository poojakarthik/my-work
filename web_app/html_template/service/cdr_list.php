<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServiceCDRList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceCDRList
 *
 * HTML Template object for the client app, Displays a paginated list of CDRs for a given service
 *
 * HTML Template object for the client app, Displays a paginated list of CDRs for a given service
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateServiceCDRList
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceCDRList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
		//$this->LoadJavascript("tooltip");
		
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		echo "<div class='WideContent'>\n";
		echo "<h2 class='CDR'>Call Information</h2>\n";
		
		// This is used to store the various record types that a CDR can be
		$arrRecordTypes = Array();
		$intServiceId = DBO()->Service->Id->Value;
		if (DBO()->Filter->Id->Value)
		{
			$intFilterId = DBO()->Filter->Id->Value;
		}
		else
		{
			$intFilterId = 0;
		}
		
		// Retrieve all the record type definitions and store it in an associative array
		// This information could have been linked to DBL()->CDR through joined tables but is probably much faster this way
		DBL()->RecordType->Load();
		foreach (DBL()->RecordType as $dboRecordType)
		{
			$arrRecordTypes[$dboRecordType->Id->Value]['DisplayType']	= $dboRecordType->DisplayType->Value;
			$arrRecordTypes[$dboRecordType->Id->Value]['Output']		= $dboRecordType->DisplayType->AsCallback("GetConstantDescription", Array("DisplayType"));
			$arrRecordTypes[$dboRecordType->Id->Value]['Name']			= $dboRecordType->Name->FormattedValue();
		}
		
		// Build the filter combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Call Type Filter:</div>\n";
		echo "   <div class='DefaultOutput' style='padding-left:100px;'>\n";
		echo "      <select id='RecordTypeCombo' style='width:300px' onchange='javascript:window.location = \"vixen.php/Service/ViewUnbilledCharges/?Service.Id=$intServiceId&Filter.Id=\"+ this.value'>\n";
		echo "         <option selected='selected' value='0'>List all call types</option>\n";
		foreach ($arrRecordTypes as $intRecordTypeId=>$arrRecordType)
		{
			// check if this RecordType was the last one selected
			if ($intRecordTypeId == $intFilterId)
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}
			$strDescription = $arrRecordType['Name'];
			echo "         <option $strSelected value='$intRecordTypeId'>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		
		Table()->CDRs->SetHeader("Time", "Called Party", "Duration", "&nbsp;", "Charge (inc GST)");
		Table()->CDRs->SetWidth("30%", "30%", "20%", "5%", "15%");
		Table()->CDRs->SetAlignment("left", "left", "left", "left", "right");
		
		// add the rows
		foreach (DBL()->CDR as $dboCDR)
		{
			// Work out what to display in the duration column
			switch ($arrRecordTypes[$dboCDR->RecordType->Value]['DisplayType'])
			{
				case RECORD_DISPLAY_CALL:
					// the CDR represents a phone call.  The duration column will display the duration of the call
					// Note: this might screw up if the duration of the phone call is greater than 24 hours
					$strTime = date("H:i:s", mktime(0, 0, $dboCDR->Units->Value));
					$strDuration = $dboCDR->Units->AsArbitrary($strTime);
					break;
				case RECORD_DISPLAY_DATA:
					// the CDR represents data transfer.  The duration column will display the number of KB transfered
					$strDataTransfered = number_format($dboCDR->Units->Value, 0, ".", ",") . "KB";
					$strDuration = $dboCDR->Units->AsArbitrary($strDataTransfered);
					break;
				default:
					$strDuration = $arrRecordTypes[$dboCDR->RecordType->Value]['Output'];
					break;
			}
			
			// If it is a credit then we want to flag it as such
			if ($dboCDR->Credit->Value)
			{
				$strCredit = $dboCDR->Credit->AsValue();
			}
			else
			{
				$strCredit = "&nbsp;";
			}
			
			Table()->CDRs->AddRow($dboCDR->StartDatetime->AsValue(),
									$dboCDR->Destination->AsValue(),
									$strDuration,
									$strCredit,
									$dboCDR->Charge->AsCallback("AddGST"));
		}
		
		if (Table()->CDRs->RowCount() == 0)
		{
			// There aren't any CDRs to display in the CDR table
			Table()->CDRs->AddRow("<span class='DefaultOutputSpan'>No records to display</span>", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
		}
		
		// Render the Call Information table
		Table()->CDRs->Render();
		
		
		// Now display the pagination controls
		// Left side controls
		if (DBO()->Page->CurrentPage->Value > 1)
		{
			// Not currently on the first page
			$strFirstPageHref 		= Href()->ViewUnbilledChargesForService($intServiceId, DBO()->Page->FirstPage->Value, $intFilterId);
			$strFirstPageLabel 		= "<span class='DefaultOutputSpan Default'><a href='$strFirstPageHref'>&lt;&lt;&nbsp;First</a></span>";
			$strPreviousPageHref 	= Href()->ViewUnbilledChargesForService($intServiceId, (DBO()->Page->CurrentPage->Value - 1), $intFilterId);
			$strPreviousPageLabel 	= "<span class='DefaultOutputSpan Default'><a href='$strPreviousPageHref'>&lt;&nbsp;Previous</a></span>";
		}
		else
		{
			// currently on the first page
			$strFirstPageLabel 		= "&nbsp;";
			$strPreviousPageLabel	= "&nbsp;";
		}
		
		// Right Side Controls
		if (DBO()->Page->CurrentPage->Value != DBO()->Page->LastPage->Value)
		{
			// Not currently on the last page
			$strLastPageHref 	= Href()->ViewUnbilledChargesForService($intServiceId, DBO()->Page->LastPage->Value, $intFilterId);
			$strLastPageLabel 	= "<span class='DefaultOutputSpan Default'><a href='$strLastPageHref'>Last&nbsp;&gt;&gt;</a></span>";
			$strNextPageHref 	= Href()->ViewUnbilledChargesForService($intServiceId, (DBO()->Page->CurrentPage->Value + 1), $intFilterId);
			$strNextPageLabel 	= "<span class='DefaultOutputSpan Default'><a href='$strNextPageHref'>Next&nbsp;&gt;</a></span>";
		}
		else
		{
			// currently on the last page
			$strLastPageLabel 	= "&nbsp;";
			$strNextPageLabel	= "&nbsp;";
		}
		
		$strRecordsDetails  = "<span class='DefaultOutputSpan Default'>Page ". DBO()->Page->CurrentPage->Value ." of ". DBO()->Page->LastPage->Value;
		$strRecordsDetails .= "<br>Results per page: ". MAX_RECORDS_PER_PAGE ."</span>";
		
		echo "<table border=0 cellspacing=0 cellpadding=0 width=100%>\n";
		echo "   <tr>\n";
		echo "      <td width='10%' align='left'>$strFirstPageLabel</td>\n";
		echo "      <td width='15%' align='left'>$strPreviousPageLabel</td>\n";
		echo "      <td width='50%' align='center'>$strRecordsDetails</td>\n";
		echo "      <td width='15%' align='right'>$strNextPageLabel</td>\n";
		echo "      <td width='10%' align='right'>$strLastPageLabel</td>\n";
		echo "   </tr>\n";
		echo "</table>\n";
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
