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
			echo "There are no viewable rates for this plan.";
		}
		else
		{
			echo "<div  style='overflow:scroll; height:500px'>\n";
			echo "<h2 class='Adjustment'>Rates</h2>\n";
			echo "<p>";
			
			Table()->RateTable->SetHeader("Id", "Description", "Name", "RateGroup");
			Table()->RateTable->SetWidth("10%", "30%", "30%", "30%");
			Table()->RateTable->SetAlignment("Left", "Left", "Left", "Left");
			
			$arrTables = Array();
			foreach (DBL()->RateList as $dboRate)
			{
				// Set up data for the detail divs
				DBO()->Rate->Id = $dboRate->Id->Value;
				DBO()->Rate->Load();
				DBO()->RecordType->Id = DBO()->Rate->RecordType->Value;
				DBO()->RecordType->Load();
				
				$strTableName = str_replace(Array(" ", "-"), "", $dboRate->Name->Value);
				$strTableLabel = $dboRate->RateGroup->Value . " (" . $dboRate->Name->Value . ")";
				
				if (!in_array($strTableName, $arrTables))
				{
					$arrTables[] = $strTableName;
				}
				
				//Misc strings to store things to pass as AsArbitrary
				$strCharge = OutputMask()->MoneyValue(DBO()->Rate->StdRatePerUnit->Value, 2, TRUE) . " per " . DBO()->Rate->StdUnits->Value . " " . $GLOBALS['RecordDisplayRateSuffix'][DBO()->RecordType->DisplayType->Value]; 
				$strDaysAvailable = DBO()->Rate->Monday->AsValue() . " " . 
									DBO()->Rate->Tuesday->AsValue() . " " . 
									DBO()->Rate->Wednesday->AsValue() . " " . 
									DBO()->Rate->Thursday->AsValue() . " " . 
									DBO()->Rate->Friday->AsValue() . " " . 
									DBO()->Rate->Saturday->AsValue() . " " . 
									DBO()->Rate->Sunday->AsValue();
				$strDaysAvailable = str_replace("\n", "", $strDaysAvailable);
				$strDaysUnformatted = 	DBO()->Rate->Monday->FormattedValue() . " " . 
										DBO()->Rate->Tuesday->FormattedValue() . " " . 
										DBO()->Rate->Wednesday->FormattedValue() . " " . 
										DBO()->Rate->Thursday->FormattedValue() . " " . 
										DBO()->Rate->Friday->FormattedValue() . " " . 
										DBO()->Rate->Saturday->FormattedValue() . " " . 
										DBO()->Rate->Sunday->FormattedValue();
				$strTimeAvailable = date("g:i:s A", strtotime(DBO()->Rate->StartTime->Value)) . " to " . date("g:i:s A", strtotime(DBO()->Rate->EndTime->Value));
				$strHours = gmdate("G", (strtotime(DBO()->Rate->EndTime->Value) - strtotime(DBO()->Rate->StartTime->Value))) + 1;
				$strMinutes = gmdate("i", (strtotime(DBO()->Rate->EndTime->Value) - strtotime(DBO()->Rate->StartTime->Value)) + 1);
				$strDuration = $strHours . " hours, " . $strMinutes . " minutes";
				
				Table()->$strTableName->SetHeader($strTableLabel);
				Table()->$strTableName->SetAlignment("Left");
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
				
				//echo "<br>";
				Table()->$strTable->RowHighlighting = TRUE;
				Table()->$strTable->Render();
				Table()->$strTable->Info();
	
			}
		}	
	}


}

?>
