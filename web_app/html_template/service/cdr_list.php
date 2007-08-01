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
		
		// Retrieve all the record type definitions and store it in an associative array
		// This information could have been linked to DBL()->CDR through joined tables but is probably much faster this way
		DBL()->RecordType->Load();
		foreach (DBL()->RecordType as $dboRecordType)
		{
			$arrRecordTypes[$dboRecordType->Id->Value]['DisplayType']	= $dboRecordType->DisplayType->Value;
			$arrRecordTypes[$dboRecordType->Id->Value]['Output']		= $dboRecordType->DisplayType->AsCallback("GetConstantDescription", Array("DisplayType"));
		}
		
		Table()->CDRs->SetHeader("Date & Time", "Called Party", "Duration", "&nbsp;", "Charge (inc GST)");
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
		
		Table()->CDRs->Render();
		
		
		// Now display the pagination controls
		$strHtmlPageLeft = 
		
		echo "<table border=0 cellspacing=0 cellpadding=0 width=100%>\n";
		echo "   <tr>";
		
		echo "   </tr>";
		echo "</table>\n";
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
