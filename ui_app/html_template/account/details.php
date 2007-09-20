<?php
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
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		$this->LoadJavascript("rate_add");
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
		// put in ajax form to enable switching context 
		echo "<div id='AccountDetailDiv'>\n";
		echo "<h2 class='Account'>Account Full Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		$this->FormStart("EditAccount", "Account", "Edit");
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Account->ABN->RenderOutput();
		DBO()->Account->Address1->RenderOutput();
		DBO()->Account->Suburb->RenderOutput();
		DBO()->Account->Postcode->RenderOutput();
		DBO()->Account->State->RenderOutput();
		
		DBO()->Account->Country->RenderOutput();
		DBO()->Account->BillingType->RenderCallback("GetConstantDescription", Array("BillingType"), RENDER_OUTPUT);
		DBO()->Account->BillingMethod->RenderCallback("GetConstantDescription", Array("BillingMethod"), RENDER_OUTPUT);
		DBO()->Account->CustomerGroup->RenderCallback("GetConstantDescription", Array("CustomerGroup"), RENDER_OUTPUT);
		DBO()->Account->Archived->RenderCallback("GetConstantDescription", Array("Account"), RENDER_OUTPUT);
		
		//$this->Button("Commit", "Vixen.Popup.Confirm(\"Are you sure you want to commit this Rate?<br />The Rate cannot be edited once it is committed\", Vixen.RateAdd.Commit)");
			
		//$this->Button("Cancel", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");	
		//echo $this->_objAjax->strId;
		$this->Button("Edit", "Vixen.RateAdd.Edit(".DBO()->Account->Id->Value.")");
		$this->FormEnd();
		echo "--->>>".DBO()->Account->Id->Value;
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
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
		
		//echo "->>>>>>>>>>>".DBO();
		echo "<h2 class='Account'>Account Edit Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		echo"<table border='0' cellpadding='3' cellspacing='0'>\n";
				
				foreach (DBO()->Account AS $strProperty=>$objValue)
				{	
					echo "<tr>\n";
					$objValue->RenderOutput();
					echo "</tr>\n";
				}
				
		echo "</table>\n";
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		

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
		echo "      </td>\n";
		
		echo "      <td width='35%' valign='top'>\n";
		// Render the properties that can be changed
		DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "</table>\n";

		// Disable the "Never Charge a late payment fee" radio option
		echo "<script type='text/javascript'>document.getElementById('Account.DisableLatePayment_1').disabled = true;</script>";
		
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
