<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// menu_items.php
//----------------------------------------------------------------------------//
/**
 * menu_items
 *
 * Defines the MenuItems class, which stores all menu items that can be used in the application
 *
 * Defines the MenuItems class, which stores all menu items that can be used in the application
 *
 * @file		menu_items.php
 * @language	PHP
 * @package		ui_app
 * @author		Jared
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// MenuItems
//----------------------------------------------------------------------------//
/**
 * MenuItems
 *
 * Defines the resultant Href for each paricular item that can be included in a menu
 *
 * Defines the resultant Href for each paricular item that can be included in a menu.
 * Each type of menu item (a command in the context menu) should have a method
 * defined here which returns the Href that should be used when the menu item is 
 * clicked.  Alternatively the menu item can be handled by the __call function.
 * You will notice that the menu item "ViewAccount" has been handled both ways as
 * an example of how they work.
 * These menu items can also be expressed as BreadCrumbMenu items, so long as they 
 * set $strLabel to the label that will be displayed for the BreadCrumb.
 *
 * @prefix	mit
 *
 * @package	ui_app
 * @class	MenuItems
 */
class MenuItems
{
	//------------------------------------------------------------------------//
	// strLabel
	//------------------------------------------------------------------------//
	/**
	 * strLabel
	 *
	 * Stores the accompanying label if the last menu item processed can be used as a breadcrumb
	 *
	 * Stores the accompanying label if the last menu item processed can be used as a breadcrumb
	 *
	 * @type		string
	 *
	 * @property
	 */
	public $strLabel;
	
	//------------------------------------------------------------------------//
	// ViewAccount
	//------------------------------------------------------------------------//
	/**
	 * ViewAccount()
	 *
	 * Compiles the Href to be executed when the ViewAccount menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewAccount menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account to view
	 *
	 * @return	string				Href to be executed when the ViewAccount menu item is clicked
	 *
	 * @method
	 */
	function ViewAccount($intId)
	{
		$this->strLabel	= "acc: $intId";
		return "account_view.php?Id=$intId";
	}

	//------------------------------------------------------------------------//
	// EmployeeConsole
	//------------------------------------------------------------------------//
	/**
	 * EmployeeConsole()
	 *
	 * Compiles the Href to be executed when the EmployeeConsole menu item is clicked
	 *
	 * Compiles the Href to be executed when the EmployeeConsolet menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string				Href to be executed when the EmployeeConsole menu item is clicked
	 *
	 * @method
	 */
	function EmployeeConsole()
	{
		$this->strLabel	= "console";
		return "console.php";
	}

	

	//------------------------------------------------------------------------//
	// EditContact
	//------------------------------------------------------------------------//
	/**
	 * EditContact()
	 *
	 * Compiles the Href to be executed when the EditContact menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditContact menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the contact to edit
	 *
	 * @return	string				Href to be executed when the EditContact menu item is clicked
	 *
	 * @method
	 */
	function EditContact($intId)
	{
		$this->strLabel	= "contact: $intId";
		return "contact_edit.php?Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// EditService
	//------------------------------------------------------------------------//
	/**
	 * EditService()
	 *
	 * Compiles the Href to be executed when the EditService menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditService menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the service to view
	 *
	 * @return	string				Href to be executed when the EditService menu item is clicked
	 *
	 * @method
	 */
	function EditService($intId)
	{
		return "vixen.php/Service/Edit/?Service.Id=$intId";
	}	
	
	//------------------------------------------------------------------------//
	// ViewContact
	//------------------------------------------------------------------------//
	/**
	 * ViewContact()
	 *
	 * Compiles the Href to be executed when the ViewContact menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewContact menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the contact to view
	 *
	 * @return	string				Href to be executed when the ViewContact menu item is clicked
	 *
	 * @method
	 */
	function ViewContact($intId)
	{
		$this->strLabel	= "contact: $intId";
		return "vixen.php/Contact/View/?Contact.Id=$intId";
	}
	
	
	//------------------------------------------------------------------------//
	// ViewService
	//------------------------------------------------------------------------//
	/**
	 * ViewService()
	 *
	 * Compiles the Href to be executed when the ViewService menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewService menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the service to view
	 * @param	int		$strFNN		[optional] FNN of the service to view
	 *
	 * @return	string				Href to be executed when the ViewService menu item is clicked
	 *
	 * @method
	 */
	function ViewService($intId, $strFNN=NULL)
	{
		$this->strLabel	= "service : $strFNN";
		return "vixen.php/Service/View/?Service.Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// InvoicesAndPayments
	//------------------------------------------------------------------------//
	/**
	 * InvoicesAndPayments()
	 *
	 * Compiles the Href to be executed when the InvoicesAndPayments menu item is clicked
	 *
	 * Compiles the Href to be executed when the InvoicesAndPayments menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account to view
	 *
	 * @return	string				Href to be executed when the InvoicesAndPayments menu item is clicked
	 *
	 * @method
	 */
	function InvoicesAndPayments($intId)
	{
		$this->strLabel	= "acc: $intId";
		return "vixen.php/Account/InvoicesAndPayments/?Account.Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// EditEmployee
	//------------------------------------------------------------------------//
	/**
	 * EditEmployee()
	 *
	 * Compiles the Href to be executed when the EditEmployee menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditEmployee menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the Employee to view
	 *
	 * @return	string				Href to be executed when the EditEmployee menu item is clicked
	 *
	 * @method
	 */
	function EditEmployee($intId)
	{
		
		$this->strLabel	= "edit emp: $intId";
		
		// Setup data to send

		//$arrData['HtmlMode'] = TRUE;
		//$arrData['Application'] = "Employee.Edit";
		$arrData['Objects']['Employee']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"Employee{$intId}EditPopup\", \"medium\", \"Employee\", \"Edit\", $strJsonCode)";

	}

	//------------------------------------------------------------------------//
	// AddAssociatedAccount
	//------------------------------------------------------------------------//
	/**
	 * AddAssociatedAccount()
	 *
	 * Compiles the Href to be executed when the AddAssociatedAccount menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddAssociatedAccount menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intInvoice		account number of the calling account
	 *
	 * @return	string					Href to be executed when the AddAssociatedAccount menu item is clicked
	 *
	 * @method
	 */
	function AddAssociatedAccount($intAccount)
	{
		return "account_add.php?Associated=$intAccount";
	}

	//------------------------------------------------------------------------//
	// ViewInvoicePdf
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoicePdf()
	 *
	 * Compiles the Href to be executed when the View Invoice Pdf menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoice Pdf menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intInvoice		invoice number of the invoice to view
	 *
	 * @return	string					Href to be executed when the View Invoice Pdf menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoicePdf($intAccount, $intMonth, $intYear)
	{
		$this->strLabel = "pdf acct: $intAccount, $intMonth/$intYear";
		return "invoice_pdf.php?Account=$intAccount&Year=$intYear&Month=$intMonth";
	}
	
	//------------------------------------------------------------------------//
	// ViewInvoice
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoice()
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intInvoice		invoice number of the invoice to view
	 *
	 * @return	string					Href to be executed when the View Invoice menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoice($intInvoice)
	{
		$this->strLabel = "inv: $intInvoice";
		return "invoice_view.php?Invoice=$intInvoice";
		
	}
	
	//------------------------------------------------------------------------//
	// ViewAccountNotes
	//------------------------------------------------------------------------//
	/**
	 * ViewAccountNotes()
	 *
	 * Compiles the javascript to be executed when the ViewAccountNotes menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ViewAccountNotes menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account associated with the notes to view
	 *
	 * @return	string				action to be executed when the ViewAccountNotes menu item is clicked
	 *
	 * @method
	 */
	function ViewAccountNotes($intId)
	{
		$this->strLabel	= "view account notes";
		
		// Setup data to send
		$arrData['Objects']['Note']['NoteGroupId'] = $intId;
		$arrData['Objects']['Note']['NoteClass'] = NOTE_CLASS_ACCOUNT_NOTES;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewNotesPopupId\", \"medium\", \"Note\", \"View\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// ViewContactNotes
	//------------------------------------------------------------------------//
	/**
	 * ViewContactNotes()
	 *
	 * Compiles the javascript to be executed when the ViewContactNotes menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ViewContactNotes menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account associated with the notes to view
	 *
	 * @return	string				action to be executed when the ViewContactNotes menu item is clicked
	 *
	 * @method
	 */
	function ViewContactNotes($intId)
	{
		$this->strLabel	= "view contact notes";
		
		// Setup data to send
		$arrData['Objects']['Note']['NoteGroupId'] = $intId;
		$arrData['Objects']['Note']['NoteClass'] = NOTE_CLASS_CONTACT_NOTES;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewNotesPopupId\", \"medium\", \"Note\", \"View\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddContactNote
	//------------------------------------------------------------------------//
	/**
	 * AddContactNote()
	 *
	 * Compiles the javascript to be executed when the AddContactNote menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddContactNote menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account associated with the note to add
	 *
	 * @return	string				action to be executed when the AddContactNotes menu item is clicked
	 *
	 * @method
	 */
	function AddContactNote($intId)
	{
		$this->strLabel	= "add note";
		
		// Setup data to send
		$arrData['Objects']['Contact']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddNotePopupId\", \"medium\", \"Note\", \"AddContact\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddAccountNote
	//------------------------------------------------------------------------//
	/**
	 * AddAccountNote()
	 *
	 * Compiles the javascript to be executed when the AddAccountNote menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddAccountNote menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account associated with the note to add
	 *
	 * @return	string				action to be executed when the AddAccountNotes menu item is clicked
	 *
	 * @method
	 */
	function AddAccountNote($intId)
	{
		$this->strLabel	= "add account note";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddNotePopupId\", \"medium\", \"Note\", \"AddAccount\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// EmailPDFInvoice
	//------------------------------------------------------------------------//
	/**
	 * EmailPDFInvoice()
	 *
	 * Compiles the javascript to be executed when the EmailPDFInvoice menu item is clicked
	 *
	 * Compiles the javascript to be executed when the EmailPDFInvoice menu item is clicked
	 * 
	 * @param	int		$intId		id of the account associated with the invoice to email
	 * @param	int		$intYear	year part of the date of the invoice to email
	 * @param	int		$intMonth	month part of the date of the invoice to email
	 *
	 * @return	string				action to be executed when the AddNotes menu item is clicked
	 *
	 * @method
	 */
	function EmailPDFInvoice($intId, $intYear, $intMonth)
	{
		$this->strLabel	= "email pdf invoice";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		$arrData['Objects']['Invoice']['Year'] = $intYear;
		$arrData['Objects']['Invoice']['Month'] = $intMonth;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"EmailPDFInvoicePopupId\", \"medium\", \"Invoice\", \"EmailPDFInvoice\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// RatesList
	//------------------------------------------------------------------------//
	/**
	 * RatesList()
	 *
	 * Compiles the javascript to be executed when the RatesList menu item is clicked
	 *
	 * Compiles the javascript to be executed when the RatesList menu item is clicked
	 * 
	 * @param	int		$intId		id of the account associated with the invoice to email
	 * @param	int		$intYear	year part of the date of the invoice to email
	 * @param	int		$intMonth	month part of the date of the invoice to email
	 *
	 * @return	string				action to be executed when the AddNotes menu item is clicked
	 *
	 * @method
	 */
	function RatesList($intId)
	{
		$this->strLabel	= "rates list";
		
		// Setup data to send
		$arrData['Objects']['RatePlan']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RatesListPopupId\", \"large\", \"Plan\", \"RateList\", $strJsonCode)";
	}
	

	//------------------------------------------------------------------------//
	// AddAdjustment
	//------------------------------------------------------------------------//
	/**
	 * AddAdjustment()
	 *
	 * Compiles the javascript to be executed when the AddAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account that the Adjustment will be added to
	 *
	 * @return	string				action to be executed when the AddAdjustment menu item is clicked
	 *
	 * @method
	 */
	function AddAdjustment($intId)
	{
		$this->strLabel	= "add adjustment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddAdjustmentPopupId\", \"medium\", \"Adjustment\", \"Add\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// AddRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * AddRecurringAdjustment()
	 *
	 * Compiles the javascript to be executed when the AddRecurringAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddRecurringAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account that the Adjustment will be added to
	 *
	 * @return	string				action to be executed when the AddRecurringAdjustment menu item is clicked
	 *
	 * @method
	 */
	function AddRecurringAdjustment($intId)
	{
		$this->strLabel	= "add recurring adjustment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddRecurringAdjustmentPopupId\", \"large\", \"Adjustment\", \"AddRecurring\", $strJsonCode)";
	}
	
	
	//------------------------------------------------------------------------//
	// MakePayment
	//------------------------------------------------------------------------//
	/**
	 * MakePayment()
	 *
	 * Compiles the javascript to be executed when the MakePayment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the MakePayment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account that is currently being viewed
	 *
	 * @return	string				action to be executed when the MakePayment menu item is clicked
	 *
	 * @method
	 */
	function MakePayment($intId)
	{
		$this->strLabel	= "make payment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"MakePaymentPopupId\", \"large\", \"Payment\", \"Add\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeletePayment
	//------------------------------------------------------------------------//
	/**
	 * DeletePayment()
	 *
	 * Compiles the javascript to be executed when the DeletePayment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeletePayment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intPaymentId		id of the payment to delete
	 *
	 * @return	string						action to be executed when the DeletePayment menu item is clicked
	 *
	 * @method
	 */
	function DeletePayment($intPaymentId)
	{
		$this->strLabel	= "delete payment: $intPaymentId";
		
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "Payment";
		$arrData['Objects']['Payment']['Id'] = $intPaymentId;
		
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeletePaymentPopupId\", \"medium\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeleteAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteAdjustment()
	 *
	 * Compiles the javascript to be executed when the DeleteAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeleteAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAdjustmentId		id of the adjustment to delete
	 *
	 * @return	string							action to be executed when the DeleteAdjustment menu item is clicked
	 *
	 * @method
	 */
	function DeleteAdjustment($intAdjustmentId)
	{
		$this->strLabel	= "delete adjustment: $intAdjustmentId";
				
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "Adjustment";
		$arrData['Objects']['Charge']['Id'] = $intAdjustmentId;
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteAdjustmentPopupId\", \"medium\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeleteRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteRecurringAdjustment()
	 *
	 * Compiles the javascript to be executed when the DeleteRecurringAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeleteRecurringAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intRecurringAdjustmentId		id of the recurring adjustment to delete
	 *
	 * @return	string									action to be executed when the DeleteRecurringAdjustment menu item is clicked
	 *
	 * @method
	 */
	function DeleteRecurringAdjustment($intRecurringAdjustmentId)
	{
		$this->strLabel	= "delete recurring adjustment: $intRecurringAdjustmentId";
		
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "RecurringAdjustment";
		$arrData['Objects']['RecurringCharge']['Id'] = $intRecurringAdjustmentId;
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteRecurringAdjustmentPopupId\", \"medium\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// BreadCrumb
	//------------------------------------------------------------------------//
	/**
	 * BreadCrumb()
	 *
	 * Compiles the passed menu item as a breadcrumb to be used in the breadcrumb menu
	 *
	 * Compiles the passed menu item as a breadcrumb to be used in the breadcrumb menu
	 * Any menu item can be used as a breadcrumb so long as it defines a value for 
	 * the public data attribute $strLabel
	 *
	 * @param	string	$strName	Name of the menu item to be used as a breadcrumb
	 *								ie "ViewAccount" or "View_Account"
	 * @param	array	$arrParams	Parameters to be passed to the MenuItem method associated
	 *								with $strName
	 *
	 * @return	array				['Href'] 	= Href to be executed when the breadcrumb is clicked
	 *								['Label'] 	= breadcrumb's label
	 *
	 * @method
	 */
	function BreadCrumb($strName, $arrParams)
	{
		$this->strLabel = NULL;
		$arrReturn = Array();
		$strName = str_replace('_', '', $strName);
		
		// call the menu item method specific to $strName
		$arrReturn['Href'] = call_user_func_array(array($this, $strName), $arrParams);
		
		if (!$this->strLabel)
		{
			// the menu item cannot be used as a breadcrumb
			return FALSE;
		}
		$arrReturn['Label'] = $this->strLabel;
		
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Handles all menu items that have not had a specific method defined in this class
	 *
	 * Handles all menu items that have not had a specific method defined in this class
	 * 
	 * @param	string		$strName		name of the menu item
	 * @param	array		$arrParams		any parameters defined for the menu item
	 *
	 * @return	string						the Href to be executed when menu item is clicked
	 *
	 * @method
	 */
	function __call($strName, $arrParams)
	{
		switch ($strName)
		{
			case "Logout":
				return "logout.php";
				break;
			case "AdminConsole":
				return "console.php";
				break;
			default;
				return "[insert generic HREF here]";
				
				break;
		}
	}
}
