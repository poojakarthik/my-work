<?php
//----------------------------------------------------------------------------//
// HtmlTemplaterateadd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplaterateadd
 *
 * A specific HTML Template object
 *
 * An rate add HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplaterateadd
 * @extends	HtmlTemplate
 */
class HtmlTemplaterateadd extends HtmlTemplate
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
		$this->LoadJavascript("date_selection");
		$this->LoadJavascript("rate_add");
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
		echo "<div  style='overflow:auto; height:600px'>\n";
		echo "<div class='PopupLarge' style='width:auto;'>\n";
		// define javascript to be triggered when the Cap and Excess radiobuttons change
		$strRateCapOnClick = 'Vixen.RateAdd.RateCapOnChange(this.value)';
		$this->FormStart("AddRate", "Rate", "Add");
		
		// Include the flag which specifies whether this Rate will be added to a RateGroup
		DBO()->CallingPage->AddRateGroup->RenderHidden();
		
		// Load the RecordType record relating to this rate
		DBO()->RecordType->Id = DBO()->Rate->RecordType->Value;
		DBO()->RecordType->Load();
		
		DBO()->Rate->ServiceType = DBO()->RecordType->ServiceType->Value;
		
		DBO()->Rate->ServiceType->RenderHidden();
		DBO()->Rate->RecordType->RenderHidden();
		if (DBO()->Rate->Id->Value)
		{
			DBO()->Rate->Id->RenderHidden();
		}
		
		echo "<div class='NarrowContent'>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%' rowspan=10>&nbsp;</td><td width='98%'>".DBO()->Rate->Name->AsInput()."</td></tr>\n";
		echo "<tr><td>".DBO()->Rate->Description->AsInput()."</td></tr>\n";
		echo "<tr><td>".DBO()->Rate->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT)."</td></tr>\n";
		echo "<tr><td>".DBO()->RecordType->Description->AsOutput()."</td></tr>\n";
		
		// check context of recordtype and compare with destination
		// Retrieve destinations associated with this Record Type
		$selDestinations = new StatementSelect("Destination", "Code, Description", "Context=<Context>", "Description");
		$selDestinations->Execute(Array('Context' => DBO()->RecordType->Context->Value));
		$arrDestinations = $selDestinations->FetchAll();
		
		if (count($arrDestinations) > 0)
		{
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Destination:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='Rate.Destination' style='width:250px'>\n";
			echo "         <option value='0'>&nbsp;</option>";
			foreach ($arrDestinations as $arrDestination)
			{
				// flag this option as being selected, if it is the currently selected destination
				$strSelected = (DBO()->Rate->Destination->Value == $arrDestination['Code']) ? "selected='selected'" : "";
				echo "         <option value='". $arrDestination['Code'] ."' $strSelected>". $arrDestination['Description'] ."</option>";
			}

			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";		
		}
		
		//DBO()->Rate->StartTime = "00:00";
		//DBO()->Rate->EndTime = "00:59";
		//DBO()->Rate->Duration = "00:59";

		if (!DBO()->Rate->StartTime->IsSet)
		{
			DBO()->Rate->StartTime = "00:00";
		}
		if (!DBO()->Rate->EndTime->IsSet)
		{
			DBO()->Rate->EndTime = "00:59";
		}

		echo "<tr><td>".DBO()->Rate->StartTime->AsInput()."</td></tr>\n";
		echo "<tr><td>".DBO()->Rate->EndTime->AsInput()."</td></tr>\n";
		echo "<tr><td>".DBO()->Rate->Duration->AsInput()."</td></tr>\n";
		echo "<tr><td>\n";

		echo "<div class='Seperator'></div>\n";
		//----------------------------------------
	
		echo "<script type='text/javascript'>new weekPlanner(document.getElementById ('weekScheduler_Container'))</script>\n";
		echo "<div id='weekScheduler_Constraint'>\n";
		echo "	<div id='weekScheduler_Container'>\n";
		
		echo "		<div id='weekScheduler_Meridians' class='Meridian'>\n";
		echo "			<div>AM</div>\n";
		echo "			<div>PM</div>\n";
		echo "		</div>\n";
		
		echo "		<div id='weekScheduler_Hours' class='Hour'>\n";
		echo "			<div>12</div>\n";
	  	echo "			<div>1</div>\n";
	  	echo "			<div>2</div>\n";
	  	echo "			<div>3</div>\n";
	  	echo "			<div>4</div>\n";
	  	echo "			<div>5</div>\n";
	  	echo "			<div>6</div>\n";
	  	echo "			<div>7</div>\n";
	  	echo "			<div>8</div>\n";
	  	echo "			<div>9</div>\n";
	  	echo "			<div>10</div>\n";
	  	echo "			<div>11</div>\n";
	  	echo "			<div>12</div>\n";
	  	echo "			<div>1</div>\n";
	  	echo "			<div>2</div>\n";
	  	echo "			<div>3</div>\n";
	  	echo "			<div>4</div>\n";
	  	echo "			<div>5</div>\n";
	  	echo "			<div>6</div>\n";
	  	echo "			<div>7</div>\n";
	  	echo "			<div>8</div>\n";
	  	echo "			<div>9</div>\n";
	  	echo "			<div>10</div>\n";
	  	echo "			<div>11</div>\n";
        echo "		</div>\n";

		echo "		<div id='weekScheduler_Content'>\n";
		echo "			<div id='weekScheduler_12AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_01AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_02AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_03AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_04AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_05AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_06AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_07AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_08AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_09AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_10AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_11AM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_12PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_01PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_02PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_03PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_04PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_05PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_06PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_07PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_08PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_09PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_10PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "			<div id='weekScheduler_11PM' class='weekScheduler_SelectableTime'></div>\n";
		echo "		</div>\n";

		echo "	</div>\n";
		echo "</div>\n";

		//----------------------------------------------------
		echo "</td></tr>";
		echo "<tr><td>";
		
		echo "<div class='Seperator'></div>\n";
	
			echo "<table width='576' border=1 cellpadding=3 cellspacing=0>\n";
			echo "<tr><td><span class='DefaultOutputSpan'>MONDAY</span></td><td><span class='DefaultOutputSpan'>TUESDAY</span></td>";
			echo "<td><span class='DefaultOutputSpan'>WEDNESDAY</span></td><td><span class='DefaultOutputSpan'>THURSDAY</span></td><td>";
			echo "<span class='DefaultOutputSpan'>FRIDAY</span></td><td><span class='DefaultOutputSpan'>SATURDAY</span></td><td>";
			echo "<span class='DefaultOutputSpan'>SUNDAY</span></td></tr>\n";
			
			echo "<tr><td><input type='checkbox' name='Rate.Monday'". (DBO()->Rate->Monday->Value == TRUE ? "checked='checked'" : "") ."></input>";
			echo "</td><td><input type='checkbox' name='Rate.Tuesday'". (DBO()->Rate->Tuesday->Value == TRUE ? "checked='checked'" : "") ."></input>";
			echo "</td><td><input type='checkbox' name='Rate.Wednesday'". (DBO()->Rate->Wednesday->Value == TRUE ? "checked='checked'" : "") ."></input>";
			echo "</td><td><input type='checkbox' name='Rate.Thursday'". (DBO()->Rate->Thursday->Value == TRUE ? "checked='checked'" : "") ."></input>";
			echo "</td><td><input type='checkbox' name='Rate.Friday'". (DBO()->Rate->Friday->Value == TRUE ? "checked='checked'" : "") ."></input>";
			echo "</td><td><input type='checkbox' name='Rate.Saturday'". (DBO()->Rate->Saturday->Value == TRUE ? "checked='checked'" : "") ."></input>";
			echo "</td><td><input type='checkbox' name='Rate.Sunday'". (DBO()->Rate->Sunday->Value == TRUE ? "checked='checked'" : "") ."></input>";
			echo "</td></tr>\n";
			echo "</table>\n";
					
		echo "</table>\n";
		echo "</div>\n";	
	
		echo "<div class='Seperator'></div>\n";	

		switch (DBO()->Rate->ChargeType->Value)
		{
			case RATE_CAP_STANDARD_RATE_PER_UNIT:
				$mixChargeStatus = RATE_CAP_STANDARD_RATE_PER_UNIT;
				break;
			case RATE_CAP_STANDARD_MARKUP:
				$mixChargeStatus = RATE_CAP_STANDARD_MARKUP;
				break;
			case RATE_CAP_STANDARD_PERCENTAGE:
				$mixChargeStatus = RATE_CAP_STANDARD_PERCENTAGE;
				break;
			default:
				$mixChargeStatus = RATE_CAP_STANDARD_RATE_PER_UNIT;
				break;
		}

		echo "<div class='NarrowContent'>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%'>&nbsp;</td><td width='56%'>".DBO()->Rate->StdUnits->AsInput()."</td><td>&nbsp;</td></tr>\n";
		//echo "<tr><td><input type='radio' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_RATE_PER_UNIT."'". ($mixChargeStatus == RATE_CAP_STANDARD_RATE_PER_UNIT ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->StdRatePerUnit->AsInput()."</td><td><span class='DefaultOutputSpan'>per Standard Unit</span></td></tr>\n";$strLayout = 'popup_layout';
		echo "<tr><td><input type='radio' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_RATE_PER_UNIT."'". ($mixChargeStatus == RATE_CAP_STANDARD_RATE_PER_UNIT ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->StdRatePerUnit->AsInput()."</td><td><span class='DefaultOutputSpan'>per Standard Unit</span></td></tr>\n";$strLayout = 'popup_layout';
		echo "<tr><td><input type='radio' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_MARKUP."'". ($mixChargeStatus == RATE_CAP_STANDARD_MARKUP ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->StdMarkup->AsInput()."</td><td><span class='DefaultOutputSpan'>per Standard Unit</span></td></tr>\n";
		echo "<tr><td><input type='radio' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_PERCENTAGE."'". ($mixChargeStatus == RATE_CAP_STANDARD_PERCENTAGE ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->StdPercentage->AsInput()."</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>".DBO()->Rate->StdMinCharge->AsInput()."</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>".DBO()->Rate->StdFlagfall->AsInput()."</td><td>&nbsp;</td></tr>\n";
		echo "</table>\n";
		echo "</div>\n";
		
		echo "<div class='Seperator'></div>\n";		

		switch (DBO()->Rate->CapCalculation->Value)
		{
			case RATE_CAP_NO_CAP:
				$mixCalculationStatus = RATE_CAP_NO_CAP;
				break;
			case RATE_CAP_CAP_UNITS:
				$mixCalculationStatus = RATE_CAP_CAP_UNITS;
				break;
			case RATE_CAP_CAP_COST:
				$mixCalculationStatus = RATE_CAP_CAP_COST;
				break;
			default:
				$mixCalculationStatus = RATE_CAP_NO_CAP;
				break;
		}

		echo "<div class='NarrowContent'>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "<tr><td width='2%'><input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_NO_CAP."'". ($mixCalculationStatus == RATE_CAP_NO_CAP ? "checked='checked'" : "") ." onchange=\"$strRateCapOnClick\"></td><td><span class='DefaultOutputSpan'>&nbsp;&nbsp;No Cap</span></td><td width='58%'>&nbsp;</td></tr>\n";
		echo "<tr><td width='2%'><input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_CAP_UNITS."'". ($mixCalculationStatus == RATE_CAP_CAP_UNITS || DBO()->Rate->CapUnits->Value != 0  ? "checked='checked'" : "") ." onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapUnits->AsInput()."</td><td width='58%'>&nbsp;</td></tr>\n";
		echo "<tr><td width='2%'><input type='radio' name='Rate.CapCalculation' value='".RATE_CAP_CAP_COST."'". ($mixCalculationStatus == RATE_CAP_CAP_COST || DBO()->Rate->CapCost->Value != 0 ? "checked='checked'" : "") ." onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapCost->AsInput()."</td><td width='58%'>&nbsp;</td></tr>\n";
		echo "</table>\n";

		// conditional if any of the exs fields in the database are not 0 then show the cap chargers & excess charges DIV
		$bolShowExcessDiv = FALSE;
		if ((DBO()->Rate->ExsUnits->Value != 0)||(DBO()->Rate->ExsRatePerUnit->Value != 0)||(DBO()->Rate->ExsFlagfall->Value != 0)||(DBO()->Rate->ExsPercentage->Value != 0)||(DBO()->Rate->ExsMarkup->Value != 0))
		{
			$bolShowExcessDiv = TRUE;
		}

		if ((DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST)||(DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS)||$bolShowExcessDiv)
		{	
			$mixCapStatus = DBO()->Rate->CapLimitting->Value;
			echo "<div id='CapDetailDiv' style='display:inline'>\n";		
		}
		else
		{
			$mixCapStatus = RATE_CAP_NO_CAP_LIMITS;
			echo "<div id='CapDetailDiv' style='display:none'>\n";
		}
			// cap usage and cap limit specific detail
				echo "<div class='Seperator'></div>\n";
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_NO_CAP_LIMITS."'". ($mixCapStatus == RATE_CAP_NO_CAP_LIMITS ? "checked='checked'" : "") ." onchange=\"$strRateCapOnClick\"></td><td><span class='DefaultOutputSpan'>&nbsp;&nbsp;No Cap Limits</span></td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_CAP_LIMIT."'". ($mixCapStatus == RATE_CAP_CAP_LIMIT || DBO()->Rate->CapLimit->Value != 0 ? "checked='checked'" : "") ." onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapLimit->AsInput()."</td></tr>\n";		
				echo "<tr><td width='2%'><input type='radio' name='Rate.CapLimitting' value='".RATE_CAP_CAP_USAGE."'". ($mixCapStatus == RATE_CAP_CAP_USAGE || DBO()->Rate->CapUsage->Value != 0 ? "checked='checked'" : "") ." onchange=\"$strRateCapOnClick\"></td><td>".DBO()->Rate->CapUsage->AsInput()."</td></tr>\n";
				echo "</table>\n";		
			echo "</div>\n";	

		if ((DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_USAGE)||($bolShowExcessDiv))
		{	
			$mixCapLimittingStatus = DBO()->Rate->ExsChargeType->Value;
			echo "<div id='ExcessDetailDiv' style='display:inline'>\n";	
			// problem with displaying excess and not cap, clicking no cap closes both DIVs		
		}
		else
		{
			$mixCapLimittingStatus = RATE_CAP_EXS_RATE_PER_UNIT;
			echo "<div id='ExcessDetailDiv' style='display:none'>\n";
		}
			// excess rate and markup specific detail
				echo "<div class='Seperator'></div>\n";
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='2%'>&nbsp;</td><td width='56%'>".DBO()->Rate->ExsUnits->AsInput()."</td><td width='55%'>&nbsp;</td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_RATE_PER_UNIT."'". ($mixCapLimittingStatus == RATE_CAP_EXS_RATE_PER_UNIT || DBO()->Rate->ExsRatePerUnit->Value != 0 ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->ExsRatePerUnit->AsInput()."</td><td><span class='DefaultOutputSpan'>per Standard Unit</span></td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_MARKUP."'". ($mixCapLimittingStatus == RATE_CAP_EXS_MARKUP || DBO()->Rate->ExsMarkup->Value != 0 ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->ExsMarkup->AsInput()."</td><td><span class='DefaultOutputSpan'>per Standard Unit</span></td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_PERCENTAGE."'". ($mixCapLimittingStatus == RATE_CAP_EXS_PERCENTAGE || DBO()->Rate->ExsPercentage->Value != 0 ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->ExsPercentage->AsInput()."</td><td>&nbsp;</td></tr>\n";
				echo "<tr><td width='2%'>&nbsp;</td><td>&nbsp;&nbsp;".DBO()->Rate->ExsFlagfall->AsInput()."</td><td>&nbsp;</td></tr>\n";	
				echo "</table>\n";	
			echo "</div>\n";
		
		echo "</div>\n";

		echo "<div class='Seperator'></div>\n";	

		echo "<div class='NarrowContent'>\n";
			echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
			echo "<tr><td>".DBO()->Rate->Prorate->AsInput()."</td></tr>\n";
			echo "<tr><td>".DBO()->Rate->Fleet->AsInput()."</td></tr>\n";
			echo "<tr><td>".DBO()->Rate->Uncapped->AsInput()."</td></tr>\n";
			echo "</table>\n";	
		echo "</div>\n";	

		echo "<div class='Seperator'></div>\n";	

		echo "<div class='right'>\n";
			$this->AjaxSubmit("Add");
			$this->AjaxSubmit("Save as Draft");
		echo "</div>\n";
		$this->FormEnd();
		echo "</div>\n"; // PopupLarge
		echo "</div>\n"; // scrollbar div
	}
}

?>
