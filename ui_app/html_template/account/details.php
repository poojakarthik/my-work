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
		echo "<div class='NarrowContent'>\n";

		// Render the details of the Account
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
		
		DBO()->Account->CustomerGroup->RenderCallback("GetConstantDescription", Array("CustomerGroup"), RENDER_OUTPUT);
		DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("Account"), RENDER_OUTPUT);
		
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
		
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();
		
		DBO()->Account->DisableDDR->RenderOutput();
		DBO()->Account->DisableLatePayment->RenderOutput();

		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Edit Details", "Vixen.AccountDetails.RenderAccountDetailsForEditing();");
		echo "</div></div>\n";
		
		echo "</div>\n"; // NarrowContent
		
		// Initialise the AccountDetails object and register the OnAccountDetailsUpdate Listener
		$intAccountId = DBO()->Account->Id->Value;
		$strJavascript = "Vixen.AccountDetails.InitialiseView($intAccountId, '{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
		
		echo "<div class='SmallSeperator'></div>\n";
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
		echo "<div class='NarrowContent'>\n";

		// Render hidden values
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		
		// Render the details of the Account
		DBO()->Account->Id->RenderOutput();

		DBO()->Account->BusinessName->RenderInput();
		DBO()->Account->TradingName->RenderInput();

		DBO()->Account->ABN->RenderInput();
		DBO()->Account->ACN->RenderInput();
		
		// Render the CustomerGroup combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Customer Group :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Account.CustomerGroup' name='Account.CustomerGroup'>\n";
		foreach ($GLOBALS['*arrConstant']['CustomerGroup'] as $intConstant=>$arrCustomerGroupSelection)
		{
			$strSelected = (DBO()->Account->CustomerGroup->Value == $intConstant) ? "selected='selected'" : "";
			echo "		<option value='$intConstant' $strSelected>{$arrCustomerGroupSelection['Description']}</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";		

		// Render the Account Status Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Account Status :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Account.Archived' name='Account.Archived'>\n";
		foreach ($GLOBALS['*arrConstant']['Account'] as $intConstant=>$arrArchivedSelection)
		{
			if (($intConstant == ACCOUNT_DEBT_COLLECTION) || ($intConstant == ACCOUNT_ARCHIVED)|| ($intConstant == ACCOUNT_SUSPENDED))
			{
				// Only users with Admin privileges can mark an account as ACCOUNT_DEBT_COLLECTION or ACCOUNT_ARCHIVED
				if (!$bolUserHasAdminPerm)
				{
					// The user does not have permission to select these options
					continue;
				}
			}

			$strSelected = (DBO()->Account->Archived->Value == $intConstant) ? "selected='selected'" : "";
			echo "         <option value='$intConstant' $strSelected>{$arrArchivedSelection['Description']}</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
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
		
		DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();

		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.AccountDetails.CancelEdit();");
		$this->AjaxSubmit("Commit Changes");
		echo "</div></div>\n";
		
		echo "</div>\n"; // NarrowContent
		
		// Initialise the AccountDetails object
		$intAccountId = DBO()->Account->Id->Value;
		$strJavascript = "Vixen.AccountDetails.InitialiseEdit($intAccountId, '{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
		
		$this->FormEnd();
		
		// If the user doesn't have Admin privileges they cannot select the "Never charge a late payment fee" option
		$strDisableThirdLatePaymentOption = "";
		if (!$bolUserHasAdminPerm)
		{
			// The user doesn't have admin privileges
			$strDisableThirdLatePaymentOption = "document.getElementById('Account.DisableLatePayment_1').disabled = true;\n".
												"document.getElementById('Account.DisableLatePayment_1.Label').style.color='#4C4C4C';\n";
		}
		
		//Resize the textboxes and the comboboxes and disable the "Never charge a late payment fee" radio
		$strWidth = "300px";
		$strJsCode =	"document.getElementById('Account.BusinessName').style.width='$strWidth';\n".
						"document.getElementById('Account.TradingName').style.width='$strWidth';\n".
						"document.getElementById('Account.ABN').style.width='$strWidth';\n".
						"document.getElementById('Account.ACN').style.width='$strWidth';\n".
						"document.getElementById('Account.CustomerGroup').style.width='$strWidth';\n".
						"document.getElementById('Account.Archived').style.width='$strWidth';\n".
						"document.getElementById('Account.Address1').style.width='$strWidth';\n".
						"document.getElementById('Account.Address2').style.width='$strWidth';\n".
						"document.getElementById('Account.Suburb').style.width='$strWidth';\n".
						"document.getElementById('Account.Postcode').style.width='$strWidth';\n".
						"document.getElementById('Account.State').style.width='$strWidth';\n".
						"document.getElementById('Account.BillingMethod').style.width='$strWidth';\n". $strDisableThirdLatePaymentOption;
						
		echo "<script type='text/javascript'>$strJsCode</script>";
		
		echo "<div class='SmallSeperator'></div>\n";
	}

	//------------------------------------------------------------------------//
	// _RenderLedgerDetail (currently only used in invoice and payments)
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
		echo "<div class='NarrowContent'>\n";

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
		
		DBO()->Account->CustomerGroup->RenderCallback("GetConstantDescription", Array("CustomerGroup"), RENDER_OUTPUT);
		
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();
		DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("Account"), RENDER_OUTPUT);
		echo "      </td>\n";
		
		echo "      <td width='35%' valign='top'>\n";
		// Render the properties that can be changed
		DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "</table>\n";

		// If the user doesn't have Admin privileges they cannot select the "Never charge a late payment fee" option
		if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
		{
			// Disable the "Never Charge a late payment fee" radio option
			echo "<script type='text/javascript'>document.getElementById('Account.DisableLatePayment_1').disabled = true;
					document.getElementById('Account.DisableLatePayment_1.Label').style.color='#4C4C4C';</script>";
		}
		
		// Render the submit button
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->AjaxSubmit("Apply Changes");
		echo "</div></div>\n";
		
		echo "</div>\n";  //NarrowContent
		echo "<div class='SmallSeperator'></div>\n";
		
		// Declare the end of the form
		$this->FormEnd();
	}
}

?>
