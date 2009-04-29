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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		// Load all java script specific to the page here
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
		$bolUserHasProperAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolUserHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		
		// Currently anyone can create credit adjustments
		// Originally the user required either the Proper Admin permission or the Credit Management permission
		//$bolCanCreateCreditAdjustments = ($bolUserHasProperAdminPerm || $bolUserHasCreditManagementPerm);
		$bolCanCreateCreditAdjustments = TRUE;
		
		// Only apply the output mask if the DBO()->Charge is not invalid
		$bolApplyOutputMask = !DBO()->Charge->IsInvalid();

		$this->FormStart("AddAdjustment", "Adjustment", "Add");
		
		if (!$bolCanCreateCreditAdjustments)
		{
			// The user cannot create credit adjustments because they don't have the required permissions
			echo "<div class='MsgNotice'>You do not have the required permissions to create credit adjustments</div>";
		}
		
		echo "<div class='GroupedContent'>\n";
		
		// include all the properties necessary to add the record, which shouldn't have controls visible on the form
		if (DBO()->Service->Id->Value)
		{
			DBO()->Service->Id->RenderHidden();
			
			// Display the Service's FNN
			DBO()->Service->FNN->RenderOutput();
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
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Adjustment:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Charge.charge_type_id' name='Charge.charge_type_id' style='width:100%' onchange='Vixen.ValidateAdjustment.DeclareChargeType(this)'>\n";
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
		
		DBO()->Charge->Amount->RenderInput(CONTEXT_INCLUDES_GST, TRUE, $bolApplyOutputMask);
		// if the charge type has a fixed amount then disable the amount textbox
		if ($arrChargeTypes[$intChargeTypeId]['Fixed'])
		{
			echo "<script type='text/javascript'>document.getElementById('Charge.Amount').disabled = TRUE;</script>\n";
		}
		
		// Create a combo box containing the last 6 invoices associated with the account
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Invoice:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='InvoiceComboBox' name='Charge.Invoice' style='width: 100%'>\n";
		echo "         <option value=''>No Association</option>\n";
		foreach (DBL()->AccountInvoices as $dboInvoice)
		{
			$strInvoiceId = $dboInvoice->Id->Value;
			$strInvoiceCreatedOn = OutputMask()->ShortDate($dboInvoice->CreatedOn->Value);
			// Check if this invoice Id was the last one selected
			$strSelected = ($strInvoiceId == DBO()->Charge->Invoice->Value) ? "selected='selected'" : "";
			
			echo "         <option value='$strInvoiceId' $strSelected>$strInvoiceId ($strInvoiceCreatedOn)</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		// Create a textbox for including a note
		DBO()->Charge->Notes->RenderInput(CONTEXT_DEFAULT);
		
		echo "</div>\n"; // GroupedContent

		// create the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		$this->AjaxSubmit("Add Adjustment");
		echo "</div></div>\n";

		// define the data required of the javacode that handles events and validation of this form
		$strJsonCode = Json()->encode($arrChargeTypes);
		
		// Set the charge types in the javascript object that handles interactions with this popup window
		echo "<script type='text/javascript'>Vixen.ValidateAdjustment.SetChargeTypes($strJsonCode);</script>\n";

		// give the ChargeTypeCombo initial focus
		echo "<script type='text/javascript'>document.getElementById('Charge.charge_type_id').focus();</script>\n";
		
		$this->FormEnd();
	}
}

?>
