<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// add
//----------------------------------------------------------------------------//
/**
 * add
 *
 * contains the HtmlTemplateRateAdd class, which is used to add/edit a rate
 *
 * contains the HtmlTemplateRateAdd class, which is used to add/edit a rate
 *
 * @file		add.php
 * @language	PHP
 * @package		framework
 * @author		Ross Mullen
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateRateAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateAdd
 *
 * HtmlTemplate for adding/editing a Rate
 *
 * HtmlTemplate for adding/editing a Rate
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateRateAdd
 * @extends	HtmlTemplate
 */
 
class HtmlTemplateRateAdd extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContextgroup_list.php
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
	 * Constructor - java script required by the HTML object is declared here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
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
		// Render the container divs
		echo "<div id='ContainerDiv_FormContainerDiv_RateAdd' style='border: solid 1px #606060; padding: 5px 5px 5px 5px'>\n";
		echo "<div id='FormContainerDiv_RateAdd' style='overflow:auto; height:530px; width:auto; padding: 0px 3px 0px 3px'>\n";
			
			$this->FormStart("AddRate", "Rate", "Add");
			
			// Include the flag which specifies whether this Rate will be added to a RateGroup
			DBO()->CallingPage->AddRateGroup->RenderHidden();
			
			// Load the RecordType record relating to this rate
			DBO()->RecordType->Id = DBO()->Rate->RecordType->Value;
			DBO()->RecordType->Load();
			
			DBO()->Rate->ServiceType = DBO()->RecordType->ServiceType->Value;
			
			DBO()->Rate->ServiceType->RenderHidden();
			DBO()->Rate->RecordType->RenderHidden();
			DBO()->Rate->Fleet->RenderHidden();
			
			DBO()->Rate->Id->RenderHidden();
			echo "<div class='NarrowContent'>\n"; //beginning of the DIV for the rate name and duration
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='2%' rowspan=10>&nbsp;</td><td width='98%'>".DBO()->Rate->Name->AsInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("style:width"=>"380px"))."</td></tr>\n";
				echo "<tr><td>".DBO()->Rate->Description->AsInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"380px"))."</td></tr>\n";
				echo "<tr><td>".DBO()->Rate->ServiceType->AsCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT, CONTEXT_DEFAULT)."</td></tr>\n";
				echo "<tr><td>".DBO()->RecordType->Description->AsOutput(CONTEXT_DEFAULT,TRUE)."</td></tr>\n";
				DBO()->Rate->Fleet->RenderOutput();
				
				// check context of recordtype and compare with destination
				// Retrieve destinations associated with this Record Type
				$selDestinations = new StatementSelect("Destination", "Code, Description", "Context=<Context>", "Description");
				$selDestinations->Execute(Array('Context' => DBO()->RecordType->Context->Value));
				$arrDestinations = $selDestinations->FetchAll();
				
				if (count($arrDestinations) > 0)
				{
					echo "<div class='DefaultElement'>\n"; //beginning of the DIV for the drop-down destination
					echo "   <div class='DefaultLabel'><span class='RequiredInput'>*</span> Destination:</div>\n"; //beginning and end of the DIV for the destination label
					echo "   <div class='DefaultOutput'>\n"; //beginning of the DIV for the drop-down destination1
					echo "      <select name='Rate.Destination' style='width:250px'>\n";
					echo "         <option value='0'>&nbsp;</option>";
					foreach ($arrDestinations as $arrDestination)
					{
						// flag this option as being selected, if it is the currently selected destination
						// used for when the page reloads through AJAX, comparing the selected destination on the initial check
						// to the selection of when the selectbox is redrawn if matched flag this as the selected option
						$strSelected = (DBO()->Rate->Destination->Value == $arrDestination['Code']) ? "selected='selected'" : "";
						echo "         <option value='". $arrDestination['Code'] ."' $strSelected>". $arrDestination['Description'] ."</option>";
					}
		
					echo "      </select>\n";
					echo "   </div>\n"; //beginning of the DIV for the drop-down destination1
					echo "</div>\n"; //beginning of the DIV for the drop-down destination1		
				}
				
				// if the start and endtime are not set, set them to the default as it
				// allows the Javascript in the date_time.js (responsible for the movable time bar)
				// to calculate the initial values, otherwise NaN error is encountered
				if (!DBO()->Rate->StartTime->IsSet)
				{
					DBO()->Rate->StartTime = "00:00:00";
				}
				if (!DBO()->Rate->EndTime->IsSet)
				{
					DBO()->Rate->EndTime = "00:59:59";
				}
		
				echo "<tr><td>".DBO()->Rate->StartTime->AsInput()."</td></tr>\n";
				echo "<tr><td>".DBO()->Rate->EndTime->AsInput()."</td></tr>\n";
				echo "<tr><td>".DBO()->Rate->Duration->AsInput()."</td></tr>\n";
				echo "<tr><td>\n";
		
				echo "<div class='Seperator'></div>\n";
				
				// Beginning of the Javascript movable time bar
				// note the inline script tag, this enables the bar to reset itself if the page
				// has been reloaded through AJAX, for example if a field is invalid
			
				echo "<script type='text/javascript'>new weekPlanner(document.getElementById('weekScheduler_Container'))</script>\n";
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
		
				// End of the Javascript movable time bar
				
				echo "</td></tr>";
				echo "<tr><td>";
				
				echo "<div class='Seperator'></div>\n";
			
					echo "<table width='576' border=1 cellpadding=3 cellspacing=0>\n";
					echo "<tr><td><span class='DefaultOutputSpan'>MONDAY</span></td><td><span class='DefaultOutputSpan'>TUESDAY</span></td>";
					echo "<td><span class='DefaultOutputSpan'>WEDNESDAY</span></td><td><span class='DefaultOutputSpan'>THURSDAY</span></td><td>";
					echo "<span class='DefaultOutputSpan'>FRIDAY</span></td><td><span class='DefaultOutputSpan'>SATURDAY</span></td><td>";
					echo "<span class='DefaultOutputSpan'>SUNDAY</span></td></tr>\n";
					
					// inline if statement, determining if the value for any given day is TRUE check the check box, again this is for
					// a AJAX reload if the page has been reloaded through an invalid field
					echo "<tr><td><input type='checkbox' name='Rate.Monday'".
							(DBO()->Rate->Monday->Value == TRUE ? "checked='checked'" : "") ."></input>";
					echo "</td><td><input type='checkbox' name='Rate.Tuesday'". 
							(DBO()->Rate->Tuesday->Value == TRUE ? "checked='checked'" : "") ."></input>";
					echo "</td><td><input type='checkbox' name='Rate.Wednesday'". 
							(DBO()->Rate->Wednesday->Value == TRUE ? "checked='checked'" : "") ."></input>";
					echo "</td><td><input type='checkbox' name='Rate.Thursday'". 
							(DBO()->Rate->Thursday->Value == TRUE ? "checked='checked'" : "") ."></input>";
					echo "</td><td><input type='checkbox' name='Rate.Friday'". 
							(DBO()->Rate->Friday->Value == TRUE ? "checked='checked'" : "") ."></input>";
					echo "</td><td><input type='checkbox' name='Rate.Saturday'". 
							(DBO()->Rate->Saturday->Value == TRUE ? "checked='checked'" : "") ."></input>";
					echo "</td><td><input type='checkbox' name='Rate.Sunday'". 
							(DBO()->Rate->Sunday->Value == TRUE ? "checked='checked'" : "") ."></input>";
					echo "</td></tr>\n";
					echo "</table>\n";
							
				echo "</table>\n";
			echo "</div>\n"; //end of the DIV for the rate name and duration
		
			echo "<div class='Seperator'></div>\n";	
	
			// For the Std Rate properties determine which of the radio buttons has been selected
			if (DBO()->Rate->ChargeType->IsSet)
			{
				$intChargeStatus = DBO()->Rate->ChargeType->Value;
			}
			else
			{
				if (DBO()->Rate->StdPercentage->Value > 0)
				{
					$intChargeStatus = RATE_CAP_STANDARD_PERCENTAGE;
				}
				elseif (DBO()->Rate->StdMarkup->Value > 0)
				{
					$intChargeStatus = RATE_CAP_STANDARD_MARKUP;
				}
				else
				{
					$intChargeStatus = RATE_CAP_STANDARD_RATE_PER_UNIT;
				}
			}

			// PassThrough div
			echo "<div class='NarrowContent' style='padding-left:25px'>\n";
			DBO()->Rate->PassThrough->RenderInput(CONTEXT_DEFAULT, TRUE);
			DBO()->Rate->Uncapped->RenderInput(CONTEXT_DEFAULT, TRUE);
			
			// The Rate.Untimed and Rate.Prorate properties require a container div, as they can be hidden (They are hidden when PassThrough is set)
			$strDisplay = (DBO()->Rate->PassThrough->Value) ? "style='display:none'" : "";
			echo "<div id='ContainerDiv_RateUntimed' $strDisplay>";
			DBO()->Rate->Prorate->RenderInput(CONTEXT_DEFAULT, TRUE);
			DBO()->Rate->Untimed->RenderInput(CONTEXT_DEFAULT, TRUE);
			echo "</div>";
			DBO()->Rate->StdMinCharge->RenderInput(CONTEXT_DEFAULT, TRUE);
			DBO()->Rate->StdFlagfall->RenderInput(CONTEXT_DEFAULT, TRUE);
			
			// HACK! HACK! HACK! I'm doing this so it truncates the float to 2 decimal places.  All other properties are "Decimals", and don't have this problem
			DBO()->Rate->discount_percentage = OutputMask()->FormatFloat(DBO()->Rate->discount_percentage->Value, 2, 2);
						
			DBO()->Rate->discount_percentage->RenderInput(CONTEXT_DEFAULT);
			echo "</div>\n"; // PassThrough

			echo "<div id='RateDetailDiv' style='display:inline'>\n"; //beginning of the ratedetail DIV
			echo "<div class='Seperator'></div>\n";	
			
				echo "<div class='NarrowContent'>\n"; //beginning of the stdunits DIV
					echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
					
					// Work out what type of unit this RecordType has
					$strUnitSuffix = GetConstantDescription(DBO()->RecordType->DisplayType->Value, "DisplayTypeSuffix");
					
					echo "<tr height='24px'><td width='2%'>&nbsp;</td>";
					echo "<td width='56%'>". DBO()->Rate->StdUnits->AsInput(CONTEXT_DEFAULT, TRUE) ."</td>";
					echo "<td>";
					echo "<span id='RateAdd_StdUnitSuffix' class='DefaultOutputSpan' UnitSuffix='$strUnitSuffix'>$strUnitSuffix</span>";
					echo "</td></tr>\n";
					
					// set the 'state' of the radio button if this is selected then check it status else don't, used for when
					// page is reloaded through AJAX in use for stdmarkup and stdpercentage and also the cap charges and excess rate
					// charges, the line is split for readability for that one line retaining its full line length makes program logic
					// easier to understand
					
					echo "<tr height='24px'>\n";
					$strChecked = ($intChargeStatus == RATE_CAP_STANDARD_RATE_PER_UNIT) ? "checked='checked'" : "";
					echo "<td><input type='radio' id='Radio_StdCharge' name='Rate.ChargeType' value='". RATE_CAP_STANDARD_RATE_PER_UNIT."' $strChecked></td>\n";
					echo "<td>". DBO()->Rate->StdRatePerUnit->AsInput() ."</td>\n";
					echo "<td><span id='RateAdd_StdRatePerUnitSuffix' class='DefaultOutputSpan'>Per X $strUnitSuffix</span></td>\n";
					echo "</tr>\n";
					
					echo "<tr height='24px'>\n";
					$strChecked = ($intChargeStatus == RATE_CAP_STANDARD_MARKUP) ? "checked='checked'" : "";
					echo "<td><input type='radio' id='Radio_StdMarkup' name='Rate.ChargeType' value='".RATE_CAP_STANDARD_MARKUP."' $strChecked></td>\n";
					echo "<td>". DBO()->Rate->StdMarkup->AsInput() ."</td>\n";
					echo "<td><span id='RateAdd_StdMarkupSuffix' class='DefaultOutputSpan'>Per X $strUnitSuffix</span></td>\n";
					echo "</tr>\n";
					
					echo "<tr height='24px'>\n";
					$strChecked = ($intChargeStatus == RATE_CAP_STANDARD_PERCENTAGE) ? "checked='checked'" : "";
					echo "<td><input type='radio' id='Radio_StdPercentage' name='Rate.ChargeType' value='". RATE_CAP_STANDARD_PERCENTAGE."' $strChecked></td>\n";
					echo "<td>".DBO()->Rate->StdPercentage->AsInput()."</td><td>&nbsp;</td>\n";
					echo "</tr>\n";
					
					echo "</table>\n";
				echo "</div>\n"; //end of the stdunits DIV
			
			echo "<div class='Seperator'></div>\n";
			
			echo "</div>\n"; // end of ratedetail DIV
			
			// For the Rate Cap properties determine which of the radio buttons has been selected
			if (DBO()->Rate->CapCalculation->IsSet)
			{
				$intCalculationStatus = DBO()->Rate->CapCalculation->Value;
			}
			else
			{
				if (DBO()->Rate->CapUnits->Value > 0)
				{
					// Cap Units has been set
					$intCalculationStatus = RATE_CAP_CAP_UNITS;
				}
				elseif (DBO()->Rate->CapCost->Value > 0)
				{
					// Cap Cost has been set
					$intCalculationStatus = RATE_CAP_CAP_COST;
				}
				elseif (DBO()->Rate->CapLimit->Value > 0 || DBO()->Rate->CapUsage->Value > 0)
				{
					// If a cap limit (CapLimit or CapUsage) has been set but neither CapUnits or CapCost have been set
					// then mark CapUnits as having been set
					$intCalculationStatus = RATE_CAP_CAP_UNITS;
				}
				else
				{
					$intCalculationStatus = RATE_CAP_NO_CAP;
				}
			}
			
			echo "<div id='CapMainDetailDiv' style='display:inline'>\n"; // beginning of capmaindetail DIV
				echo "<div class='NarrowContent'>\n"; //beginning of capdetail DIV
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='2%'><input type='radio' id='Radio_NoCap'    name='Rate.CapCalculation' value='". RATE_CAP_NO_CAP ."' ". ($intCalculationStatus == RATE_CAP_NO_CAP ? "checked='checked'" : "") . "></td><td><span class='DefaultOutputSpan'>&nbsp;&nbsp;No Capping</span></td><td>&nbsp;</td></tr>\n";
				
				echo "<tr><td width='2%'><input type='radio' id='Radio_CapUnits' name='Rate.CapCalculation' value='". RATE_CAP_CAP_UNITS ."' ". ($intCalculationStatus == RATE_CAP_CAP_UNITS ? "checked='checked'" : "") . "></td>";
				echo "<td>". DBO()->Rate->CapUnits->AsInput() ."</td>";
				echo "<td width='41%'><span class='DefaultOutputSpan'>$strUnitSuffix</span></td></tr>\n";
				
				echo "<tr><td width='2%'><input type='radio' id='Radio_CapCost'  name='Rate.CapCalculation' value='". RATE_CAP_CAP_COST ."'". ($intCalculationStatus == RATE_CAP_CAP_COST ? "checked='checked'" : "") .	"></td>";
				echo "<td>". DBO()->Rate->CapCost->AsInput() ."</td>";
				echo "<td width='41%'><span class='DefaultOutputSpan'>Dollars</span></td></tr>\n";
				echo "</table>\n";
	
				// work out which of the cap limit radio buttons should be selected
				if (DBO()->Rate->CapLimitting->IsSet)
				{
					$intCapStatus = DBO()->Rate->CapLimitting->Value;
				}
				else
				{	
					if (DBO()->Rate->CapUsage->Value > 0)
					{
						$intCapStatus = RATE_CAP_CAP_USAGE;
					}
					elseif (DBO()->Rate->CapLimit->Value > 0)
					{
						$intCapStatus = RATE_CAP_CAP_LIMIT;
					}
					else
					{
						$intCapStatus = RATE_CAP_NO_CAP_LIMITS;
					}
				}
				
				if (($intCalculationStatus == RATE_CAP_CAP_COST) || ($intCalculationStatus == RATE_CAP_CAP_UNITS))
				{	
					echo "<div id='CapDetailDiv' style='display:inline;'>\n"; //beginning of expandingcapdetail DIV
				}
				else
				{
					echo "<div id='CapDetailDiv' style='display:none;'>\n"; //beginning of expandingcapdetail DIV
				}

				// cap usage and cap limit specific detail
				echo "<div class='Seperator'></div>\n";
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='2%'><input type='radio' id='Radio_NoCapLimit'    name='Rate.CapLimitting' value='".RATE_CAP_NO_CAP_LIMITS."'". ($intCapStatus == RATE_CAP_NO_CAP_LIMITS ? "checked='checked'" : "") ."></td><td><span class='DefaultOutputSpan'>&nbsp;&nbsp;No Cap Limit</span></td><td width='41%'>&nbsp;</td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' id='Radio_CapUsageLimit' name='Rate.CapLimitting' value='".RATE_CAP_CAP_USAGE."'". ($intCapStatus == RATE_CAP_CAP_USAGE ? "checked='checked'" : "") ."></td>";
				echo "<td>". DBO()->Rate->CapUsage->AsInput() ."</td>";
				echo "<td width='41%'><span class='DefaultOutputSpan'>$strUnitSuffix</span></td></tr>\n";
				echo "<tr><td width='2%'><input type='radio' id='Radio_CapCostLimit'  name='Rate.CapLimitting' value='".RATE_CAP_CAP_LIMIT."'". ($intCapStatus == RATE_CAP_CAP_LIMIT ? "checked='checked'" : "") ."></td>";
				echo "<td>". DBO()->Rate->CapLimit->AsInput() ."</td>";
				echo "<td width='41%'><span class='DefaultOutputSpan'>Dollars</span></td></tr>\n";		
				echo "</table>\n";
				
				// Display the Excess Flagfall textbox (but hide it if it shouldn't be displayed)
				$strStyleDisplay = (($intCapStatus == RATE_CAP_CAP_LIMIT) || ($intCapStatus == RATE_CAP_CAP_USAGE)) ? "style='display:inline;'" : "style='display:none;'";
				echo "<div id='ExsFlagfallDiv' $strStyleDisplay>";
				echo "<div style='padding-left:21px;'>";
				DBO()->Rate->ExsFlagfall->RenderInput();
				echo "</div></div>\n";
				
				echo "</div>\n"; //end of expandingcapdetail DIV
		
				// Work out which of the excess rate radio buttons should be selected
				if (DBO()->Rate->ExsChargeType->IsSet)
				{
					$intCapExcessChargeType = DBO()->Rate->ExsChargeType->Value;
				}
				else
				{	
					if (DBO()->Rate->ExsRatePerUnit->Value > 0)
					{
						$intCapExcessChargeType = RATE_CAP_EXS_RATE_PER_UNIT;
					}
					elseif (DBO()->Rate->ExsMarkup->Value > 0)
					{
						$intCapExcessChargeType = RATE_CAP_EXS_MARKUP;
					}
					elseif (DBO()->Rate->ExsPercentage->Value > 0)
					{
						$intCapExcessChargeType = RATE_CAP_EXS_PERCENTAGE;
					}
					else
					{
						$intCapExcessChargeType = RATE_CAP_EXS_RATE_PER_UNIT;
					}
				}
				
				if ($intCapStatus == RATE_CAP_CAP_USAGE)
				{
					echo "<div id='ExcessDetailDiv' style='display:inline;'>\n"; //beginning of expandingexsdetail DIV
				}
				else
				{
					echo "<div id='ExcessDetailDiv' style='display:none;'>\n"; //beginning of expandingexsdetail DIV
				}
				
				echo "<div class='Seperator'></div>\n";
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr height='24px'><td width='2%'>&nbsp;</td><td width='56%'>".DBO()->Rate->ExsUnits->AsInput(CONTEXT_DEFAULT, true)."</td><td width='41%'><span class='DefaultOutputSpan'>$strUnitSuffix</span></td></tr>\n";
				echo "<tr height='24px'><td width='2%'><input type='radio' id='Radio_ExsCharge'     name='Rate.ExsChargeType' value='".RATE_CAP_EXS_RATE_PER_UNIT."'". ($intCapExcessChargeType == RATE_CAP_EXS_RATE_PER_UNIT ? "checked='checked'" : "") ."></td>";
				echo "<td>". DBO()->Rate->ExsRatePerUnit->AsInput() ."</td>";
				echo "<td><span id='RateAdd_ExsRatePerUnitSuffix' class='DefaultOutputSpan'>Per X $strUnitSuffix beyond cap limit</span></td></tr>\n";
				echo "<tr height='24px'><td width='2%'><input type='radio' id='Radio_ExsMarkup'     name='Rate.ExsChargeType' value='".RATE_CAP_EXS_MARKUP."'". ($intCapExcessChargeType == RATE_CAP_EXS_MARKUP ? "checked='checked'" : "") ."></td>";
				echo "<td>". DBO()->Rate->ExsMarkup->AsInput() ."</td>";
				echo "<td><span id='RateAdd_ExsMarkupSuffix' class='DefaultOutputSpan'>Per X $strUnitSuffix beyond cap limit</span></td></tr>\n";
				echo "<tr height='24px'><td width='2%'><input type='radio' id='Radio_ExsPercentage' name='Rate.ExsChargeType' value='".RATE_CAP_EXS_PERCENTAGE."'". ($intCapExcessChargeType == RATE_CAP_EXS_PERCENTAGE ? "checked='checked'" : "") ."></td><td>".DBO()->Rate->ExsPercentage->AsInput()."</td><td>&nbsp;</td></tr>\n";
				echo "</table>\n";
				echo "</div>\n";  //end of expandingexsdetail DIV
					
			//echo "</div>\n"; //end of capdetaildiv
		echo "</div>\n"; //end of capmaindetaildiv
			
		echo "</div>\n"; // unknown closing DIV leave in as without it doesn't format correctly
		echo "</div>\n"; // unknown closing DIV leave in as without it doesn't format correctly
		
		// Stick the "Save as Draft" and "Commit" buttons in the scrollable div, 
		// forcing the user to traverse the entire length of the div, before saving the rate
		echo "<div class='ButtonContainer'><div class='right'>\n";
		// The Cancel button was originally always visible, but it was too easy to make changes to a rate, and then accidently press it, instead of saving
		$this->Button("Cancel", "Vixen.Popup.Close(this);");
		
		$this->Button("Save as Draft", "Vixen.Popup.Confirm(\"Are you sure you want to save this Rate as a Draft?\", function(){Vixen.RateAdd.SaveAsDraft();})");
		$this->Button("Commit", "Vixen.Popup.Confirm(\"Are you sure you want to commit this Rate?<br />The Rate cannot be edited once it is committed\", function(){Vixen.RateAdd.Commit();})");
		echo "</div></div>\n";  // Buttons
		
		echo "</div>\n"; // PopupLarge
		echo "</div>\n"; // ContainerDiv for the FormContainerDiv

		$this->FormEnd();
		
		// Initialise the form's associated javascript object
		echo "<script type='text/javascript'>Vixen.RateAdd.InitialiseForm('{$this->_objAjax->strId}');</script>";
	}
}

?>
