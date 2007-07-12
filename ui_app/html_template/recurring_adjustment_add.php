<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recurring_adjustment_add.php
//----------------------------------------------------------------------------//
/**
 * recurring_adjustment_add
 *
 * HTML Template for the Add Recurring Adjustment HTML object
 *
 * HTML Template for the Add Recurring Adjustment HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a recurring adjustment.
 *
 * @file		recurring_adjustment_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRecurringAdjustmentAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRecurringAdjustmentAdd
 *
 * HTML Template class for the Add Recurring Adjustment HTML object
 *
 * HTML Template class for the Add Recurring Adjustment HTML object
 * displays the form used to add a recurring adjustment
 *
 * @package	ui_app
 * @class	HtmlTemplateRecurringAdjustmentAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateRecurringAdjustmentAdd extends HtmlTemplate
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
		// validate_adjustment is currently being explicitly included in the Render method as there was a 
		// problem with it being accessed before it was included, when using $this->LoadJavascript(...)
		$this->LoadJavascript("validate_recurring_adjustment");
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
		echo "<div class='PopupLarge'>\n";
		echo "<h2 class='Adjustment'>Add Recurring Adjustment</h2>\n";
		
		// HACK HACK HACK
		// currently this javascript file has to be included here, otherwise it is not instantiated before other calls
		// to it get executed
		//echo "<script type='text/javascript' src='javascript/validate_recurring_adjustment.js'></script>\n";
		
		$this->FormStart("AddRecurringAdjustment", "Adjustment", "AddRecurring");
		
		// include all the properties necessary to add the record, which shouldn't have controls visible on the form
		DBO()->Account->Id->RenderHidden();
		//DBO()->ChargeType->Id->RenderHidden();
		
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
		
		// create a combobox containing all the charge types
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Adjustment:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ChargeTypeCombo' onchange='Vixen.ValidateRecurringAdjustment.DeclareChargeType(this.value)'>\n";
		foreach (DBL()->ChargeTypesAvailable as $dboChargeType)
		{
			$intChargeTypeId = $dboChargeType->Id->Value;
			// check if this ChargeType was the last one selected
			if ((DBO()->RecurringChargeType->Id->Value) && ($intChargeTypeId == DBO()->RecurringChargeType->Id->Value))
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}
			$strDescription = $dboChargeType->Nature->Value .": ". $dboChargeType->Description->Value;
			echo "         <option id='ChargeType.$intChargeTypeId' $strSelected value='$intChargeTypeId'>$strDescription</option>\n";
			
			// add ChargeType details to an array that will be passed to the javascript that handles events on the ChargeTypeCombo
			$arrChargeTypeData['ChargeType']				= $dboChargeType->ChargeType->Value;
			$arrChargeTypeData['Nature']					= $dboChargeType->Nature->FormattedValue();
			$arrChargeTypeData['Fixed']						= $dboChargeType->Fixed->Value;
			$arrChargeTypeData['Description']				= $dboChargeType->Description->Value;
			$arrChargeTypeData['RecurringFreqTypeAsText']	= $dboChargeType->RecurringFreqType->FormattedValue();
			$arrChargeTypeData['RecurringFreqType']			= $dboChargeType->RecurringFreqType->Value;
			$arrChargeTypeData['RecurringFreq']				= $dboChargeType->RecurringFreq->Value;
			
			// Add GST to the Minimum Charge and format it as a money value
			$fltMinChargeIncGST = AddGST($dboChargeType->MinCharge->Value);
			$strMinCharge = FormatAsCurrency($fltMinChargeIncGST, 2, TRUE);
			$arrChargeTypeData['MinCharge'] = $strMinCharge;
			
			// Add GST to the Recursion Charge and format it as a money value
			$fltRecursionChargeIncGST = AddGST($dboChargeType->RecursionCharge->Value);
			$strRecursionCharge = FormatAsCurrency($fltRecursionChargeIncGST, 2, TRUE);
			$arrChargeTypeData['RecursionCharge'] = $strRecursionCharge;
			
			// Add GST to the Cancellation Fee and format it as a money value
			$fltCancellationFeeIncGST = AddGST($dboChargeType->CancellationFee->Value);
			$strCancellationFee = FormatAsCurrency($fltCancellationFeeIncGST, 2, TRUE);
			$arrChargeTypeData['CancellationFee'] = $strCancellationFee;
			
			$arrChargeTypes[$intChargeTypeId] = $arrChargeTypeData;
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// if a charge type hasn't been selected then use the first one from the list
		if (!DBO()->RecurringChargeType->Id->Value)
		{
			reset($arrChargeTypes);
			DBO()->RecurringChargeType->Id			= key($arrChargeTypes);
			DBO()->RecurringCharge->RecursionCharge	= $arrChargeTypes[DBO()->RecurringChargeType->Id->Value]['RecursionCharge'];
			DBO()->RecurringCharge->MinCharge		= $arrChargeTypes[DBO()->RecurringChargeType->Id->Value]['MinCharge'];
		}
		DBO()->RecurringChargeType->Id->RenderHidden();
		$intChargeTypeId = DBO()->RecurringChargeType->Id->Value;
		
		// display the charge code when the Charge Type has been selected
		DBO()->RecurringChargeType->ChargeType = $arrChargeTypes[$intChargeTypeId]['ChargeType'];
		DBO()->RecurringChargeType->ChargeType->RenderOutput();
		
		// display the description
		DBO()->RecurringChargeType->Description = $arrChargeTypes[$intChargeTypeId]['Description'];
		DBO()->RecurringChargeType->Description->RenderOutput();
		
		// display the nature of the charge
		DBO()->RecurringChargeType->Nature = $arrChargeTypes[$intChargeTypeId]['Nature'];
		DBO()->RecurringChargeType->Nature->RenderOutput();
		
		// display the cancellation fee
		DBO()->RecurringChargeType->CancellationFee = $arrChargeTypes[$intChargeTypeId]['CancellationFee'];
		DBO()->RecurringChargeType->CancellationFee->RenderOutput();
		
		// display the Recurring Frequency
		DBO()->RecurringChargeType->RecurringFreq = $arrChargeTypes[$intChargeTypeId]['RecurringFreq'];
		$strRecurringFreq = $arrChargeTypes[$intChargeTypeId]['RecurringFreq'] ." ". $arrChargeTypes[$intChargeTypeId]['RecurringFreqTypeAsText'];
		DBO()->RecurringChargeType->RecurringFreq->RenderArbitrary($strRecurringFreq, RENDER_OUTPUT);

		// display the Minimum Charge
		DBO()->RecurringCharge->MinCharge->RenderInput();

		// display the RecursionCharge
		DBO()->RecurringCharge->RecursionCharge->RenderInput();
		
		// create the TimesToCharge textbox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Times to Charge</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <input type='text' id='TimesToCharge' value='' onkeyup='Vixen.ValidateRecurringAdjustment.TimesChargedChanged()'></input>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// create the EndDate label
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>End Date:</div>\n";
		echo "   <div id='EndDate' class='DefaultOutput'>&nbsp;</div>\n";
		echo "</div>\n";
		
		// Render the status message, if there is one
		DBO()->Status->Message->RenderOutput();
		
		// create the buttons
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		$this->AjaxSubmit("Add Adjustment");
		echo "</div>\n";
		
		// define the data required of the javacode that handles events and validation of this form
		$strJsonCode = Json()->encode($arrChargeTypes);

		echo "<script type='text/javascript'>Vixen.ValidateRecurringAdjustment.InitialiseForm($strJsonCode);</script>\n";
		
		
		$this->FormEnd();
		echo "</div>\n";
	}
}

?>
