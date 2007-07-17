<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// delete_record.php
//----------------------------------------------------------------------------//
/**
 * delete_record
 *
 * HTML Template for the Delete Record HTML object
 *
 * HTML Template for the Delete Record HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to delete a Payment, Adjustment, or recurring adjustment
 *
 * @file		delete_record.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateDeleteRecord
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateDeleteRecord
 *
 * HTML Template class for the Delete Payment HTML object
 *
 * HTML Template class for the Delete Payment HTML object
 *
 *
 * @package	ui_app
 * @class	HtmlTemplateDeleteRecord
 * @extends	HtmlTemplate
 */
class HtmlTemplateDeleteRecord extends HtmlTemplate
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
		echo "<div class='PopupMedium'>\n";
		// Start the form
		$this->FormStart("DeleteRecord", DBO()->DeleteRecord->Application->Value, DBO()->DeleteRecord->Method->Value);

		// do code specific to the record type being deleted
		switch (DBO()->DeleteRecord->RecordType->Value)
		{
			case "Payment":
				echo "<h2 class='Payment'>Reverse Payment</h2>\n";
				// Display the description for the reverse payment operation
				DBO()->DeleteRecord->Description->RenderArbitrary("Are you sure you want to reverse the payment with the following details?");
				DBO()->Payment->PaidOn->RenderOutput();
				DBO()->Payment->Amount->RenderOutput();
				DBO()->Payment->Balance->RenderOutput();
				DBO()->Payment->PaymentType->RenderCallback("GetConstantDescription", Array("PaymentType"), RENDER_OUTPUT);
				DBO()->Payment->Id->RenderHidden();
				break;
			case "Adjustment":
				echo "<h2 class='Adjustment'>Delete Adjustment</h2>\n";
				// Display the description for the delete operation
				DBO()->DeleteRecord->Description->RenderArbitrary("Are you sure you want to delete the adjustment with the following details?");
				DBO()->Charge->CreatedOn->RenderOutput();
				DBO()->Charge->ChargeType->RenderOutput();
				DBO()->Charge->Description->RenderOutput();
				DBO()->Charge->Nature->RenderOutput();
				DBO()->Charge->Amount->RenderCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
				DBO()->Charge->Id->RenderHidden();
				break;
			case "RecurringAdjustment":
				echo "<h2 class='Adjustment'>Cancel Recurring Adjustment</h2>\n";
				// Display the description for the delete operation
				DBO()->DeleteRecord->Description->RenderArbitrary("Are you sure you want to cancel the recurring adjustment with the following details?");
				DBO()->RecurringCharge->CreatedOn->RenderOutput();
				DBO()->RecurringCharge->Description->RenderOutput();
				DBO()->RecurringCharge->MinCharge->RenderCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
				DBO()->RecurringCharge->TotalCharged->RenderCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
				
				// calculate the amount owing on the recurring charge
				$fltAmountOwing = DBO()->RecurringCharge->MinCharge->Value - DBO()->RecurringCharge->TotalCharged->Value;
				if ((DBO()->RecurringCharge->Nature->Value == NATURE_DR) && ($fltAmountOwing > 0.0))
				{	
					echo "<div class='SmallSeperator'></div>\n";
					
					// The recurring charge is a debit.  A charge will be made to cover the remaining minimum cost, and cancellation fee
					DBO()->DeleteRecord->Description->RenderArbitrary("WARNING: Cancelling this adjustment will incur a cost to the customer");
					
					DBO()->RecurringCharge->MinCharge->RenderCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
					DBO()->RecurringCharge->TotalCharged->RenderCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
					DBO()->RecurringCharge->CancellationFee->RenderCallback("AddGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
					
					DBO()->RecurringCharge->TotalAdditionalCharge = AddGST($fltAmountOwing + DBO()->RecurringCharge->CancellationFee->Value);
					DBO()->RecurringCharge->TotalAdditionalCharge->RenderOutput();
				}
				
				DBO()->RecurringCharge->Id->RenderHidden();
				break;
			default:
				die;
		}
		
		// display the textarea for the accompanying note
		echo "<div class='Seperator'></div>\n";
		DBO()->Note->Note->RenderInput();
		
		// display the buttons
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		$this->AjaxSubmit("OK");
		echo "</div>\n";
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Seperator'></div>\n";
		
		$this->FormEnd();
		echo "</div>\n";
	}
}

?>
