<?php
//----------------------------------------------------------------------------//
// HtmlTemplatePlanRates
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanRates
 *
 * A specific HTML Template object
 *
 * An Plan HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanRates extends HtmlTemplate
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
		
		$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
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
		if (DBL()->RateList->RecordCount() == 0)
		{
			// There are no rates for the plan (probably a dummy plan)
			echo "There are no viewable rates for this plan.";
		}
		else
		{
			echo "<div  style='overflow:scroll; height:500px'>\n";
			echo "<h2 class='Adjustment'>Rates</h2>\n";
			echo "<p>";
			
			// Array of tables for each Rate Group, to be rendered at the end			
			$arrTables = Array();
			
			foreach (DBL()->RateList as $dboRate)
			{
				// Load data objects reqd. for detail divs
				DBO()->Rate->Id = $dboRate->Id->Value;
				DBO()->Rate->Load();
				DBO()->RecordType->Id = DBO()->Rate->RecordType->Value;
				DBO()->RecordType->Load();
				
				// Set up the table name and label for each of the table (requires removal of
				// any spaces/hyphens or the js will stuff up) 
				$strTableName = str_replace(Array(" ", "-"), "", $dboRate->Name->Value);
				
				// The Table's label is of the format 'Rate group (Rate name)' 
				$strTableLabel = $dboRate->RateGroup->Value . " (" . $dboRate->Name->Value . ")";
				
				if (!in_array($strTableName, $arrTables))
				{
					// We have reached the end of a table, add it to our array of tables
					$arrTables[] = $strTableName;
				}
				
				// Misc strings to store things to pass as AsArbitrary
				
				// The charge needs to be formatted as '$0.0000 per StdUnits RecordDisplayRateSuffix' 
				// RecordDisplayRateSuffix is extracted from the definitions file, using the RecordType as an index
				$strCharge = OutputMask()->MoneyValue(DBO()->Rate->StdRatePerUnit->Value, 4, TRUE) . " per " . DBO()->Rate->StdUnits->Value . " " . $GLOBALS['RecordDisplayRateSuffix'][DBO()->RecordType->DisplayType->Value]; 
				
				// The Days Available indicator needs to contain formatting (as this indicates
				// whether or not it is available on that day), with line breaks removed
				// eg Mo Tu We Th Fr Sa Su
				$strDaysAvailable = DBO()->Rate->Monday->AsValue() . " " . 
									DBO()->Rate->Tuesday->AsValue() . " " . 
									DBO()->Rate->Wednesday->AsValue() . " " . 
									DBO()->Rate->Thursday->AsValue() . " " . 
									DBO()->Rate->Friday->AsValue() . " " . 
									DBO()->Rate->Saturday->AsValue() . " " . 
									DBO()->Rate->Sunday->AsValue();
				$strDaysAvailable = str_replace("\n", "", $strDaysAvailable);
				
				// Time available, formatted nicely
				$strTimeAvailable = date("g:i:s A", strtotime(DBO()->Rate->StartTime->Value)) . " to " . date("g:i:s A", strtotime(DBO()->Rate->EndTime->Value));
				
				// Because in the database it is listed as 12:00:00AM to 11:59:59PM, we need to add 1 second/minute before formatting it
				$strHours = gmdate("G", (strtotime(DBO()->Rate->EndTime->Value) - strtotime(DBO()->Rate->StartTime->Value))) + 1;
				$strMinutes = gmdate("i", (strtotime(DBO()->Rate->EndTime->Value) - strtotime(DBO()->Rate->StartTime->Value)) + 1);
				$strDuration = $strHours . " hours, " . $strMinutes . " minutes";
				
				// Each table has the name of $strTableName when referencing it
				Table()->$strTableName->SetHeader($strTableLabel);
				Table()->$strTableName->SetAlignment("Left");
				
				// HACK! HACK! HACK! Set the rows to be of the format 'RateDescription 	DaysAvailableIndicatorFormatted		Duration'
				// formatted nicely. Unfortunately its a bit of a hardcoded hack 
				$strRow = "<table><td width='400px' class='DefaultOutputSpan Default'>{$dboRate->Description->Value}</td><td width='160px'>$strDaysAvailable</td><td width='190px' align='right' class='DefaultOutputSpan Default'>$strTimeAvailable</td></table>";
				Table()->$strTableName->AddRow($strRow);
				
				
				//Set the detail divs
				$strDetailHtml = "<div class='VixenTableDetail'>\n";
				$strDetailHtml .= DBO()->Rate->Name->AsOutput();
				$strDetailHtml .= DBO()->Rate->Description->AsOutput();
				$strDetailHtml .= DBO()->Rate->StdRatePerUnit->AsArbitrary($strCharge, RENDER_OUTPUT); 
				$strDetailHtml .= DBO()->Rate->StdFlagfall->AsOutput();
				$strDetailHtml .= DBO()->Rate->StdMinCharge->AsOutput();
				$strDetailHtml .= DBO()->Rate->AvailableDays->AsArbitrary($strDaysAvailable, RENDER_OUTPUT); 
				$strDetailHtml .= DBO()->Rate->ServiceType->AsCallBack("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);
				$strDetailHtml .= DBO()->RecordType->Name->AsOutput();
				$strDetailHtml .= DBO()->Rate->AvailableTime->AsArbitrary($strTimeAvailable, RENDER_OUTPUT);
				$strDetailHtml .= DBO()->Rate->Duration->AsArbitrary($strDuration, RENDER_OUTPUT);
				$strDetailHtml .= "</div>\n";
				
				Table()->$strTableName->SetDetail($strDetailHtml);
			}
	
			foreach ($arrTables as $strTable)
			{
				// Turn on row highlighting (except this doesnt actually turn it on,
				// I think it has something to do with row highlighting not working in popups)
				Table()->$strTable->RowHighlighting = TRUE;
				Table()->$strTable->Render();
				Table()->$strTable->Info();
	
			}
		}	
	}


}

?>
