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
		// validate_adjustment is currently being explicitly included in the Render method as there was a 
		// problem with it being accessed before it was included, when using $this->LoadJavascript(...)
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
		//$this->LoadAjaxJavascript("validate_adjustment");
		echo "<div class='PopupMedium'>\n";
		echo "<h2 class='Adjustment'>Add Adjustment</h2>\n";
		
		// HACK HACK HACK
		// currently this javascript file has to be included here, otherwise it is not instantiated before other calls
		// to it get executed
		//echo "<script type='text/javascript' src='javascript/validate_adjustment.js'></script>\n";
		
		$this->FormStart("AddAdjustment", "Adjustment", "Add");
		
		// include all the properties necessary to add the record, which shouldn't have controls visible on the form
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
		echo "      <select id='ChargeTypeCombo' onchange='Vixen.ValidateAdjustment.DeclareChargeType(this)'>\n";
		foreach (DBL()->ChargeTypesAvailable as $dboChargeType)
		{
			$intChargeTypeId = $dboChargeType->Id->Value;
			// check if this ChargeType was the last one selected
			if ((DBO()->ChargeType->Id->Value) && ($intChargeTypeId == DBO()->ChargeType->Id->Value))
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
			$arrChargeTypeData['ChargeType']	= $dboChargeType->ChargeType->Value;
			$arrChargeTypeData['Nature']		= $dboChargeType->Nature->Value;
			$arrChargeTypeData['Fixed']			= $dboChargeType->Fixed->Value;
			
			// the amounts should be formatted as money values before being added to this array
			$arrChargeTypeData['Amount']		= FormatAsCurrency($dboChargeType->Amount->Value, 2, TRUE);
			$arrChargeTypeData['Description']	= $dboChargeType->Description->Value;
			//$arrChargeTypeData['Id']		= $dboChargeType->Id->Value;
			
			$arrChargeTypes[$intChargeTypeId] = $arrChargeTypeData;
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// if a charge type hasn't been selected then use the first one from the list
		if (!DBO()->ChargeType->Id->Value)
		{
			reset($arrChargeTypes);
			DBO()->ChargeType->Id = key($arrChargeTypes);
			DBO()->Charge->Amount = $arrChargeTypes[DBO()->ChargeType->Id->Value]['Amount'];
		}
		DBO()->ChargeType->Id->RenderHidden();
		$intChargeTypeId = DBO()->ChargeType->Id->Value;
		
		// display the charge code when the Charge Type has been selected
		DBO()->ChargeType->ChargeType = $arrChargeTypes[$intChargeTypeId]['ChargeType'];
		DBO()->ChargeType->ChargeType->RenderOutput();
		
		// display the description
		DBO()->ChargeType->Description = $arrChargeTypes[$intChargeTypeId]['Description'];
		DBO()->ChargeType->Description->RenderOutput();
		
		// display the nature of the charge
		DBO()->ChargeType->Nature = $arrChargeTypes[$intChargeTypeId]['Nature'];
		DBO()->ChargeType->Nature->RenderOutput();
		
		DBO()->Charge->Amount->RenderInput();
		
		// Create a combo box containing the last 6 invoices associated with the account
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Invoice:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='InvoiceComboBox' name='Charge.Invoice'>\n";
		echo "         <option value=''>No Association</option>\n";
		foreach (DBL()->AccountInvoices as $dboInvoice)
		{
			$strInvoiceId = $dboInvoice->Id->Value;
			// Check if this invoice Id was the last one selected
			if ($strInvoiceId == DBO()->Charge->Invoice->Value)
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}
			
			echo "         <option value='$strInvoiceId' $strSelected>$strInvoiceId</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		// Create a textbox for including a note
		DBO()->Charge->Notes->RenderInput(CONTEXT_DEFAULT, TRUE);
		
		// Render the status message, if there is one
		DBO()->Status->Message->RenderOutput();
		
		// create the submit button
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		//echo "<input type='button' value='Close' class='InputSubmit' onclick='Vixen.Popup.Close(\"AddAdjustmentPopupId\");'/>\n";
		$this->Button("Close", "Vixen.Popup.Close(\"AddAdjustmentPopupId\");");
		$this->AjaxSubmit("Add Adjustment");
		echo "</div>\n";
		
		// define the data required of the javacode that handles events and validation of this form
		$strJsonCode = Json()->encode($arrChargeTypes);
		echo "<script type='text/javascript'>Vixen;</script>";
		echo "<script type='text/javascript'>Vixen.ValidateAdjustment.SetChargeTypes($strJsonCode);</script>\n";

		
		$this->FormEnd();
		echo "</div>\n";
	}
}

?>
