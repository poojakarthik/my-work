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
 * HTML Template for the Add Charge HTML object
 *
 * HTML Template for the Add Charge HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add an charge.
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
// HtmlTemplateChargeAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateChargeAdd
 *
 * HTML Template class for the Add Charge HTML object
 *
 * HTML Template class for the Add Charge HTML object
 * displays the form used to add an charge
 *
 * @package	ui_app
 * @class	HtmlTemplateChargeAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateChargeAdd extends HtmlTemplate
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
		$this->LoadJavascript("validate_charge");
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
		// Currently anyone can create credit charges
		// Originally the user required either the Proper Admin permission or the Credit Management permission
		$bolCanCreateCreditCharges	= TRUE;
		
		// If IsRerateAdjustment & AmountOverride given, it must be an adjustment made post rerating an invoice
		$bolRerateAdjustment	= !!DBO()->Rerate->IsRerateAdjustment->Value && !!DBO()->AmountOverride->Amount->Value;
		
		// Only apply the output mask if the DBO()->Charge is not invalid
		$bolApplyOutputMask	= !DBO()->Charge->IsInvalid();
		
		// Name of the charge model (e.g. 'Charge' or 'Adjustment')
		$strChargeModel	= Constant_Group::getConstantGroup('charge_model')->getConstantName(DBO()->ChargeModel->Id->Value);
		
		$this->FormStart("Add{$strChargeModel}", "{$strChargeModel}", "Add");
		if (!$bolCanCreateCreditCharges)
		{
			// The user cannot create credit charges because they don't have the required permissions
			echo "<div class='MsgNotice'>You do not have the required permissions to create credit {$strChargeModel}s</div>";
		}
		echo "<div class='GroupedContent'>\n";
		
		// include all the properties necessary to add the record, which shouldn't have controls visible on the form
		if (DBO()->Service->Id->Value)
		{
			DBO()->Service->Id->RenderHidden();
			
			// Display the Service's FNN
			DBO()->Service->FNN->RenderOutput();
		}
		
		// Display account details
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		if (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		
		// Build array of available charge type information
		foreach (DBL()->ChargeTypesAvailable as $dboChargeType)
		{
			$intChargeTypeId	= $dboChargeType->Id->Value;
			
			// Add ChargeType details to an array that will be passed to the javascript that handles events on the ChargeTypeCombo
			$arrChargeTypeData['ChargeType']	= $dboChargeType->ChargeType->Value;
			$arrChargeTypeData['Nature']		= $dboChargeType->Nature->Value;
			$arrChargeTypeData['Fixed']			= $dboChargeType->Fixed->Value;
			
			// Add GST to the default amount and format it as a money value
			$fltAmountIncGST					= AddGST($dboChargeType->Amount->Value);
			$strAmount							= OutputMask()->MoneyValue($fltAmountIncGST, 2, TRUE);
			$arrChargeTypeData['Amount']		= $strAmount;
			$arrChargeTypeData['Description']	= $dboChargeType->Description->Value;
			
			// Cache charge type info
			$arrChargeTypes[$intChargeTypeId] 	= $arrChargeTypeData;
		}
		
		if ($bolRerateAdjustment)
		{
			// Rerate Adjustment, show only the System Charge Type for Rerating, disallow selection of a charge type 
			$objChargeType			= Charge_Type_System_Config::getChargeTypeForSystemChargeType(CHARGE_TYPE_SYSTEM_RERATE);
			DBO()->ChargeType->Id	= $objChargeType->Id;
			DBO()->ChargeType->Load();
			
			// Render the charge_type_id as hidden
			DBO()->Charge->charge_type_id	= DBO()->ChargeType->Id->Value;
			DBO()->Charge->charge_type_id->RenderHidden();
		}
		else
		{
			// Show a select with all available charge types as options
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;{$strChargeModel}:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Charge.charge_type_id' name='Charge.charge_type_id' style='width:100%' onchange='Vixen.ValidateCharge.DeclareChargeType(this)'>\n";
			foreach ($arrChargeTypes as $intChargeTypeId => $arrChargeTypeData)
			{
				// Check if this ChargeType was the last one selected
				if ((DBO()->ChargeType->Id->Value) && ($intChargeTypeId == DBO()->ChargeType->Id->Value))
				{
					$strSelected	= "selected='selected'";
				}
				else
				{
					$strSelected	= "";
				}
				$strDescription	= "{$arrChargeTypeData['Nature']}: {$arrChargeTypeData['Description']} ({$arrChargeTypeData['ChargeType']})";
				echo "		<option id='ChargeType.{$intChargeTypeId}' {$strSelected} value='{$intChargeTypeId}'>". htmlspecialchars($strDescription) ."</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}
		
		// If a charge type hasn't been selected then use the first one from the list
		if (!DBO()->ChargeType->Id->Value)
		{
			reset($arrChargeTypes);
			DBO()->ChargeType->Id	= key($arrChargeTypes);
		}
		
		DBO()->ChargeType->Id->RenderHidden();
		$intChargeTypeId	= DBO()->ChargeType->Id->Value;
		
		// Display the charge code when the Charge Type has been selected
		if ($bolRerateAdjustment === false)
		{
			// Use the default amount of the charge type
			DBO()->Charge->Amount			= $arrChargeTypes[$intChargeTypeId]['Amount'];
			DBO()->ChargeType->ChargeType 	= $arrChargeTypes[$intChargeTypeId]['ChargeType'];
			DBO()->ChargeType->Description 	= $arrChargeTypes[$intChargeTypeId]['Description'];
			DBO()->ChargeType->Nature 		= $arrChargeTypes[$intChargeTypeId]['Nature'];
		}
		
		echo "	<div class='DefaultElement'>
		  			<div id='ChargeType.ChargeType.Label' class='DefaultLabel'>&nbsp;&nbsp;{$strChargeModel} Type:</div>
		   			<div id='ChargeType.ChargeType.Output' class='DefaultOutput'>
		   				".DBO()->ChargeType->ChargeType->Value."
		   			</div class='DefaultOutput'>
				<div class='DefaultElement'>";
		
		// Display the description
		DBO()->ChargeType->Description->RenderOutput();
		
		// Display the nature of the charge
		DBO()->ChargeType->Nature->RenderOutput();
		
		// Override the amount if specified 
		if ($bolRerateAdjustment)
		{
			// Use override amount
			DBO()->Charge->Amount	= DBO()->AmountOverride->Amount->Value;
			
			// Tell the ValidateCharge javascript object what the amount override is
			$strOverrideAmount	= OutputMask()->MoneyValue(DBO()->AmountOverride->Amount->Value, 2, TRUE);
			echo "<script type='text/javascript'>Vixen.ValidateCharge.SetOverrideAmount('$strOverrideAmount');</script>\n";
		}
		
		DBO()->Charge->Amount->RenderInput(CONTEXT_INCLUDES_GST, TRUE, $bolApplyOutputMask);
		
		// If the charge type has a fixed amount then disable the amount textbox
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
			$strInvoiceId 			= $dboInvoice->Id->Value;
			$strInvoiceCreatedOn 	= OutputMask()->ShortDate($dboInvoice->CreatedOn->Value);
			
			// Check if this invoice Id was the last one selected
			$strSelected = ($strInvoiceId == DBO()->Charge->Invoice->Value) ? "selected='selected'" : "";
			
			echo "         <option value='$strInvoiceId' $strSelected>$strInvoiceId ($strInvoiceCreatedOn)</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		// Create a textbox for including a note
		DBO()->Charge->Notes->RenderInput(CONTEXT_DEFAULT);
		
		// Check if being called from a rerate, if so add 'isRerateAdjustment' & the fake invoice run id to the form (hidden)
		if (DBO()->Rerate->IsRerateAdjustment->Value)
		{
			DBO()->Rerate->IsRerateAdjustment->RenderHidden();
		}
		
		if (DBO()->RerateInvoiceRun->Id->Value)
		{
			DBO()->RerateInvoiceRun->Id->RenderHidden();
		}
		
		echo "</div>\n"; // GroupedContent

		// Create the buttons
		$strAddChargeJS	= "Vixen.Ajax.SendForm('VixenForm_Add{$strChargeModel}', 'Add {$strChargeModel}', '{$strChargeModel}', 'Add', 'Popup', 'Add{$strChargeModel}PopupId', 'medium', '{$this->_strContainerDivId}')";
		echo "	<div>
					<div style='float:right'>
						<input type='button' style='display:none;' id='AddChargeSubmitButton' value='Apply Changes' onclick=\"{$strAddChargeJS}\"></input>
						<input type='button' value='Submit Request' onclick='Vixen.ValidateCharge.SubmitRequest()'></input>
						<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)'></input>
					</div>
					<div style='float:none;clear:both'></div>
				</div>
				";

		// Define the data required of the javacode that handles events and validation of this form
		$strJsonCode = Json()->encode($arrChargeTypes);
		
		// Set the charge types in the javascript object that handles interactions with this popup window
		echo "<script type='text/javascript'>Vixen.ValidateCharge.SetChargeTypes($strJsonCode);</script>\n";

		if ($bolRerateAdjustment === false)
		{
			// Give the ChargeTypeCombo initial focus
			echo "<script type='text/javascript'>document.getElementById('Charge.charge_type_id').focus();</script>\n";
		}
		
		$this->FormEnd();
	}
}

?>
