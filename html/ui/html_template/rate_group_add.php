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
				// Just draw the "Rate Group Details" part of the form
				$this->_RenderRateGroupDetails();
				break;
			case HTML_CONTEXT_RATES:
				// Just draw the "Rate Selector control" part of the form
				$this->_RenderRateSelectorControl();
				break;
			case HTML_CONTEXT_DEFAULT:
			default:
				// This should only be called when the popup window is initially drawn
			
				// Set Up the form for adding a rate group
				$this->FormStart("RateGroup", "RateGroup", "Add");
			
				// Include the flag which specifies whether this Rate Group will be added to a RatePlan
				DBO()->CallingPage->AddRatePlan->RenderHidden();
				
				// Include RateGroup.Id as a hidden, and BaseRateGroup.Id if it is set
				DBO()->RateGroup->Id->RenderHidden();
				if (DBO()->BaseRateGroup->Id->IsSet)
				{
					DBO()->BaseRateGroup->Id->RenderHidden();
				}
				
				// Render the RateGroupDetails
				echo "<div class='GroupedContent'>\n";

				echo "<div id='RateGroupDetailsId'>\n";
				$this->_RenderRateGroupDetails();
				echo "</div>\n";

				$strCapLimit	= "";
				$strChecked		= "";
				if (DBO()->RateGroup->CapLimit->Value !== NULL)
				{
					$strCapLimit	= OutputMask()->MoneyValue(DBO()->RateGroup->CapLimit->Value);
					$strChecked		= "checked='checked'";
				}

				echo "	<div class='DefaultElement'>
							<input type='checkbox' id='CapLimitCheckbox' name='RateGroup.HasCapLimit' $strChecked class='DefaultInputCheckBox2 Default' />
							<input type='text' id='RateGroup.CapLimit' name='RateGroup.CapLimit' class='DefaultInputText Default' value='$strCapLimit' style='visibility:hidden'/>
							<div class='DefaultLabel'>&nbsp;&nbsp;Cap Limit (\$):</div>
						</div>";

		
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
				}
				if (DBO()->RateGroup->ServiceType->Value)
				{
					$intServiceType = DBO()->RateGroup->ServiceType->Value;
				}
				
				// Build the ServiceType Combobox
				echo "<div class='DefaultElement'>\n";
				echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Service Type:</div>\n";
				echo "      <select id='ServiceTypeCombo' name='RateGroup.ServiceType' class='DefaultInputComboBox' style='width:152px;' onchange='Vixen.RateGroupAdd.ChangeServiceType(this.value)'>\n";
				echo "         <option value='0' selected='selected'>&nbsp;</option>";
				foreach ($GLOBALS['*arrConstant']['service_type'] as $intKey=>$arrValue)
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
				echo "      <select id='RecordTypeCombo' name='RateGroup.RecordType' class='DefaultInputComboBox' style='width:380px;' onchange='Vixen.RateGroupAdd.ChangeRecordType(this.value)'>\n";
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
				echo "<script type='text/javascript'>Vixen.RateGroupAdd.Initialise($strJsonCode, true, '{$this->_objAjax->strId}');</script>\n";
				if ($intServiceType != 0)
				{
					echo "<script type='text/javascript'>Vixen.RateGroupAdd.ChangeServiceType($intServiceType);</script>\n";
				}
				if ($intRecordType != 0)
				{
					echo "<script type='text/javascript'>Vixen.RateGroupAdd.ChangeRecordType($intRecordType);</script>\n";
				}
				
				echo "</div>\n"; // GroupedContent (RateGroup Details)
				echo "<div class='SmallSeparator'></div>\n";

				// Stick in the div container for the RateSelectorControl section of the form
				echo "<div id='RateSelectorControlDiv'>\n";
				$this->_RenderRateSelectorControl();
				echo "</div>\n";
				
				// create the buttons
				echo "<div class='ButtonContainer'><div class='Right'>\n";
				$this->Button("Cancel", "Vixen.Popup.Close(this);");
				$this->Button("Save as Draft", "Vixen.RateGroupAdd.SaveAsDraft()");
				$this->Button("Commit", "Vixen.RateGroupAdd.Commit()");
				echo "</div></div>\n"; // Buttons
				
				$this->FormEnd();
				break;
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _RenderRateGroupDetails
	//------------------------------------------------------------------------//
	/**
	 * _RenderRateGroupDetails()
	 *
	 * Render the part of the RateGroup Details that may require rerendering, when invalid
	 *
	 * Render the part of the RateGroup Details that may require rerendering, when invalid
	 *
	 * @method
	 */
	private function _RenderRateGroupDetails()
	{
		// Only apply the output mask if the DBO()->RateGroup is not invalid
		$bolApplyOutputMask = !DBO()->RateGroup->IsInvalid();

		DBO()->RateGroup->Name->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask, Array("style:width"=>"380px", "attribute:maxlength"=>255));
		DBO()->RateGroup->Description->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask, Array("style:width"=>"380px", "attribute:maxlength"=>255));
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
		echo "<div class='GroupedContent'>\n";

		$strAvailableRates = "";
		$strSelectedRates = "";

		// Work out which column each of the rates should go
		//NOTE: The value of DBO()->Rates->ArrRates->Value is an array.  
		//This list of rates should be done as a DBList, but this method drastically cuts down the amount of memory required
		if (DBO()->Rates->ArrRates->Value)
		{
			foreach (DBO()->Rates->ArrRates->Value as $arrRate)
			{
				$intRateId 		= $arrRate['Id'];
				// All special chars have to be converted to their html safe versions
				$strRateName 	= htmlspecialchars($arrRate['Name'], ENT_QUOTES);
				$strDescription = htmlspecialchars($arrRate['Description'], ENT_QUOTES);
				$strDraft 		= "";

				if ($arrRate['Draft'])
				{
					$strDraft = "draft='draft'";
					$strRateName  = "DRAFT: " . $strRateName;
				}
				
				if ($arrRate['Selected'])
				{
					// The rate is currently selected
					$strSelectedRates .= "<option value='$intRateId' title='$strDescription' $strDraft>$strRateName</option>";
				}
				else
				{
					// The rate is not selected
					$strAvailableRates .= "<option value='$intRateId' title='$strDescription' $strDraft>$strRateName</option>";
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
		echo "<select size='15' multiple='multiple' id='AvailableRatesCombo' class='DefaultInputComboBox' style='left:0px;width:305px;' onClick='Vixen.RateGroupAdd.AvailableRatesComboOnClick()' onKeyDown='Vixen.RateGroupAdd.AvailableRatesComboOnClick()'>";
		echo $strAvailableRates;
		echo "</select>\n";
		echo "</div>\n";
		echo "</td>\n";
		
		// Draw the buttons
		echo "<td align='center'>\n";
		echo "<div class='ButtonContainer'>\n";
		$this->Button(">>", "Vixen.RateGroupAdd.MoveSelectedOptions(\"AvailableRatesCombo\", \"SelectedRatesCombo\");");
		echo "</div>";
		echo "<div class='ButtonContainer'>\n";
		$this->Button("<<", "Vixen.RateGroupAdd.MoveSelectedOptions(\"SelectedRatesCombo\", \"AvailableRatesCombo\");");
		echo "</div>";
		echo "</td>\n";
		
		// Draw the Selected Rates multi-select combo box
		echo "<td>\n";
		echo "<div class='DefaultElement'>\n";
		echo "<select size='15' multiple='multiple' valueIsList='valueIsList' id='SelectedRatesCombo' name='SelectedRates.ArrId' class='DefaultInputComboBox' style='left:0px;width:305px;' onClick='Vixen.RateGroupAdd.SelectedRatesComboOnClick()' onKeyDown='Vixen.RateGroupAdd.SelectedRatesComboOnClick()'>";
		echo $strSelectedRates;
		echo "</select>\n";
		echo "</div>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		
		// Draw the buttons ("Add New Rate", "Edit Rate" and "View Rate Summary")
		// also used for adding a rate based on an existing
		echo "<tr>\n";
		echo "<td align='left'>\n";
		echo "<div class='ButtonContainer'>\n";
		$this->Button("Add New Rate", "Vixen.RateGroupAdd.AddNewRate()");
		$this->Button("Edit Rate", "Vixen.RateGroupAdd.EditRate()");
		echo "</div></td>\n";
		echo "<td colspan='2' align='right'>\n";
		echo "<div class='ButtonContainer'>\n";
		$this->Button("Preview Rate Summary", "Vixen.RateGroupAdd.PreviewRateSummary()");
		echo "</div></td>";
		echo "</tr>\n";

		// Finish the table
		echo "</table>\n";
		
		echo "</div>"; // GroupedContent
		
		echo "<script type='text/javascript'>Vixen.RateGroupAdd.InitialiseRateSelector();</script>";
	}
	
	
}

?>
