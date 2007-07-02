<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add.php
//----------------------------------------------------------------------------//
/**
 * add
 *
 * HTML Template for the Add Adjustment HTML object
 *
 * HTML Template for the Add Adjustment HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add an adjustment.
 *
 * @file		add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAdjustmentAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAdjustmentAdd
 *
 * HTML Template class for the Add Adjustment HTML object
 *
 * HTML Template class for the Add Adjustment HTML object
 * displays the form used to add an adjustment
 *
 * @package	ui_app
 * @class	HtmlTemplateAdjustmentAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateAdjustmentAdd extends HtmlTemplate
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
		$this->LoadJavascript("dhtml");
		$this->LoadJavascript("debug");  // Tools for debugging, only use when js-ing
		//$this->LoadJavascript("validate_adjustment");
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
		//echo "<div id='NotesHolder' style='display:none;'>\n";
		echo "<div class='PopupMedium'>\n";
		echo "<h2 class='Adjustment'>Add Adjustment</h2>\n";
		
		// currently this javascript file has to be included here, otherwise it is not instantiated before other calls
		// to it get executed
		echo "<script type='text/javascript' src='javascript/validate_adjustment.js'></script>\n";
		
		$this->FormStart("AddAdjustment", "Adjustment", "Add");
		
		// include all the propeties necessary to add the record, which shouldn't have controls visible on the form
		DBO()->Account->Id->RenderHidden();
		
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
		//echo "      <select name='ChargeType.ChargeType' id='ChargeType.ChargeType' onchange='Vixen.ValidateAdjustment.DeclareChargeType(this)'>\n";
		echo "      <select id='ChargeType.ChargeType' onchange='Vixen.ValidateAdjustment.DeclareChargeType(this)'>\n";
		echo "         <option id='ChargeTypeNotSelected' value='NoSelection'>&nbsp;</option>\n";
		foreach (DBL()->ChargeType as $dboChargeType)
		{
			$strChargeType = $dboChargeType->ChargeType->Value;
			$strDescription = $dboChargeType->Nature->Value .": ". $dboChargeType->Description->Value;
			echo "         <option id='ChargeType.$strChargeType' value='$strChargeType'>$strDescription</option>\n";
			
			// add ChargeType details to an array that will be passed to the javascript that handles events on th
			$arrChargeTypeData['Nature']	= $dboChargeType->Nature->Value;
			$arrChargeTypeData['Fixed']		= $dboChargeType->Fixed->Value;
			$arrChargeTypeData['Amount']	= $dboChargeType->Amount->Value;
			$arrChargeTypeData['Description'] = $dboChargeType->Description->Value;
			$arrChargeTypeData['Id']		= $dboChargeType->Id->Value;
			$arrChargeTypes[$dboChargeType->ChargeType->Value] = $arrChargeTypeData;
			
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		DBO()->ChargeType->Id = "";
		DBO()->ChargeType->Id->RenderHidden();
		
		// display the charge code when the Charge Type has been selected
		DBO()->Charge->ChargeType->RenderOutput();
		
		// display the description
		DBO()->ChargeType->Description->RenderOutput();
		
		// display the nature of the charge
		DBO()->Charge->Nature->RenderOutput();
		
		DBO()->Charge->Amount->RenderInput();
		
		// Create a combo box containing the last 6 invoices associated with the account
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Invoice:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='InvoiceComboBox' name='Charge.Invoice'>\n";
		echo "         <option value=''>No Association</option>\n";
		foreach (DBL()->Invoice as $dboInvoice)
		{
			$strInvoice = $dboInvoice->Id->Value;
			echo "         <option value='$strInvoice'>$strInvoice</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		// Create a textbox for including a note
		DBO()->Charge->Notes->RenderInput();
		
		
		// create the submit button
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		//echo "   <input type='button' id='btnAddAdjustment' value='Add Adjustment &#xBB;' class='input-submit' onclick='Vixen.ValidateAdjustment.AddAdjustment()'>Click Me</input>\n";
		//echo "<button class='input-submit' id='btnAddAdjustment' value='submit' onclick='javascript:alert(document.getElementById(\"btnAddAdjustment\").value)'>Hey hey</button>\n";
		$this->AjaxSubmit("Add Adjustment");
		//echo "<input type='submit' name='btnAddAdjustment' value='ThisIsTheValue'>HeyHey</submit>\n";
		//echo "<label for='btnAddAdjustment'>TESTING</label>\n";
		echo "</div>\n";
		
		// define the data required of the javacode that handles events and validation of this form
		$strJsonCode = Json()->encode($arrChargeTypes);
		echo "<script type='text/javascript'>Vixen.ValidateAdjustment.SetChargeTypes($strJsonCode);</script>\n";

		
		$this->FormEnd();
		echo "</div>\n";
	}
}

?>
