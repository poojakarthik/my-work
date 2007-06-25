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
		$this->LoadJavascript("validate_adjustment");
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
		
		echo "<form method='POST' action='INSERT ACTION HERE (probably javascript)'>\n";
		
		// define hidden variables
		echo "<input type='hidden' name='Account.Id' value='{DBO()->Account->Id->Value}'>\n";
		
		
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
		echo "      <select name='ChargeType.ChargeType' id='ChargeType.ChargeType' onclick='ValidateAdjustment.DeclareChargeType(this)'>\n";
		foreach (DBL()->ChargeType as $dboChargeType)
		{
			$strChargeType = $dboChargeType->ChargeType->Value;
			$strDefaultAmount = $dboChargeType->Amount->Value;
			$strDescription = $dboChargeType->Nature->Value .": ". $dboChargeType->Description->Value;
			echo "         <option id='ChargeType.$strChargeType' value='$strChargeType' DefaultAmount='$strDefaultAmount'>$strDescription</option>\n";
			//TODO! Add to the structure of data that will be stored in the javascript
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		//TODO! add some javascript which loads the default charge when the charge type is selected, and gives focus to the Amount textbox
		
		// display the charge code when the Charge Type has been selected
		//TODO! This is only set after the user chooses from the combp box
		//echo "<div class='DefaultElement'>\n";
		//echo "   <div class='DefaultLabel'>Charge Code:</div>\n";
		//echo "   <div id='ChargeCode' class='DefaultOutput'>&nbsp;</div>\n";
		//echo "</div>\n";
		
		//DBO()->Charge->ChargeType = "CRG";
		DBO()->Charge->ChargeType->RenderOutput();
		
		// display the nature of the charge
		//TODO! this has to be set first, based on what the user chooses from the combo box
		DBO()->Charge->Nature = "CR";
		if (DBO()->Charge->Nature->Value == "CR")
		{
			DBO()->Charge->Nature->RenderArbitrary("Credit", RENDER_OUTPUT);
		}
		else
		{
			DBO()->Charge->Nature->RenderArbitrary("Debit", RENDER_OUTPUT);
		}
		
		//DBO()->Charge->Amount = 55.00;
		
		DBO()->Charge->Amount->RenderInput();
		
		// Create a combo box containing the last 6 invoices associated with the account
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Invoice:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='InvoiceComboBox'>\n";
		echo "         <option value='0'>No Association</option>\n";
		foreach (DBL()->Invoice as $dboInvoice)
		{
			$strInvoice = $dboInvoice->Id->Value;
			echo "         <option value='$strInvoice'>$strInvoice</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		// Create a textbox for including a note
		DBO()->Charge->Notes = "INSERT NOTE HERE\nTHIS IS LINE TWO";
		DBO()->Charge->Notes->RenderInput();
		
		
		// create the submit button
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		echo "   <input type='submit' name='Confirm' value='Add Adjustment &#xBB;' class='input-submit'></input>\n";
		echo "</div>\n";
		
		// define the data in javascript:ValidateAdjustment
		//TODO! Joel
		echo "<script ></script>\n";
		
		echo "</form>\n";
		echo "</div>\n";
	}
}

?>
