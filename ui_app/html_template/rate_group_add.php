<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_add.php
//----------------------------------------------------------------------------//
/**
 * rate_group_add
 *
 * HTML Template for the Add Rate Group HTML object
 *
 * HTML Template for the Add Rate Group HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a rate group.
 *
 * @file		rate_group_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateGroupAdd
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupAdd extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("rate_group_add");
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
		switch ($this->_intContext)
		{
		case HTML_CONTEXT_DETAILS:
			$this->_RenderRateGroupDetails();
			break;
		case HTML_CONTEXT_RATES:
			$this->_RenderRateSelectorControl();
			break;
		case HTML_CONTEXT_DEFAULT:
		default:
			echo "<div class='PopupLarge'>\n";
		
			// Set Up the form for adding a rate group
			$this->FormStart("RateGroup", "RateGroup", "Add");
		
			// Include the flag which specifies whether this Rate Group will be added to a RatePlan
			DBO()->CallingPage->AddRatePlan->RenderHidden();
			
			DBO()->RateGroup->Id->RenderHidden();
			
			echo "<div id='RateGroupDetailsId'>\n";
			$this->_RenderRateGroupDetails();
			echo "</div>\n";
	
			// Stick in the div container for the DeclareRates section of the form
			echo "<div id='RateSelectorControlDiv'>\n";
			$this->_RenderRateSelectorControl();
			echo "</div>\n";
			
			// create the buttons
			echo "<div class='SmallSeperator'></div>\n";
			echo "<div class='Right'>\n";
			$this->Button("Cancel", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
			$this->AjaxSubmit("Save as Draft");
			$this->AjaxSubmit("Commit");
			echo "</div>\n";
			$this->FormEnd();
			
			echo "</div>\n"; // PopupLarge
			break;
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _RenderRateGroupDetails
	//------------------------------------------------------------------------//
	/**
	 * _RenderRateGroupDetails()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	private function _RenderRateGroupDetails()
	{
		echo "<h2 class='Plan'>Rate Group Details</h2>\n";
		echo "<div class='Wide-Form'>\n";

		// Only apply the output mask if the DBO()->RateGroup is not invalid
		$bolApplyOutputMask = !DBO()->RateGroup->IsInvalid();

		DBO()->RateGroup->Name->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RateGroup->Description->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RateGroup->Fleet->RenderInput(CONTEXT_DEFAULT, TRUE);

		// Set the record type and service type, if they have already been defined
		$intServiceType	= 0;
		$intRecordType	= 0;
		if (DBO()->RecordType->Id->Value)
		{
			DBO()->RecordType->Load();
			$intRecordType	= DBO()->RecordType->Id->Value;
			$intServiceType	= DBO()->RecordType->ServiceType->Value;
		}
		if (DBO()->RateGroup->RecordType->Value)
		{
			$intRecordType = DBO()->RateGroup->RecordType->Value;
			
			// Disable the RecordType Combo if we are editting an existing RateGroup
			if (DBO()->RateGroup->Id->Value > 0)
			{
				$strRecordTypeDisabled = "disabled='disabled'";
			}
		}
		if (DBO()->RateGroup->ServiceType->Value)
		{
			$intServiceType = DBO()->RateGroup->ServiceType->Value;
			// Disable the RecordType Combo if we are editting an existing RateGroup
			if (DBO()->RateGroup->Id->Value > 0)
			{
				$strServiceTypeDisabled = "disabled='disabled'";
			}
		}
		
		// Build the ServiceType Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Service Type:</div>\n";
		echo "      <select id='ServiceTypeCombo' name='RateGroup.ServiceType' class='DefaultInputComboBox' style='width:152px;' onchange='Vixen.RateGroupAdd.ChangeServiceType(this.value) $strServiceTypeDisabled'>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intKey=>$arrValue)
		{
			// Check if this is the currently selected ServiceType
			$strSelected = ($intServiceType == $intKey) ? "selected='selected'" : "";
			echo "         <option value='". $intKey ."' $strSelected>". $arrValue['Description'] ."</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		
		// Build the RecordType Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Record Type:</div>\n";
		echo "      <select id='RecordTypeCombo' name='RateGroup.RecordType' class='DefaultInputComboBox' style='width:250px;' onchange='Vixen.RateGroupAdd.ChangeRecordType(this.value)' $strRecordTypeDisabled>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>";
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		
		
		// Retrieve a list of all Record Types
		DBL()->RecordType->OrderBy("Name");
		DBL()->RecordType->Load();
		
		// Load all the record types into an array.  This will be used by javascript to populate the RecordTypeCombo when a Service Type has been selected
		$arrRecordTypes = Array();
		$arrRecordType = Array();
		foreach (DBL()->RecordType as $dboRecordType)
		{
			$arrRecordType['Id'] = $dboRecordType->Id->Value;
			$arrRecordType['ServiceType'] = $dboRecordType->ServiceType->Value;
			$arrRecordType['Description'] = $dboRecordType->Description->Value;
			
			$arrRecordTypes[] = $arrRecordType;
		}
		
		// Define the data required of the javascript that handles events and validation of this form
		$strJsonCode = Json()->encode($arrRecordTypes);

		// Initialise the javascript object
		echo "<script type='text/javascript'>Vixen.RateGroupAdd.InitialiseForm($strJsonCode);</script>\n";
		if ($intServiceType != 0)
		{
			echo "<script type='text/javascript'>Vixen.RateGroupAdd.ChangeServiceType($intServiceType);</script>\n";
		}
		if ($intRecordType != 0)
		{
			echo "<script type='text/javascript'>Vixen.RateGroupAdd.ChangeRecordType($intRecordType);</script>\n";
		}
		
		echo "</div>\n"; // Wide-Form
		echo "<div class='SmallSeperator'></div>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderRateSelectorControl
	//------------------------------------------------------------------------//
	/**
	 * _RenderRateSelectorControl()
	 *
	 * Renders the control used to select which rates belong to this Rate Group
	 *
	 * Renders the control used to select which rates belong to this Rate Group
	 *
	 * @method
	 */
	private function _RenderRateSelectorControl()
	{
		// Render a table for the user to specify a Rate Group for each Record Type required of the Service Type
		echo "<h2 class='Plan'>Rates</h2>\n";
		echo "<div class='Wide-Form'>\n";
		

		$strAvailableRates = "";
		$strSelectedRates = "";

		// Work out which column each of the rates should go
		//NOTE: The value of DBO()->Rates->ArrRates->Value is an array.  
		//This list of rates should be done as a DBList, but this method drastically cuts down the amount of memory required
		if (DBO()->Rates->ArrRates->Value)
		{
			foreach (DBO()->Rates->ArrRates->Value as $arrRate)
			{
				$intRateId = $arrRate['Id'];
				$strRateName = $arrRate['Name'];
				$strDescription = $arrRate['Description'];
				if ($arrRate['Draft'])
				{
					$strDraft = "draft='draft'";
				}
				else
				{
					$strDraft = "";
				}
				
				if (isset($arrRate['Selected']) && $arrRate['Selected'] == TRUE)
				{
					// The rate is currently selected
					$strSelectedRates .= "<option value='$intRateId' title='$strRateName' $strDraft>$strDescription</option>";
				}
				else
				{
					// The rate is not selected
					$strAvailableRates .= "<option value='$intRateId' title='$strRateName' $strDraft>$strDescription</option>";
				}
			}
		}
		
		// Draw the controls in a table to space them
		echo "<table border='0' cellspacing='0' cellpadding='0' width='100%'>\n";
		
		// Draw the Titles 
		echo "<tr>\n";
		echo "   <th align='left' width='45%'>Available Rates</th><th align='center' width='10%'>&nbsp;</th><th align='left' width='45%'>Selected Rates</th>";
		echo "</tr>\n";
		
		echo "<tr>\n";

		// Draw the Available Rates multi-select combo box
		echo "<td>\n";
		echo "<div class='DefaultElement'>\n";
		echo "<select size='15' multiple='multiple' id='AvailableRatesCombo' class='DefaultInputComboBox' style='left:0px;width:300px;'>";
		echo $strAvailableRates;
		echo "</select>\n";
		echo "</div>\n";
		echo "</td>\n";
		
		// Draw the buttons
		//TODO! These aren't lining up properly
		echo "<td align='center'>\n";		
		$this->Button(">>", "Vixen.RateGroupAdd.MoveSelectedOptions(\"AvailableRatesCombo\", \"SelectedRatesCombo\");");
		echo "<br /><br />\n";
		$this->Button("<<", "Vixen.RateGroupAdd.MoveSelectedOptions(\"SelectedRatesCombo\", \"AvailableRatesCombo\");");
		echo "</td>\n";
		
		// Draw the Selected Rates multi-select combo box
		echo "<td>\n";
		echo "<div class='DefaultElement'>\n";
		echo "<select size='15' multiple='multiple' valueIsList='valueIsList' id='SelectedRatesCombo' name='SelectedRates.ArrId' class='DefaultInputComboBox' style='left:0px;width:300px;'>";
		echo $strSelectedRates;
		echo "</select>\n";
		echo "</div>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		
		// Draw the buttons ("Add New Rate" and "View Rate Summary")
		echo "<tr>\n";
		echo "<td align='left'>\n";
		echo "<input type='button' value='Add New Rate' class='InputSubmit' onclick=\"Vixen.RateGroupAdd.AddNewRate()\"></input>\n";
		echo "</td>\n";
		echo "<td colspan='2' align='right'>\n";
		echo "<input type='button' value='Preview Rate Summary' class='InputSubmit' onclick=\"javascript: alert('Dont forget to do this');\"></input>\n";
		echo "</td>";
		echo "</tr>\n";

		// Finish the table
		echo "</table>\n";
		
		echo "</div>"; // Wide-Form
	}
	
	
}

?>
