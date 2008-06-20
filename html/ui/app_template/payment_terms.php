<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// payment_terms.php
//----------------------------------------------------------------------------//
/**
 * adjustment
 *
 * contains all ApplicationTemplate extended classes relating to Payment Terms functionality
 *
 * contains all ApplicationTemplate extended classes relating to Payment Terms functionality
 *
 * @file		adjustment.php
 * @language	PHP
 * @package		framework
 * @author		Hadrian Oliver
 * @version		0.1
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplatePaymentTerms
//----------------------------------------------------------------------------//
/**
 * AppTemplatePaymentTerms
 *
 * The AppTemplatePaymentTerms class
 *
 * The AppTemplatePaymentTerms class.  This incorporates all logic for all pages
 * relating to payment temrs
 *
 *
 * @package	ui_app
 * @class	AppTemplatePaymentTerms
 * @extends	ApplicationTemplate
 */
class AppTemplatePaymentTerms extends ApplicationTemplate
{
	public function Manage()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->SetCurrentPage("Payment Terms");

		$qryQuery = new StatementSelect('payment_terms', array('id' => 'MAX(id)'));
		$id = 0;
		if ($qryQuery->Execute())
		{
			$id = $qryQuery->Fetch();
			$id = $id['id'];
		}
		DBO()->payment_terms->Load($id);
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('payment_terms_display');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// RenderHtmlTemplateCustomerGroupDetails
	//------------------------------------------------------------------------//
	/**
	 * RenderHtmlTemplateCustomerGroupDetails()
	 *
	 * Renders the CustomerGroupDetails Html Template in the specified context (View or Edit)
	 * 
	 * Renders the CustomerGroupDetails Html Template in the specified context (View or Edit)
	 * It expects	DBO()->CustomerGroup->Id 	CustomerGroup Id 
	 *				DBO()->Container->Id		id of the container div in which to place the Rendered HtmlTemplate
	 *				DBO()->Context->View		true if rending in viewing context (defaults to this)
	 *				DBO()->Context->Edit		true if rending in edit context (View takes precedence)
	 *
	 * @return		void
	 * @method
	 *
	 */
	function RenderHtmlTemplatePaymentTermsDisplay()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Load the CustomerGroup
		$qryQuery = new StatementSelect('payment_terms', array('id' => 'MAX(id)'));
		$id = 0;
		if ($qryQuery->Execute())
		{
			$id = $qryQuery->Fetch();
			$id = $id['id'];
		}
		DBO()->payment_terms->Load($id);

		// Work out which context to render the HtmlTemplate in
		$intContext = HTML_CONTEXT_VIEW;
		if (DBO()->Context->View->Value)
		{
			$intContext = HTML_CONTEXT_VIEW;
		}
		elseif (DBO()->Context->Edit->Value)
		{
			$intContext = HTML_CONTEXT_EDIT;
		}

		// Render the CustomerGroupDetails HtmlTemplate for Viewing
		Ajax()->RenderHtmlTemplate("PaymentTermsDisplay", $intContext, DBO()->Container->Id->Value);

		return TRUE;
	}


	//------------------------------------------------------------------------//
	// SaveDetails
	//------------------------------------------------------------------------//
	/**
	 * SaveDetails()
	 *
	 * Handles the logic of validating and saving the details of PaymentTerms
	 * 
	 * Handles the logic of validating and saving the details of PaymentTerms
	 * This works with the HtmlTemplatePaymentTermsDisplay object, when rendered in Edit mode (HTML_CONTEXT_EDIT)
	 * It fires the OnPaymentTermsUpdate Event if the PaymentTerms are successfully modified
	 *
	 * @return	void
	 * @method	SaveDetails
	 *
	 */
	function SaveDetails()
	{
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Validate the CustomerGroup
		if (DBO()->payment_terms->IsInvalid())
		{
			// At least one Field is invalid
			Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
			Ajax()->RenderHtmlTemplate("PaymentTermsDisplay", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return TRUE;
		}

		// Load the current CustomerGroup
		$qryQuery = new StatementSelect('payment_terms', array('id' => 'MAX(id)'));
		$id = 0;
		if ($qryQuery->Execute())
		{
			$id = $qryQuery->Fetch();
			$id = $id['id'];
		}
		DBO()->current_payment_terms->SetTable('payment_terms');
		DBO()->current_payment_terms->Load($id);
		
		// Only save the new record if the table has changed
		if (DBO()->payment_terms->invoice_day->Value != DBO()->current_payment_terms->invoice_day->Value ||
			DBO()->payment_terms->payment_terms->Value != DBO()->current_payment_terms->payment_terms->Value ||
			DBO()->payment_terms->overdue_notice_days->Value != DBO()->current_payment_terms->overdue_notice_days->Value ||
			DBO()->payment_terms->suspension_notice_days->Value != DBO()->current_payment_terms->suspension_notice_days->Value ||
			DBO()->payment_terms->final_demand_notice_days->Value != DBO()->current_payment_terms->final_demand_notice_days->Value ||
			DBO()->payment_terms->minimum_balance_to_pursue->Value != DBO()->current_payment_terms->minimum_balance_to_pursue->Value)
		{
			// Copy over the values that we wish to save
			DBO()->current_payment_terms->invoice_day = DBO()->payment_terms->invoice_day->Value;
			DBO()->current_payment_terms->payment_terms = DBO()->payment_terms->payment_terms->Value;
			DBO()->current_payment_terms->overdue_notice_days = DBO()->payment_terms->overdue_notice_days->Value;
			DBO()->current_payment_terms->suspension_notice_days = DBO()->payment_terms->suspension_notice_days->Value;
			DBO()->current_payment_terms->final_demand_notice_days = DBO()->payment_terms->final_demand_notice_days->Value;
			DBO()->current_payment_terms->minimum_balance_to_pursue = DBO()->payment_terms->minimum_balance_to_pursue->Value;
			// Blank the Id to force a new record to be created
			DBO()->current_payment_terms->Id = 0;
			// Set the employee and date for auditing purposes
			DBO()->current_payment_terms->employee = AuthenticatedUser()->GetUserId();
			DBO()->current_payment_terms->created = date('Y-m-d h:i:s');
	
			// The payment terms are valid.  Save them
			if (!DBO()->current_payment_terms->Save())
			{
				// The CustomerGroup could not be saved for some unforseen reason
				Ajax()->AddCommand("Alert", "ERROR: Saving changes to the Payment Terms failed, unexpectedly");
				return TRUE;
			}
		}
		// Fire the OnCustomerGroupDetailsUpdate Event
		Ajax()->FireEvent('OnPaymentTermsUpdate', array());
		return TRUE;
	}

}
?>
