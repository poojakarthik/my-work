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
 * contains all ApplicationTemplate extended classes relating to Invoice Run Events functionality
 *
 * contains all ApplicationTemplate extended classes relating to Invoice Run Events functionality
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
// AppTemplateIncoiceRunEvents
//----------------------------------------------------------------------------//
/**
 * AppTemplateIncoiceRunEvents
 *
 * The AppTemplateIncoiceRunEvents class
 *
 * The AppTemplateIncoiceRunEvents class.  This incorporates all logic for all pages
 * relating to Invoice Run Events
 *
 *
 * @package	ui_app
 * @class	AppTemplateIncoiceRunEvents
 * @extends	ApplicationTemplate
 */
class AppTemplateInvoiceRunEvents extends ApplicationTemplate
{
	public function Manage()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Invoice Run Events");

		// Ensure that the event records have been created for the latest invoice run
		EnsureLatestInvoiceRunEventsAreDefined();

		$this->_manage();

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('invoice_run_event');

		return TRUE;
	}

	public function RenderHtmlTemplateInvoiceRunEventsManage()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		$this->_manage();

		// Render the CustomerGroupDetails HtmlTemplate for Viewing
		Ajax()->RenderHtmlTemplate("InvoicerunEvent", HTML_CONTEXT_DEFAULT, DBO()->Container->Id->Value);

		return TRUE;
	}

	public function _manage()
	{
		// Find out which invoice runs are currently incomplete (not all events have been actioned)
		$arrColumns = array(
			'Id' => 'r.id',
		);
		$strTables = "automatic_invoice_run_event e JOIN InvoiceRun r on e.invoice_run_id = r.Id AND e.actioned_datetime IS NULL";
		$strGroupBy = 'e.invoice_run_id';
		$qryQuery = new StatementSelect($strTables, $arrColumns, "invoice_run_type_id = ".INVOICE_RUN_TYPE_LIVE, "", "", $strGroupBy);
		$ids = array();
		if ($qryQuery->Execute())
		{
			while($id = $qryQuery->Fetch())
			{
				$ids[] = $id['Id'];
			}
		}

		if (count($ids))
		{
			$strWhere = "Id IN (" . implode(',', $ids) . ")";
			DBL()->InvoiceRun->SetColumns(array('Id', 'InvoiceRun', 'customer_group_id', 'BillingDate'));
			DBL()->InvoiceRun->OrderBy("BillingDate DESC");
			DBL()->InvoiceRun->Load($strWhere, NULL);
		}
	}

	//------------------------------------------------------------------------//
	// RenderHtmlTemplateInvoiceRunEventsView
	//------------------------------------------------------------------------//
	/**
	 * RenderHtmlTemplateInvoiceRunEventsView()
	 *
	 * Renders the IncoiceRunEvents Html Template in the specified context (View)
	 * 
	 * Renders the IncoiceRunEvents Html Template in the specified context (View)
	 * It expects	DBO()->InvoiceRun->Id 	InvoiceRun Id 
	 *
	 * @return		void
	 * @method
	 *
	 */
	public function RenderHtmlTemplateInvoiceRunEventsView()
	{
		return $this->RenderHtmlTemplateInvoiceRunEvents(HTML_CONTEXT_VIEW);
	}

	//------------------------------------------------------------------------//
	// RenderHtmlTemplateInvoiceRunEventsEdit
	//------------------------------------------------------------------------//
	/**
	 * RenderHtmlTemplateInvoiceRunEventsEdit()
	 *
	 * Renders the IncoiceRunEvents Html Template in the specified context (Edit)
	 * 
	 * Renders the IncoiceRunEvents Html Template in the specified context (Edit)
	 * It expects	DBO()->InvoiceRun->Id 	InvoiceRun Id 
	 *
	 * @return		void
	 * @method
	 *
	 */
	function RenderHtmlTemplateInvoiceRunEventsEdit()
	{
		return $this->RenderHtmlTemplateInvoiceRunEvents(HTML_CONTEXT_EDIT);
	}

	//------------------------------------------------------------------------//
	// RenderHtmlTemplateInvoiceRunEvents
	//------------------------------------------------------------------------//
	/**
	 * RenderHtmlTemplateInvoiceRunEvents()
	 *
	 * Renders the IncoiceRunEvents Html Template in the specified context (Edit)
	 * 
	 * Renders the IncoiceRunEvents Html Template in the specified context (Edit)
	 * It expects	DBO()->InvoiceRun->Id 	InvoiceRun Id 
	 *
	 * @return		void
	 * @method
	 *
	 */
	function RenderHtmlTemplateInvoiceRunEvents($context)
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ManageInvoiceRunEvents();
		BreadCrumb()->SetCurrentPage("Invoice Run Events");

		// Load the events for the invoice run
		DBO()->InvoiceRun->Load();

		DBL()->automatic_invoice_action_config->SetColumns(array('id', 'days_from_invoice', 'automatic_invoice_action_id'));
		DBL()->automatic_invoice_action_config->OrderBy("days_from_invoice, automatic_invoice_action_id");
		DBL()->automatic_invoice_action_config->Load('can_schedule = 1 AND customer_group_id ' . (DBO()->InvoiceRun->customer_group_id->Value ? (' = ' . DBO()->InvoiceRun->customer_group_id->Value) : ' IS NULL'));

		$strWhere = "invoice_run_id = " . DBO()->InvoiceRun->Id->Value;
		DBL()->automatic_invoice_run_event->SetColumns(array('id', 'scheduled_datetime', 'actioned_datetime', 'automatic_invoice_action_id'));
		DBL()->automatic_invoice_run_event->Load($strWhere);

		// All required data has been retrieved from the database so now load the page template
		Ajax()->RenderHtmlTemplate("InvoicerunEvent", $context, DBO()->Container->Id->Value);

		return TRUE;
	}


	//------------------------------------------------------------------------//
	// SaveDetails
	//------------------------------------------------------------------------//
	/**
	 * SaveDetails()
	 *
	 * Handles the logic of validating and saving the details of IncoiceRunEvents
	 * 
	 * Handles the logic of validating and saving the details of IncoiceRunEvents
	 * This works with the HtmlTemplateIncoiceRunEventsDisplay object, when rendered in Edit mode (HTML_CONTEXT_EDIT)
	 * It fires the OnIncoiceRunEventsUpdate Event if the IncoiceRunEvents are successfully modified
	 *
	 * @return	void
	 * @method	SaveDetails
	 *
	 */
	function SaveDetails()
	{
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Load the automatic invoice run events for the invoice run
		$strWhere = "invoice_run_id = <INVOICE_RUN_ID>";
		$arrWhere = array('INVOICE_RUN_ID' => DBO()->InvoiceRun->Id->Value);
		DBL()->current_automatic_invoice_run_event->SetTable('automatic_invoice_run_event');
		DBL()->current_automatic_invoice_run_event->Where->Set($strWhere, $arrWhere);
		DBL()->current_automatic_invoice_run_event->Load();

		TransactionStart();

		foreach (DBL()->current_automatic_invoice_run_event as $dboAutomaticInvoiceRunEvent)
		{
			// Get the submitted version
			$name = 'automatic_invoice_run_event_' . $dboAutomaticInvoiceRunEvent->id->Value;
			$submitted = DBO()->{$name};

			// Check to see if the value is set
			if ($dboAutomaticInvoiceRunEvent->id->Value == $submitted->Id->Value)
			{
				// Parse the submitted datetime (expected format h:i:s d/m/Y)
				if (!$submitted->scheduled_datetime || !$submitted->scheduled_datetime->Value)
				{
					$scheduledDatetime = NULL;
				}
				else
				{
					$scheduledDatetime = trim($submitted->scheduled_datetime->Value);
					$parts = array();
					$gap = "(?:[^0-9]+)";
					$dig0 = "([0-9]{0,2})";
					$dig2 = "([0-9]{1,2})";
					$dig4 = "([0-9]{4,4})";
					$regExp = "/^{$dig2}{$gap}{$dig2}{$gap}(?:{$dig2}{$gap}|){$dig2}{$gap}{$dig2}{$gap}{$dig4}$/";
					if (!preg_match($regExp, $scheduledDatetime, $parts) || !($mktime = mktime(intval($parts[1]), intval($parts[2]), intval($parts[3]), intval($parts[5]), intval($parts[4]), intval($parts[6]))))
					{
						Ajax()->AddCommand("Alert", "The expected date/time format is hour:minute:seconds day/month/year (e.g. " . date("H:i:s d/m/Y") . "). You entered $scheduledDatetime");
						Ajax()->AddCommand("SetFocus", "automatic_invoice_run_event_" . $submitted->Id->Value . ".scheduled_datetime");
						return TRUE;
					}
					$scheduledDatetime = date('Y-m-d H:i:s', $mktime);
				}

				// Get the previous scheduled datetime
				if (!$dboAutomaticInvoiceRunEvent->scheduled_datetime || !$dboAutomaticInvoiceRunEvent->scheduled_datetime->Value)
				{
					$previousDatetime = NULL;
				}
				else
				{
					$previousDatetime = $dboAutomaticInvoiceRunEvent->scheduled_datetime->Value;
				}

				// Check to see if the value has been changed
				if ($scheduledDatetime != $previousDatetime)
				{
					// Update the value and change it
					$dboAutomaticInvoiceRunEvent->SetColumns(array('scheduled_datetime', 'update_user_id', 'update_datetime'));
					$dboAutomaticInvoiceRunEvent->Id = $dboAutomaticInvoiceRunEvent->id->Value;
					$dboAutomaticInvoiceRunEvent->scheduled_datetime = $scheduledDatetime;
					$dboAutomaticInvoiceRunEvent->update_user_id = AuthenticatedUser()->GetUserId();
					$dboAutomaticInvoiceRunEvent->update_datetime = date('Y-m-d H:i:s');

					if (!$dboAutomaticInvoiceRunEvent->Save())
					{
						TransactionRollback();
						Ajax()->AddCommand("Alert", "ERROR: Saving changes to the scheduled times failed.");
						return TRUE;
					}
				}
			}
		}

		// Fire the OnCustomerGroupDetailsUpdate Event
		TransactionCommit();
		Ajax()->FireEvent('OnInvoiceRunEventsUpdate', array());
		return TRUE;
	}

}
?>
