<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_override.php
//----------------------------------------------------------------------------//
/**
 * rate_group_override
 *
 * HTML Template for the Rate Group Override HTML object
 *
 * HTML Template for the Rate Group Override HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a rate group.
 *
 * @file		rate_group_override.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross 'Spudnik' Mullen
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupOverride
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateGroupOverride
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupOverride
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupOverride extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("rate_group_override");
		$this->LoadJavascript("date_time_picker");
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
	 *
	 * @method
	 */
	function Render()
	{
		echo "<div class='NarrowForm'>\n";
		
		$this->FormStart("RateGroupOverride", "RateGroup", "Override");

		// Inline Javascript that retrieves the selectlist text and enteres it in the hidden form element (no doubt an easier way to achieve this)
		$strOnRateGroupChange = "document.getElementById(\"RateGroupDescription\").innerHTML = arrDescription[this.selectedIndex];
													document.getElementById(\"RateGroup.Name\").value = this.options[this.selectedIndex].text";
		// Trap for the checking of the ImmediateStart checkbox, if checked hide StartSection DIV else show
		$strImmediateStartClick = "
													if (this.checked)
													{
														document.getElementById(\"StartSection\").style.visibility = \"hidden\";
													}
													else
													{
														document.getElementById(\"StartSection\").style.visibility = \"visible\";
													}";
		// Trap for the checking of the IndefinateEnd checkbox, if checked hide EndSection DIV else show													
		$strIndefinateEndClick = "
													if (this.checked)
													{
														document.getElementById(\"EndSection\").style.visibility = \"hidden\";
													}
													else
													{
														document.getElementById(\"EndSection\").style.visibility = \"visible\";
													}";

		DBO()->Account->Id->RenderHidden();
		DBO()->Service->Id->RenderHidden();
		DBO()->RecordType->Id->RenderHidden();

		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value != NULL)
		{
			DBO()->Account->BusinessName->RenderOutput();			
		}
		
		else
		{
			if (DBO()->Account->TradingName->Value != NULL)
			{
				DBO()->Account->TradingName->RenderOutput();
			}
		}
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->Service->FNN->RenderOutput();
		DBO()->RecordType->Description->RenderOutput();
		
		echo "<table width='100%' border='0' cellpadding='1' cellspacing='0'>\n";
		echo "<tr><td width='1' rowspan='7'>&nbsp;</td><td width='190'><span>Current Plan :</span></td><td>" . DBO()->RatePlan->Name->AsValue() . "</td></tr>\n";
		echo "<tr><td><span>Rate Group :</span></td><td>\n";

		echo "<span><select name='ServiceRateGroup.Selected' style=\"width: 100%;\" onchange='$strOnRateGroupChange'>\n";
		
		$bolFirstValueFlagged = FALSE;
		$strFirstValueShown = "";
		
		// Opening of Javascript array
		$strDescription = '[';

		// Populates the selectbox with all the Rategroups
		foreach (DBL()->RateGroup as $dboRateGroup)
		{
			// Builds a Javascript array of all the rategroup descriptions
			$strDescription .= "'" . $dboRateGroup->Description->Value . "',";
			
			// If the page is reloaded set the ServiceRateGroup.Selected selected value as the one previously selected
			// i.e. do not default back to the first selected value in the list
			if (DBO()->ServiceRateGroup->Selected->Value == $dboRateGroup->Id->Value)
			{
				echo "<option value='" . $dboRateGroup->Id->Value . "' selected>" . $dboRateGroup->Name->Value;
				$strFirstValueShown = $dboRateGroup->Description->Value;					
			}
			else
			{
				echo "<option value='" . $dboRateGroup->Id->Value . "'>" . $dboRateGroup->Name->Value;
				// If $bolFirstValueFlagged is not set
				if (!$bolFirstValueFlagged)
				{
					$strFirstValueShown = $dboRateGroup->Description->Value;
					$strValue = $dboRateGroup->Name->Value;
					$bolFirstValueFlagged = TRUE;
				}				
			}
		}
	
		// Strip the hanging comma from '$strDescription' and close the Javascript array
		$strDescription = substr($strDescription, 0, strlen($strDescription)-1);
		$strDescription .= ']';

		echo "</select></span>\n";
		echo "<input type='hidden' id='RateGroup.Name' name='RateGroup.Name' value='$strValue'>\n";

		// embedded Javascript of the Rategroup descriptions
		echo "<script language='Javascript'>\nvar arrDescription = " . $strDescription . ";\n</script>\n";	

		$strStartSection = "hidden";
		$strEndSection = "hidden";
		$strImmediateStart = "checked";
		$strIndefinateEnd = "checked";

		// If the startdate is not NULL OR (the ServiceRateGroup is invalid AND immediateStart IS NOT checked) show the StartSection DIV
		// and uncheck the immediateStart checkbox
		if (DBO()->ServiceRateGroup->StartDate->Value != NULL || (DBO()->ServiceRateGroup->IsInvalid() && DBO()->RateGroup->ImmediateStart->Value == 0))
		{
			$strStartSection = "visible";
			$strImmediateStart = "";
		}
		// If the enddate is not NULL OR (the ServiceRateGroup is invalid AND indefinateEnd IS NOT checked) show the EndSection DIV
		// and uncheck the indefinateEnd checkbox		
		if (DBO()->ServiceRateGroup->EndDate->Value != NULL || (DBO()->ServiceRateGroup->IsInvalid() && DBO()->RateGroup->IndefinateEnd->Value == 0))
		{
			$strEndSection = "visible";
			$strIndefinateEnd = "";
		}

		echo "</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td><div class='DefaultRegularOutput' id='RateGroupDescription' style='line-height: 1;'>$strFirstValueShown</div></td></tr>\n";
		echo "<tr><td><span>Immediate Start :</span></td><td><input type='checkbox' name='RateGroup.ImmediateStart' $strImmediateStart onClick='$strImmediateStartClick' style='margin-left: 1px; margin-top: 2px; outline-style:	solid; outline-width: 1px;'></td></tr>\n";
		echo "<tr><td><span>Indefinate End :</span></td><td><input type='checkbox' name='RateGroup.IndefinateEnd' $strIndefinateEnd onClick='$strIndefinateEndClick'  style='margin-left: 1px; margin-top: 2px; outline-style:	solid; outline-width: 1px;'></td></tr>\n";
		echo "</table>\n";

		echo "&nbsp;\n";

		// StartSection DIV defaults to hidden
		echo "<div id='StartSection' style='visibility: $strStartSection'>\n";
		
		DBO()->ServiceRateGroup->StartDate->RenderInput();
		
		// This code defines the Date Picker
		echo "<a href='javascript:showChooser(document.getElementById(\"ServiceRateGroup.StartDate\"), \"ServiceRateGroup.StartDate\", \"StartDateCalender\", 2007, 2037, \"d/m/Y\", false, true, true);'>Picker</a>\n";
		echo "<div id='StartDateCalender' class='date-time select-free' style='display: none; visibility: hidden;'></div>";
		
		echo "</div>\n"; //StartSection container
		
		// EndSection DIV defaults to hidden
		echo "<div id='EndSection' style='visibility: $strEndSection'>\n";		
		DBO()->ServiceRateGroup->EndDate->RenderInput();
		echo "</div>\n";

		$this->FormEnd();
		echo "</div>\n"; // Narrow form DIV

		echo "<div class='ButtonContainer'><div class='right'>\n";
			$this->Button("Cancel", "Vixen.Popup.Close(this);");
			$this->AjaxSubmit("Apply Changes");
		echo "</div>\n";	
	}
}

?>
