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
		
		$this->LoadJavascript("account_details");
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
			case HTML_CONTEXT_LEDGER_DETAIL:
				$this->_RenderLedgerDetail();
				break;
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
		DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("Account"), RENDER_OUTPUT);
		
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
		
		DBO()->Account->Balance->RenderOutput();
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
		
		if (DBO()->Account->DisableLatePayment->Value === NULL)
		{
			// If DisableLatePayment is NULL then set it to 0
			DBO()->Account->DisableLatePayment = 0;
		}
		DBO()->Account->DisableLatePayment->RenderOutput();
		
		// To avoid a double negative display ChargeAdminFee instead of DisableDDR
		DBO()->Account->ChargeAdminFee = !(DBO()->Account->DisableDDR->Value);
		DBO()->Account->ChargeAdminFee->RenderOutput();
		
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
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
	
		$this->FormStart("EditAccount", "Account", "SaveDetails");
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		// Render hidden values
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		DBO()->Account->InvoicesAndPaymentsPage->RenderHidden();
		
		// Render the details of the Account
		DBO()->Account->Id->RenderOutput();

		if ($bolUserHasAdminPerm)
		{
			// Render the CustomerGroup combobox
			DBL()->CustomerGroup->OrderBy("InternalName");
			DBL()->CustomerGroup->Load();
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Customer Group :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.CustomerGroup' name='Account.CustomerGroup'>\n";
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
		
		// Reorder the list of Account Status values
		$arrAccountStatus[ACCOUNT_ACTIVE]			= GetConstantDescription(ACCOUNT_ACTIVE, "Account");
		$arrAccountStatus[ACCOUNT_CLOSED]			= GetConstantDescription(ACCOUNT_CLOSED, "Account");
		$arrAccountStatus[ACCOUNT_SUSPENDED]		= GetConstantDescription(ACCOUNT_SUSPENDED, "Account");
		$arrAccountStatus[ACCOUNT_DEBT_COLLECTION]	= GetConstantDescription(ACCOUNT_DEBT_COLLECTION, "Account");
		$arrAccountStatus[ACCOUNT_ARCHIVED]			= GetConstantDescription(ACCOUNT_ARCHIVED, "Account");
		
		// Render the Account Status Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Account Status :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Account.Archived' name='Account.Archived'>\n";
		foreach ($arrAccountStatus as $intConstant=>$strAccountStatus)
		{
			if (($intConstant == ACCOUNT_DEBT_COLLECTION) || ($intConstant == ACCOUNT_ARCHIVED) || ($intConstant == ACCOUNT_SUSPENDED))
			{
				// Only users with Admin privileges can mark an account as ACCOUNT_DEBT_COLLECTION or ACCOUNT_ARCHIVED
				if (!$bolUserHasAdminPerm)
				{
					// The user does not have permission to select these options
					continue;
				}
			}

			$strSelected = (DBO()->Account->Archived->Value == $intConstant) ? "selected='selected'" : "";
			echo "         <option value='$intConstant' $strSelected>$strAccountStatus</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		DBO()->Account->BusinessName->RenderInput();
		DBO()->Account->TradingName->RenderInput();

		DBO()->Account->ABN->RenderInput();
		DBO()->Account->ACN->RenderInput();
		
		// Don't include address and BillingMethod details if this
		// HtmlTemplate is being rendered on the InvoicesAndPayments page 
		if (!DBO()->Account->InvoicesAndPaymentsPage->Value)
		{
			DBO()->Account->Address1->RenderInput();
			DBO()->Account->Address2->RenderInput();
			DBO()->Account->Suburb->RenderInput();
			DBO()->Account->Postcode->RenderInput();
			
			// Render the State combobox
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='Account.State' name='Account.State'>\n";
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
			echo "      <select id='Account.BillingMethod' name='Account.BillingMethod'>\n";
			foreach ($GLOBALS['*arrConstant']['BillingMethod'] as $intConstant=>$arrBillingMethodSelection)
			{
				$strSelected = (DBO()->Account->BillingMethod->Value == $intConstant) ? "selected='selected'" : "";
				echo "		<option value='$intConstant' $strSelected>{$arrBillingMethodSelection['Description']}</option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}
		
		DBO()->Account->Sample->RenderInput();
		
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
		echo "      <select id='Account.LatePaymentAmnesty' name='Account.LatePaymentAmnesty'>\n";
		foreach ($arrOptions as $strDate=>$strLabel)
		{
			$strSelected = (DBO()->Account->LatePaymentAmnesty->Value == $strDate) ? "selected='selected'" : "";
			echo "		<option value='$strDate' $strSelected>$strLabel</option>\n";
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
		
		echo "</div>\n"; // GroupedContent
		
		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.AccountDetails.CancelEdit();");
		$this->AjaxSubmit("Commit Changes");
		echo "</div></div>\n";
		
		// Initialise the AccountDetails object
		$strInvoicesAndPaymentsPage = (DBO()->Account->InvoicesAndPaymentsPage->Value) ? "true" : "false";		
		$intAccountId				= DBO()->Account->Id->Value;
		$strJavascript = "Vixen.AccountDetails.InitialiseEdit($intAccountId, '{$this->_strContainerDivId}', $strInvoicesAndPaymentsPage);";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
		
		$this->FormEnd();
		
		//Resize the textboxes and the comboboxes and disable the "Never charge a late payment fee" radio
		$strJsCode  = "Vixen.AccountDetails.ResizeEditControls(330, ". ((!DBO()->Account->InvoicesAndPaymentsPage->Value) ? "true" : "false") .");";
		
		// If the user doesn't have Admin privileges they cannot select the "Never charge a late payment fee" option
		if (!$bolUserHasAdminPerm)
		{
			// The user doesn't have admin privileges
			$strJsCode .=	"document.getElementById('Account.DisableLatePayment_1').disabled = true;\n".
							"document.getElementById('Account.DisableLatePayment_1.Label').style.color = '#4C4C4C';\n";
		}
		
		echo "<script type='text/javascript'>$strJsCode</script>";
	}

	//------------------------------------------------------------------------//
	// _RenderLedgerDetail DEPRICATED
	//------------------------------------------------------------------------//
	/**
	 * _RenderLedgerDetail()
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * @method
	 */
	private function _RenderLedgerDetail()
	{
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		// Declare the start of the form
		$this->FormStart('AccountDetails', 'Account', 'InvoicesAndPayments');
		
		// Render the Id of the Account as a hidden input
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->Archived->RenderHidden();

		// Use a table to stick the account details and the checkbox and radio buttons next to each other

		echo "<table border='0' cellspacing='0' cellpadding='0'>\n";
		echo "   <tr>\n";
		echo "      <td width='65%' valign='top'>\n";
		// Render the details of the Account
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value != "")
		{
			// Display the Business Name, but only if there is one
			DBO()->Account->BusinessName->RenderOutput();
		}
		elseif (DBO()->Account->TradingName->Value != "")
		{
			// If there was no Business Name, display the Trading Name, if there is one
			DBO()->Account->TradingName->RenderOutput();
		}
		else
		{
			// There is no Business Name or Trading Name
			DBO()->Account->BusinessName->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
		}
		if (DBO()->Account->ABN->Value != "")
		{
			DBO()->Account->ABN->RenderOutput();
		}
		elseif (DBO()->Account->ACN->Value != "")
		{
			DBO()->Account->ACN->RenderOutput();
		}
		else
		{
			DBO()->Account->ABN->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
		}
		
		// Retrieve the CustomerGroup
		DBO()->CustomerGroup->Id = DBO()->Account->CustomerGroup->Value;
		DBO()->CustomerGroup->Load();
		$strCustomerGroup = DBO()->CustomerGroup->InternalName->Value;
		
		DBO()->Account->CustomerGroup->RenderArbitrary($strCustomerGroup, RENDER_OUTPUT);
		
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();
		DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("Account"), RENDER_OUTPUT);
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			// The user can edit the Admin fee and late payment fee properties
			// Finish off the first column
			echo "      </td>\n";
			
			// Start the second
			echo "      <td width='35%' valign='top'>\n";
			// Render the properties that can be changed
			DBO()->Account->DisableDDR->RenderInput();
			echo "<div class='ContentSeparator'></div>\n";
			DBO()->Account->DisableLatePayment->RenderInput();
			echo "<div class='ContentSeparator'></div>\n";
			DBO()->Account->DisableLateNotices->RenderInput(1);
			echo "      </td>\n";
		}
		else
		{
			// The user can't edit the Admin fee and late payment fee properties
			// Render them as labels
			DBO()->Account->DisableDDR->RenderOutput();
			DBO()->Account->DisableLateNotices->RenderOutput();
			if (DBO()->Account->DisableLatePayment->Value === NULL)
			{
				// If DisableLatePayment is NULL then set it to 0
				DBO()->Account->DisableLatePayment = 0;
			}
			DBO()->Account->DisableLatePayment->RenderOutput();
			echo "      </td>\n";
		}
		// Finish the table
		echo "   </tr>\n";
		echo "</table>\n";

		// If the user doesn't have Admin privileges they cannot select the "Never charge a late payment fee" option
		// If the user doesn't have Operator privileges then the checkbox and radio buttons aren't even rendered
		if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN) && AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			// Disable the "Never Charge a late payment fee" radio option
			echo "<script type='text/javascript'>document.getElementById('Account.DisableLatePayment_1').disabled = true;
					document.getElementById('Account.DisableLatePayment_1.Label').style.color='#4C4C4C';</script>";
		}
		
		echo "</div>\n";  //GroupedContent
		
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			// Render the submit button
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->AjaxSubmit("Apply Changes");
			echo "</div></div>\n";
		}
		else
		{
			echo "<div class='SmallSeperator'></div>\n";
		}
		
		// Declare the end of the form
		$this->FormEnd();
	}
}

?>
