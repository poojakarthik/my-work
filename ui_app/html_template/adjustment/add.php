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
		
		$this->FormStart("AddAdjustment", "Adjustment", "Add");
		
		// include all the properties necessary to add the record, which shouldn't have controls visible on the form
		if (DBO()->Service->Id->Value)
		{
			echo "<h2 class='Adjustment'>Add Service Adjustment</h2>\n";
			DBO()->Service->Id->RenderHidden();
		}
		else
		{
			echo "<h2 class='Adjustment'>Add Adjustment</h2>\n";
		}
		
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
		echo "      <select id='ChargeTypeCombo' style='width:240px' onchange='Vixen.ValidateAdjustment.DeclareChargeType(this)'>\n";
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
			
			// Add GST to the default amount and format it as a money value
			$fltAmountIncGST					= AddGST($dboChargeType->Amount->Value);
			$strAmount							= OutputMask()->MoneyValue($fltAmountIncGST, 2, TRUE);
			$arrChargeTypeData['Amount']		= $strAmount;
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
		
		DBO()->Charge->Amount->RenderInput(CONTEXT_INCLUDES_GST, TRUE);
		// if the charge type has a fixed amount then disable the amount textbox
		if ($arrChargeTypes[$intChargeTypeId]['Fixed'])
		{
			echo "<script type='text/javascript'>document.getElementById('Charge.Amount').disabled = TRUE;</script>\n";
		}
		

		
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
		DBO()->Charge->Notes->RenderInput(CONTEXT_DEFAULT);
		
		// output the manditory field message
		echo "<div class='DefaultElement'><span class='RequiredInput'>*</span> : Required Field</div>\n";
		
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
		
		// Set the charge types in the javascript object that handles interactions with this popup window
		echo "<script type='text/javascript'>Vixen.ValidateAdjustment.SetChargeTypes($strJsonCode);</script>\n";

		// give the ChargeTypeCombo initial focus
		echo "<script type='text/javascript'>document.getElementById('ChargeTypeCombo').focus();</script>\n";
		
		$this->FormEnd();
		echo "</div>\n";
	}
}

?>
