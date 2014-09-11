<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recurring_charge_add.php
//----------------------------------------------------------------------------//
/**
 * recurring_charge_add
 *
 * HTML Template for the Add Recurring Charge HTML object
 *
 * HTML Template for the Add Recurring Charge HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a recurring charge.
 *
 * @file		recurring_charge_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRecurringChargeAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRecurringChargeAdd
 *
 * HTML Template class for the Add Recurring Charge HTML object
 *
 * HTML Template class for the Add Recurring Charge HTML object
 * displays the form used to add a recurring charge
 *
 * @package	ui_app
 * @class	HtmlTemplateRecurringChargeAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateRecurringChargeAdd extends HtmlTemplate
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
		
		// Load all java script specific to the page here
		$this->LoadJavascript("recurring_charge_add");
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
		$this->FormStart("AddRecurringCharge", "Charge", "AddRecurring");
		
		// Include all the properties necessary to add the record, which shouldn't have controls visible on the form
		DBO()->Account->Id->RenderHidden();
		//DBO()->ChargeType->Id->RenderHidden();
		
		echo "<div class='GroupedContent'>\n";
		
		// Check if the recurring charge is being applied to a service
		if (DBO()->Service->Id->Value)
		{
			DBO()->Service->Id->RenderHidden();
			
			// Display the Service's FNN
			DBO()->Service->FNN->RenderOutput();
		}
		
		// Display account details
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		if (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		
		// Create a combobox containing all the charge types
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Charge :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ChargeTypeCombo' style='width:100%' onchange='Vixen.RecurringChargeAdd.DeclareChargeType(this.value)'>\n";
		foreach (DBL()->ChargeTypesAvailable as $dboChargeType)
		{
			$intChargeTypeId = $dboChargeType->Id->Value;
			// Flag this ChargeType if it was the last one selected
			$strSelected = ((DBO()->RecurringChargeType->Id->Value) && ($intChargeTypeId == DBO()->RecurringChargeType->Id->Value)) ? "selected='selected'" : "";

			$strDescription = $dboChargeType->Nature->Value .": ". $dboChargeType->Description->Value ." (". $dboChargeType->ChargeType->Value .")";
			echo "         <option id='ChargeType.$intChargeTypeId' $strSelected value='$intChargeTypeId'>". htmlspecialchars($strDescription) ."</option>\n";
			
			// Add ChargeType details to an array that will be passed to the javascript that handles events on the ChargeTypeCombo
			$arrChargeTypeData['Id']						= $dboChargeType->Id->Value;
			$arrChargeTypeData['ChargeType']				= $dboChargeType->ChargeType->Value;
			$arrChargeTypeData['Nature']					= $dboChargeType->Nature->Value;
			$arrChargeTypeData['Fixed']						= $dboChargeType->Fixed->Value;
			$arrChargeTypeData['Description']				= $dboChargeType->Description->Value;
			$arrChargeTypeData['RecurringFreqTypeAsText']	= $dboChargeType->RecurringFreqType->FormattedValue();
			$arrChargeTypeData['RecurringFreqType']			= $dboChargeType->RecurringFreqType->Value;
			$arrChargeTypeData['RecurringFreq']				= $dboChargeType->RecurringFreq->Value;
			$arrChargeTypeData['Continuable']				= (boolean)($dboChargeType->Continuable->Value == TRUE);
			$arrChargeTypeData['ApprovalRequired']			= (boolean)($dboChargeType->approval_required->Value == TRUE);
			
			// Add GST to the Minimum Charge and format it as a money value
			$fltMinChargeIncGST 					= AddGST($dboChargeType->MinCharge->Value);
			$strMinCharge 							= OutputMask()->MoneyValue($fltMinChargeIncGST, 2, TRUE);
			$arrChargeTypeData['MinCharge'] 		= $strMinCharge;
			
			// Add GST to the Recursion Charge and format it as a money value
			$fltRecursionChargeIncGST 				= AddGST($dboChargeType->RecursionCharge->Value);
			$strRecursionCharge 					= OutputMask()->MoneyValue($fltRecursionChargeIncGST, 2, TRUE);
			$arrChargeTypeData['RecursionCharge'] 	= $strRecursionCharge;
			
			// Add GST to the Cancellation Fee and format it as a money value
			$fltCancellationFeeIncGST 				= AddGST($dboChargeType->CancellationFee->Value);
			$strCancellationFee 					= OutputMask()->MoneyValue($fltCancellationFeeIncGST, 2, TRUE);
			$arrChargeTypeData['CancellationFee'] 	= $strCancellationFee;
			
			$arrChargeTypes[$intChargeTypeId] 		= $arrChargeTypeData;
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// If a charge type hasn't been selected then use the first one from the list
		if (!DBO()->RecurringChargeType->Id->Value)
		{
			reset($arrChargeTypes);
			DBO()->RecurringChargeType->Id			= key($arrChargeTypes);
			DBO()->RecurringCharge->RecursionCharge	= $arrChargeTypes[DBO()->RecurringChargeType->Id->Value]['RecursionCharge'];
			DBO()->RecurringCharge->MinCharge		= $arrChargeTypes[DBO()->RecurringChargeType->Id->Value]['MinCharge'];
		}
		DBO()->RecurringChargeType->Id->RenderHidden();
		$intChargeTypeId = DBO()->RecurringChargeType->Id->Value;
		
		// Display the charge code when the Charge Type has been selected
		DBO()->RecurringChargeType->ChargeType = $arrChargeTypes[$intChargeTypeId]['ChargeType'];
		DBO()->RecurringChargeType->ChargeType->RenderOutput();
		
		// Display the description
		DBO()->RecurringChargeType->Description = $arrChargeTypes[$intChargeTypeId]['Description'];
		DBO()->RecurringChargeType->Description->RenderOutput();
		
		// Display the nature of the charge
		DBO()->RecurringChargeType->Nature = $arrChargeTypes[$intChargeTypeId]['Nature'];
		DBO()->RecurringChargeType->Nature->RenderOutput();

		// Create the "Requires Approval" label
		echo "
<div class='DefaultElement'>
	<div class='DefaultLabel'>&nbsp;&nbsp;Requires Approval :</div>
	<div id='RecurringChargeType.ApprovalRequired' class='DefaultOutput'>&nbsp;</div>
</div>";

		
		// Display whether or not the charge is continuable (keeps getting charged after the minimum charge is reached
		DBO()->RecurringChargeType->Continuable = $arrChargeTypes[$intChargeTypeId]['Continuable'];
		DBO()->RecurringChargeType->Continuable->RenderOutput();
		
		// Display the cancellation fee
		DBO()->RecurringChargeType->CancellationFee = $arrChargeTypes[$intChargeTypeId]['CancellationFee'];
		DBO()->RecurringChargeType->CancellationFee->RenderOutput(CONTEXT_INCLUDES_GST);
		
		// Display the Recurring Frequency
		DBO()->RecurringChargeType->RecurringFreq = $arrChargeTypes[$intChargeTypeId]['RecurringFreq'];
		$strRecurringFreq = $arrChargeTypes[$intChargeTypeId]['RecurringFreq'] ." ". $arrChargeTypes[$intChargeTypeId]['RecurringFreqTypeAsText'];
		DBO()->RecurringChargeType->RecurringFreq->RenderArbitrary($strRecurringFreq, RENDER_OUTPUT);

		// If Today is the 29th - 31st of the month, then the user has to choose whether to snap the charge to the 28th or the 1st of next month
		$intNow				= strtotime(GetCurrentISODateTime());
		$intCurrentDay		= intval(date("d", $intNow));
		$intCurrentMonth	= intval(date("m", $intNow));
		$intCurrentYear		= intval(date("Y", $intNow));
		
		if (($intCurrentDay >= 29) && ($intCurrentDay <= 31))
		{
			// The user will have to choose to snap the recurring charge to the 28th or the 1st of next month
			$strStartDate28th	= date("d/m/Y", mktime(0, 0, 0, $intCurrentMonth, 28, $intCurrentYear));
			$strStartDate1st	= date("d/m/Y", mktime(0, 0, 0, $intCurrentMonth + 1, 1, $intCurrentYear));
			if (DBO()->RecurringCharge->SnapToDayOfMonth->Value == 1)
			{
				$strSnapTo1StSelected	= "selected='selected'";
				$strSnapTo28ThSelected	= "";
			}
			else
			{
				$strSnapTo28ThSelected	= "selected='selected'";
				$strSnapTo1StSelected	= "";
			}
			
			echo "
<div class='DefaultElement' id='StartDateSnapControl'>
	<div class='DefaultLabel'>&nbsp;&nbsp;Start Date Snap To :</div>
	<div class='DefaultOutput'>
		<select id='RecurringCharge.SnapToDayOfMonth' name='RecurringCharge.SnapToDayOfMonth'>
			<option value='28' $strSnapTo28ThSelected>$strStartDate28th</option>
			<option value='1' $strSnapTo1StSelected>$strStartDate1st</option>
		</select>
	</div>
</div>
";
		}

		// Display the Minimum Charge
		DBO()->RecurringCharge->MinCharge->RenderInput(CONTEXT_INCLUDES_GST, TRUE);

		// Display the RecursionCharge
		DBO()->RecurringCharge->RecursionCharge->RenderInput(CONTEXT_INCLUDES_GST, TRUE);
		
		// Create the TimesToCharge textbox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Times to Charge :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <input type='text' id='TimesToCharge' value='' style='padding-left:3px;width:165px' onkeyup='Vixen.RecurringChargeAdd.TimesChargedChanged(event)'></input>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// Create the in_advance checkbox
		echo "
<div class='DefaultElement'>
	<div class='DefaultLabel'>&nbsp;&nbsp;Charge in Advance/Arrears :</div>
	<div class='DefaultOutput'>
		<select id='RecurringCharge.in_advance' name='RecurringCharge.in_advance'>
			<option value='0' selected='selected'>In Arrears</option>
			<option value='1'>In Advance</option>
		</select>
	</div>
</div>";
		
		// Create the EndDate label
		echo "<div class='TinySeparator'></div>";
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;End Date :</div>\n";
		echo "   <div id='EndDate' class='DefaultOutput'>&nbsp;</div>\n";
		echo "</div>\n";
		
		echo "</div>\n"; // GroupedContent
		
		echo "
<div>
	<div style='float:right'>
		<input type='button' style='display:none;' id='AddChargeSubmitButton' value='Apply Changes' onclick=\"Vixen.Ajax.SendForm('VixenForm_AddRecurringCharge', 'Add Charge', 'Charge', 'AddRecurring', 'Popup', 'AddRecurringChargePopupId', 'medium', '{$this->_strContainerDivId}')\"></input>
		<input type='button' value='Submit Request' id='RecurringCharge_submitRequestButton' onclick='Vixen.RecurringChargeAdd.SubmitRequest()'></input>
		<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)'></input>
	</div>
	<div style='float:none;clear:both'></div>
</div>
";


		// Define the data required of the javacode that handles events and validation of this form
		$strJsonCode = Json()->encode($arrChargeTypes);

		$intCurrentChargeTypeId = DBO()->RecurringChargeType->Id->Value;
		echo "<script type='text/javascript'>Vixen.RecurringChargeAdd.InitialiseForm($strJsonCode, $intCurrentChargeTypeId);</script>\n";
				
		$this->FormEnd();
	}
}

?>
