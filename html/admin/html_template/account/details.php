<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// details.php
//----------------------------------------------------------------------------//
/**
 * details
 *
 * HTML Template for the details of an Account.  Primarily those stored in the Account table 
 *
 * HTML Template for the details of an Account.  Primarily those stored in the Account table 
 *
 * @file		details.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountDetails
 *
 * A specific HTML Template object
 *
 * An Account Details HTML Template object
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountDetails extends HtmlTemplate
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
		
		$this->LoadJavascript("account_details");
		$this->LoadJavascript("constants");

		$this->LoadJavascript("prototype");
		$this->LoadJavascript("jquery");
		$this->LoadJavascript("json");
		$this->LoadJavascript("reflex_popup");
		$this->LoadJavascript("credit_card_type");
		$this->LoadJavascript("credit_card_payment");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_VIEW:
				$this->_RenderForViewing();
				break;
			case HTML_CONTEXT_EDIT:
				$this->_RenderForEditing();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderForViewing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForViewing()
	 *
	 * Renders the Account Details in "View" mode
	 *
	 * Renders the Account Details in "View" mode
	 *
	 * @method
	 */
	private function _RenderForViewing()
	{
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		// Render the details of the Account
		DBO()->CustomerGroup->Id = DBO()->Account->CustomerGroup->Value;
		DBO()->CustomerGroup->Load();
		$strCustomerGroup = DBO()->CustomerGroup->InternalName->Value;
		DBO()->Account->CustomerGroup->RenderArbitrary($strCustomerGroup, RENDER_OUTPUT);
		
		DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("account_status"), RENDER_OUTPUT);
		
		DBO()->Account->Id->RenderOutput();
		
		if ((DBO()->Account->BusinessName->Value == "") && (DBO()->Account->TradingName->Value == ""))
		{
			// There is no business name of trading name
			DBO()->Account->BusinessName->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
		}
		
		if (DBO()->Account->BusinessName->Value != "")
		{
			// Display the Business Name, but only if there is one
			DBO()->Account->BusinessName->RenderOutput();
		}
		
		if (DBO()->Account->TradingName->Value != "")
		{
			// Display the Trading name, but only if there is one
			DBO()->Account->TradingName->RenderOutput();
		}

		if ((DBO()->Account->ABN->Value == "") && (DBO()->Account->ACN->Value == ""))
		{
			// There is no ABN or ACN
			DBO()->Account->ABN->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
		}
		
		if (DBO()->Account->ABN->Value != "")
		{
			// Display the ABN, but only if there is one
			DBO()->Account->ABN->RenderOutput();
		}
		
		if (DBO()->Account->ACN->Value != "")
		{
			// If there is no ABN, display the ACN, if there is one
			DBO()->Account->ACN->RenderOutput();
		}
		
		// Don't include address and BillingType/BillingMethod details if this
		// HtmlTemplate is being rendered on the InvoicesAndPayments page 
		if (!DBO()->Account->InvoicesAndPaymentsPage->Value)
		{
			// Display the first line of the address, but only if there is one
			if (DBO()->Account->Address1->Value != "")
			{
				DBO()->Account->Address1->RenderOutput();
			}
			else
			{
				DBO()->Account->Address1->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
			}
			
			if (DBO()->Account->Address2->Value != "")
			{
				DBO()->Account->Address2->RenderOutput();
			}
			
			if (DBO()->Account->Suburb->Value != "")
			{
				DBO()->Account->Suburb->RenderOutput();
			}
			
			if (DBO()->Account->Postcode->Value != "")
			{
				DBO()->Account->Postcode->RenderOutput();
			}
			
			if (DBO()->Account->State->Value != "")
			{
				DBO()->Account->State->RenderOutput();
			}
			
			if (DBO()->Account->Country->Value != "")
			{
				DBO()->Account->Country->RenderOutput();
			}
			
			DBO()->Account->BillingType->RenderCallback("GetConstantDescription", Array("BillingType"), RENDER_OUTPUT);
			DBO()->Account->BillingMethod->RenderCallback("GetConstantDescription", Array("BillingMethod"), RENDER_OUTPUT);
		}
		
?>
<div class="DefaultElement">
	<div id="Account.Balance.Output" name="Account.Balance" class="DefaultOutput Currency">$<?php DBO()->Account->Balance->Render(); ?><?php
		require_once dirname(__FILE__) . '/../../../../lib/classes/credit/card/Credit_Card_Payment.php';
		if (Credit_Card_Payment::availableForCustomerGroup(DBO()->Account->CustomerGroup->Value))
		{
			echo Credit_Card_Payment::getPopupActionButton(DBO()->Account->Id->Value);
		}
	?></div>
	<div id="Account.Balance.Label" class="DefaultLabel">
		<span> &nbsp;</span>
		<span id="Account.Balance.Label.Text">Balance : </span>
	</div>
</div>
<?php

		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();
		
		if (DBO()->Account->Sample->Value === NULL)
		{
			// If Account->Sample is NULL, then set it to 0
			DBO()->Account->Sample = 0;
		}
		DBO()->Account->Sample->RenderOutput();
		
		// This property is DEPRICATED
		//DBO()->Account->DisableLateNotices->RenderOutput();
		
		if (DBO()->Account->LatePaymentAmnesty->Value == substr(END_OF_TIME, 0, 10))
		{
			DBO()->Account->LatePaymentAmnesty = "Never send late notices";
		}
		elseif (DBO()->Account->LatePaymentAmnesty->Value < date("Y-m-d"))
		{
			DBO()->Account->LatePaymentAmnesty = "Send late notices";
		}
		else
		{
			DBO()->Account->LatePaymentAmnesty = "Exempt until after ". date("jS F, Y", strtotime(DBO()->Account->LatePaymentAmnesty->Value));
		}
		DBO()->Account->LatePaymentAmnesty->RenderOutput();


		// Load the credit control statuses
		DBO()->credit_control_status->Id = DBO()->Account->credit_control_status->Value;
		DBO()->credit_control_status->Load();
		DBO()->Account->credit_control_status = DBO()->credit_control_status->name->Value;
		DBO()->Account->credit_control_status->RenderOutput();

		if (DBO()->Account->DisableLatePayment->Value === NULL)
		{
			// If DisableLatePayment is NULL then set it to 0
			DBO()->Account->DisableLatePayment = 0;
		}
		if (DBO()->Account->DisableLatePayment->Value < -1)
		{	
			DBO()->Account->DisableLatePayment->Value = abs(DBO()->Account->DisableLatePayment->Value);
		}
		DBO()->Account->DisableLatePayment->RenderOutput();
		
		// To avoid a double negative display ChargeAdminFee instead of DisableDDR
		DBO()->Account->ChargeAdminFee = !(DBO()->Account->DisableDDR->Value);
		DBO()->Account->ChargeAdminFee->RenderOutput();


		// Details of last automated actions
		// ... automatic notices sent
		DBO()->automatic_invoice_action->Id = DBO()->Account->last_automatic_invoice_action->Value;
		DBO()->automatic_invoice_action->Load();
		if (DBO()->Account->last_automatic_invoice_action->Value != AUTOMATIC_INVOICE_ACTION_NONE)
		{
			DBO()->Account->last_automatic_invoice_action = 
				DBO()->automatic_invoice_action->name->Value . ' on ' .
				OutputMask()->LongDateAndTime(DBO()->Account->last_automatic_invoice_action_datetime->Value);
		}
		else
		{
			DBO()->Account->last_automatic_invoice_action = DBO()->automatic_invoice_action->name->Value;
		}
		DBO()->Account->last_automatic_invoice_action->RenderOutput();
		// ... automatic account barring
		DBO()->automatic_barring_status->Id = DBO()->Account->automatic_barring_status->Value;
		DBO()->automatic_barring_status->Load();
		if (DBO()->Account->automatic_barring_status->Value != AUTOMATIC_BARRING_STATUS_NONE)
		{
			DBO()->Account->automatic_barring_status = 
				DBO()->automatic_barring_status->name->Value . ' on ' .
				OutputMask()->LongDateAndTime(DBO()->Account->automatic_barring_datetime->Value);
		}
		else
		{
			DBO()->Account->automatic_barring_status = DBO()->automatic_barring_status->name->Value;
		}
		DBO()->Account->automatic_barring_status->RenderOutput();

		if (DBO()->Account->tio_reference_number->Value !== NULL)
		{
			// This account has a TIO reference number.  Display it
			$strTIORefNum = htmlspecialchars(DBO()->Account->tio_reference_number->Value, ENT_QUOTES);
			echo "
<div class='DefaultElement'>
	<div class='DefaultOutput'>$strTIORefNum</div>
	<div class='DefaultLabel'>
		<span> &nbsp;</span>
		<span>T.I.O. Reference Number :</span>
	</div>
</div>";
		}
		
		echo "</div>\n"; // GroupedContent
		
		// Render the buttons but only if the user has operator privileges
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Edit Details", "Vixen.AccountDetails.RenderAccountDetailsForEditing();");
			echo "</div></div>\n";
		}
		else
		{
			echo "<div class='SmallSeperator'></div>\n";
		}
		
		// Initialise the AccountDetails object and register the OnAccountDetailsUpdate Listener
		$strInvoicesAndPaymentsPage	= (DBO()->Account->InvoicesAndPaymentsPage->Value) ? "true" : "false";
		$intAccountId				= DBO()->Account->Id->Value;
		$strJavascript = "Vixen.AccountDetails.InitialiseView($intAccountId, '{$this->_strContainerDivId}', $strInvoicesAndPaymentsPage);";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderForEditing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForEditing()
	 *
	 * Renders the Account Details in "Edit" mode
	 *
	 * Renders the Account Details in "Edit" mode
	 *
	 * @method
	 */
	private function _RenderForEditing()
	{
		$bolIsAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
	
		$this->FormStart("EditAccount", "Account", "SaveDetails");
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		// Render hidden values
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		DBO()->Account->InvoicesAndPaymentsPage->RenderHidden();
		
		// Render the details of the Account
		DBO()->Account->Id->RenderOutput();

		if ($bolIsAdminUser)
		{
			// Render the CustomerGroup combobox
			DBL()->CustomerGroup->OrderBy("InternalName");
			DBL()->CustomerGroup->Load();
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Customer Group :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.CustomerGroup' name='Account.CustomerGroup' style='width:330px'>\n";
			foreach (DBL()->CustomerGroup as $dboCustomerGroup)
			{
				$intCustomerGroupId		= $dboCustomerGroup->Id->Value;
				$strCustomerGroupName	= $dboCustomerGroup->InternalName->Value;
				$strSelected = (DBO()->Account->CustomerGroup->Value == $intCustomerGroupId) ? "selected='selected'" : "";
				echo "		<option value='$intCustomerGroupId' $strSelected>$strCustomerGroupName</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}
		else
		{
			// The user does not have permission to edit this property
			DBO()->CustomerGroup->Id = DBO()->Account->CustomerGroup->Value;
			DBO()->CustomerGroup->Load();
			$strCustomerGroupName = DBO()->CustomerGroup->InternalName->Value;
			DBO()->Account->CustomerGroup->RenderArbitrary($strCustomerGroupName, RENDER_OUTPUT);
		}
		
		// Work out which Account Status' can be chosen
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			// The account is pending activation
			$arrAccountStatuses = array(ACCOUNT_STATUS_PENDING_ACTIVATION, ACCOUNT_STATUS_ACTIVE);
		}
		else
		{
			// The account has already been activated
			$arrAccountStatuses = array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED, ACCOUNT_STATUS_SUSPENDED, ACCOUNT_STATUS_DEBT_COLLECTION, ACCOUNT_STATUS_ARCHIVED);
		}
		
		$strStatusOptions = "";
		foreach ($arrAccountStatuses as $intStatus)
		{
			$strSelected		= (DBO()->Account->Archived->Value == $intStatus)? "selected='selected'" : "";
			$strStatus			= GetConstantDescription($intStatus, "account_status");
			$strStatusOptions	.= "<option value='$intStatus' $strSelected>$strStatus</option>";
		}
		
		// Render the Account Status Combobox
		echo "
<div class='DefaultElement'>
	<div class='DefaultLabel'>&nbsp;&nbsp;Status :</div>
	<div class='DefaultOutput'>
		<select id='AccountStatusCombo' name='Account.Archived' style='width:330px'>$strStatusOptions</select>
	</div>
</div>";
		
		
		DBO()->Account->BusinessName->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		DBO()->Account->TradingName->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));

		DBO()->Account->ABN->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		DBO()->Account->ACN->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		
		// Don't include address and BillingMethod details if this
		// HtmlTemplate is being rendered on the InvoicesAndPayments page 
		if (!DBO()->Account->InvoicesAndPaymentsPage->Value)
		{
			DBO()->Account->Address1->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			DBO()->Account->Address2->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			DBO()->Account->Suburb->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			DBO()->Account->Postcode->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
			
			// Render the State combobox
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.State' name='Account.State' style='width:330px'>\n";
			foreach ($GLOBALS['*arrConstant']['ServiceStateType'] as $strKey=>$arrState)
			{
				$strSelected = (DBO()->Account->State->Value == $strKey) ? "selected='selected'" : "";
				echo "		<option value='$strKey' $strSelected>{$arrState['Description']}</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
			
			// Render the BillingMethod combobox
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Billing Method :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.BillingMethod' name='Account.BillingMethod' style='width:330px'>\n";
			foreach ($GLOBALS['*arrConstant']['BillingMethod'] as $intConstant=>$arrBillingMethodSelection)
			{
				if ($intConstant == BILLING_METHOD_EMAIL_SENT)
				{
					// Don't include this option
					continue;
				}
				$strSelected = (DBO()->Account->BillingMethod->Value == $intConstant) ? "selected='selected'" : "";
				echo "		<option value='$intConstant' $strSelected>{$arrBillingMethodSelection['Description']}</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}
		
		DBO()->Account->Sample->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("style:width"=>"330px"));
		
		// This property is DEPRICATED
		//DBO()->Account->DisableLateNotices->RenderInput();
		
		// Build the Array of options for the Late Notices combobox
		// The key to this array will be the amnesty date as a string, so that it can be sorted, and any previous
		// value for LatePaymentAmnesty can safely override any new ones set up
		$strEndOfTime				= substr(END_OF_TIME, 0, 10);
		$arrOptions					= Array();
		$arrOptions[NULL]			= "Send late notices";
		
		$intPaymentTerms			= DBO()->Account->PaymentTerms->Value;
		$intLastMonthsBillAmnesty	= strtotime("+ $intPaymentTerms days", GetStartDateTimeForBillingPeriod());
		if ($intLastMonthsBillAmnesty > time())
		{
			$strLastMonthsBillAmnesty = date("Y-m-d", $intLastMonthsBillAmnesty);
			
			// The user can still flag the account to not receive late notices regarding last months bill
			$arrOptions[$strLastMonthsBillAmnesty]	= "Exempt until ". date("jS F", $intLastMonthsBillAmnesty);
		}
		$intThisMonthsBillAmnesty					= strtotime("+ $intPaymentTerms days", GetStartDateTimeForNextBillingPeriod());
		$strThisMonthsBillAmnesty					= date("Y-m-d", $intThisMonthsBillAmnesty);
		$arrOptions[$strThisMonthsBillAmnesty]		= "Exempt until ". date("jS F", $intThisMonthsBillAmnesty);
		$arrOptions[$strEndOfTime]					= "Never send late notices";
		
		// Add the Account's current LatePaymentAmnesty if it is in the future (or today) and is not set to END_OF_TIME
		$strLatePaymentAmnesty = DBO()->Account->LatePaymentAmnesty->Value;
		if (($strLatePaymentAmnesty != $strEndOfTime) && ($strLatePaymentAmnesty >= date("Y-m-d")))
		{
			// If this date is already in the array of options, then it will just override it
			$arrOptions[$strLatePaymentAmnesty] = "Exempt until ". date("jS F", strtotime($strLatePaymentAmnesty));
		}
		
		// Sort the list 
		ksort($arrOptions);
		
		// Render the combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Late Notices :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Account.LatePaymentAmnesty' name='Account.LatePaymentAmnesty' style='width:330px'>\n";
		foreach ($arrOptions as $strDate=>$strLabel)
		{
			$strSelected = (DBO()->Account->LatePaymentAmnesty->Value == $strDate) ? "selected='selected'" : "";
			echo "		<option value='$strDate' $strSelected>$strLabel</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		

		// Load the credit control statuses
		DBL()->credit_control_status->Load();
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Credit Control Status :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Account.credit_control_status' name='Account.credit_control_status' style='width:330px'>\n";
		while ($cc = DBL()->credit_control_status->current())
		{
			$id = $cc->id->Value;
			$strLabel = $cc->name->Value;
			$strSelected = (DBO()->Account->credit_control_status->Value == $id) ? "selected='selected'" : "";
			echo "		<option value='$id' $strSelected>$strLabel</option>\n";
			DBL()->credit_control_status->next();
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		DBO()->Account->DisableLatePayment->RenderInput(1);
		
		// To avoid a double negative, display ChargeAdminFee instead of DisableDDR
		//DBO()->Account->DisableDDR->RenderInput(1);
		// If ChargeAdminFee has not been set, then set it to the opposite of what DisableDDR is set to
		if (!DBO()->Account->ChargeAdminFee->IsSet)
		{
			DBO()->Account->ChargeAdminFee = !(DBO()->Account->DisableDDR->Value);
		}
		DBO()->Account->ChargeAdminFee->RenderInput();
		
		// TIO reference number and checkbox
		if ((DBO()->Account->tio_reference_number->Value !== NULL && trim(DBO()->Account->tio_reference_number->Value) != "") || (DBO()->Account->WithTIO->Value))
		{
			$strTIOChecked		= "checked='checked'";
			$strTIODisplayStyle	= "visibility:visible;display:inline;";
			$strTIOLabel		= "T.I.O Reference Number";
		}
		else
		{
			$strTIOChecked		= "";
			$strTIODisplayStyle	= "visibility:hidden;display:none;";
			$strTIOLabel		= "With T.I.O.";
		}
		$strTIORefNum 		= htmlspecialchars(DBO()->Account->tio_reference_number->Value, ENT_QUOTES);
		$strTIOTextboxClass	= (DBO()->Account->tio_reference_number->IsInvalid())? "DefaultInvalidInputText" : "DefaultInputText";

		echo "
<div style='top:2px;position:relative;height:25px;'>
	<span>&nbsp;&nbsp;</span><span id='TIOLabel'>$strTIOLabel</span>
	<input type='checkbox' id='Account.WithTIO' name='Account.WithTIO' $strTIOChecked style='position:absolute;left:197px'/>
	<input type='text' id='Account.tio_reference_number' name='Account.tio_reference_number' value='$strTIORefNum' class='$strTIOTextboxClass' style='{$strTIODisplayStyle}position:absolute;left:223px;width:305px;'/>
</div>
";
		
		echo "</div>\n"; // GroupedContent
		
		// Render buttons
		echo "
<div class='ButtonContainer'>
	<div style='float:right'>
		<input type='button' style='display:none;' id='AccountEditSubmitButton' value='Commit Changes' onclick=\"Vixen.Ajax.SendForm('VixenForm_EditAccount', 'Commit Changes', 'Account', 'SaveDetails', '', '', '', '{$this->_strContainerDivId}')\"></input>
		<input type='button' value='Cancel' onclick='Vixen.AccountDetails.CancelEdit();'></input>
		<input type='button' value='Commit Changes' onclick='Vixen.AccountDetails.CommitChanges()'></input>
	</div>
</div>
";
		
		// Load the Constants required for the javascript code
		$jsonAccountStatuses = Json()->encode($GLOBALS['*arrConstant']['account_status']);
		echo "<script type='text/javascript'>\$Const.SetConstantGroup('account_status', $jsonAccountStatuses);</script>";
		
		// Initialise the AccountDetails object
		$strInvoicesAndPaymentsPage = (DBO()->Account->InvoicesAndPaymentsPage->Value) ? "true" : "false";		
		$jsonObjAccount				= Json()->encode(DBO()->Account->_arrProperties);
		$strJavascript = "Vixen.AccountDetails.InitialiseEdit($jsonObjAccount, '{$this->_strContainerDivId}', $strInvoicesAndPaymentsPage);";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
		
		$this->FormEnd();
		
		// If the user doesn't have Admin privileges they cannot select the "Never charge a late payment fee" option
		if (!$bolIsAdminUser)
		{
			// The user doesn't have admin privileges
			$strJsCode .=	"document.getElementById('Account.DisableLatePayment_1').disabled = true;\n".
							"document.getElementById('Account.DisableLatePayment_1.Label').style.color = '#4C4C4C';\n";
		}
		
		echo "<script type='text/javascript'>$strJsCode</script>";
	}

}

?>
