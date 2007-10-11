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
			case HTML_CONTEXT_FULL_DETAIL:
				$this->_RenderFullDetail();
				break;
			case HTML_CONTEXT_EDIT_DETAIL:
				$this->_RenderEditDetail();
				break;	
			default:
				$this->_RenderFullDetail();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderFullDetail()
	{	
		// Define javascript to execute when the "Edit" button is triggered
		$strEditAccountJsCode =	"var objObjects = {};\n".
								"objObjects.Account = {};\n".
								"objObjects.Account.Id = ". DBO()->Account->Id->Value .";\n".
								"Vixen.Ajax.CallAppTemplate(\"Account\", \"EditDetails\", objObjects);\n";

		echo "<div class='NarrowForm'>\n";
			DBO()->Account->Id->RenderOutput();
			DBO()->Account->Balance->RenderOutput();
			DBO()->Account->BusinessName->RenderOutput();
			DBO()->Account->ABN->RenderOutput();
			DBO()->Account->Address1->RenderOutput();
			
			if (DBO()->Account->Address2->Value != "")
			{
				DBO()->Account->Address2->RenderOutput();
			}
			
			DBO()->Account->Suburb->RenderOutput();
			DBO()->Account->Postcode->RenderOutput();
			DBO()->Account->State->RenderOutput();
			
			DBO()->Account->Country->RenderOutput();
			DBO()->Account->BillingType->RenderCallback("GetConstantDescription", Array("BillingType"), RENDER_OUTPUT);
			DBO()->Account->BillingMethod->RenderCallback("GetConstantDescription", Array("BillingMethod"), RENDER_OUTPUT);
			DBO()->Account->CustomerGroup->RenderCallback("GetConstantDescription", Array("CustomerGroup"), RENDER_OUTPUT);
			
			DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("Account"), RENDER_OUTPUT);
		echo "</div>\n";

		echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Close", "Vixen.Popup.Close(this);");
			$this->Button("Edit", $strEditAccountJsCode);
		echo "</div></div>\n";
	}

	//------------------------------------------------------------------------//
	// _RenderEditDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderEditDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderEditDetail()
	{
		// Define javascript to execute when the "Cancel" button is triggered
		$strCancelJsCode =	"var objObjects = {};\n".
							"objObjects.Objects = {};\n".
							"objObjects.Objects.Account = {};\n".
							"objObjects.Objects.Account.Id = ". DBO()->Account->Id->Value .";\n".
							"Vixen.Popup.ShowAjaxPopup(\"ViewAccountPopupId\", \"large\", null, \"Account\", \"ViewDetails\", objObjects);\n";

		//echo "<div id='AccountDetailDiv'>\n";
		$this->FormStart("EditAccount", "Account", "ValidateAndSaveDetails");
		echo "<div class='NarrowForm'>\n";
		
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		DBO()->Account->Country->RenderHidden();
		DBO()->Account->BillingType->RenderHidden();		
		DBO()->Account->CurrentStatus = DBO()->Account->Archived->Value;
		DBO()->Account->CurrentStatus->RenderHidden();

		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderInput();
		DBO()->Account->TradingName->RenderInput();
		DBO()->Account->ABN->RenderInput();
		DBO()->Account->ACN->RenderInput();
		DBO()->Account->Address1->RenderInput();
		DBO()->Account->Address2->RenderInput();
		DBO()->Account->Suburb->RenderInput();
		DBO()->Account->Postcode->RenderInput();
		
		$arrState = array();
		$arrState[SERVICE_STATE_TYPE_ACT]	= SERVICE_STATE_TYPE_ACT;
		$arrState[SERVICE_STATE_TYPE_NSW]	= SERVICE_STATE_TYPE_NSW;
		$arrState[SERVICE_STATE_TYPE_VIC]	= SERVICE_STATE_TYPE_VIC;
		$arrState[SERVICE_STATE_TYPE_SA]	= SERVICE_STATE_TYPE_SA;
		$arrState[SERVICE_STATE_TYPE_WA]	= SERVICE_STATE_TYPE_WA;
		$arrState[SERVICE_STATE_TYPE_TAS]	= SERVICE_STATE_TYPE_TAS;
		$arrState[SERVICE_STATE_TYPE_NT]	= SERVICE_STATE_TYPE_NT;
		$arrState[SERVICE_STATE_TYPE_QLD]	= SERVICE_STATE_TYPE_QLD;
		
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Account.State' style='width:158px'>\n";
	
		foreach ($arrState as $strKey=>$strStateSelection)
		{
			$strSelected = (DBO()->Account->State->Value == $strKey) ? "selected='selected'" : "";
			echo "		<option value='$strKey' $strSelected>$strStateSelection</option>\n";
		}
		
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		DBO()->Account->Country->RenderOutput();
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Billing Method :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Account.BillingMethod' style='width:158px'>\n";
	
		foreach ($GLOBALS['*arrConstant']['BillingMethod'] as $intConstant=>$arrBillingMethodSelection)
		{
			$strSelected = (DBO()->Account->BillingMethod->Value == $intConstant) ? "selected='selected'" : "";
			echo "		<option value='$intConstant' $strSelected>{$arrBillingMethodSelection['Description']}</option>\n";
		}
		
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Customer Group :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Account.CustomerGroup' style='width:158px'>\n";
	
		foreach ($GLOBALS['*arrConstant']['CustomerGroup'] as $intConstant=>$arrCustomerGroupSelection)
		{
			$strSelected = (DBO()->Account->CustomerGroup->Value == $intConstant) ? "selected='selected'" : "";
			echo "		<option value='$intConstant' $strSelected>{$arrCustomerGroupSelection['Description']}</option>\n";
		}

		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";		

		echo "<div class='Seperator'></div>\n";

		DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();

		// Disable the "Never Charge a late payment fee" radio option
		echo "<script type='text/javascript'>document.getElementById('Account.DisableLatePayment_1').disabled = true;
				document.getElementById('Account.DisableLatePayment_1.Label').style.color='#4C4C4C'</script>";
		
		echo "<div class='Seperator'></div>\n";		

		// Render the Account Status Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Account Status :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Account.Archived' style='width:152px'>\n";
	
		foreach ($GLOBALS['*arrConstant']['Account'] as $intConstant=>$arrArchivedSelection)
		{
			if (($intConstant == ACCOUNT_DEBT_COLLECTION) || ($intConstant == ACCOUNT_ARCHIVED))
			{
				// Only users with Admin privileges can mark an account as ACCOUNT_DEBT_COLLECTION or ACCOUNT_ARCHIVED
				if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
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
		
		echo "</div>\n"; // NarrowForm

		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", $strCancelJsCode);
		$this->AjaxSubmit("Apply Changes");
		echo "</div></div>\n";
		
		$this->FormEnd();
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

		// Use a table to stick the account details and the checkbox and radio buttons next to each other

		echo "<table border='0' cellspacing='0' cellpadding='0'>\n";
		echo "   <tr>\n";
		echo "      <td width='65%' valign='top'>\n";
		// Render the details of the Account
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
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

		// Disable the "Never Charge a late payment fee" radio option
		// Styling that removes the ugly spacing which was unnaturally spacing out the labels
		echo "<script type='text/javascript'>document.getElementById('Account.DisableLatePayment_1').disabled = true;
				document.getElementById('Account.DisableLatePayment_1.Label').style.color='#4C4C4C';</script>";
		
		// Render the submit button
		echo "<div class='Right'>\n";
		//echo "   <input type='submit' class='input-submit' value='Apply Changes' />\n";
		//$this->AjaxSubmit("Apply Changes");
		$this->AjaxSubmit("Apply Changes");
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<div class='SmallSeperator'></div>\n";
		echo "</div>\n";
		echo "<div class='SmallSeperator'></div>\n";
		
		// Declare the end of the form
		$this->FormEnd();
	}
}

?>
