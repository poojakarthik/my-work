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
 * @package		web_app
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
 * @package	web_app
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
	// MainPage
	//------------------------------------------------------------------------//
	/**
	 * MainPage()
	 *
	 * Compiles the url to be executed when the MainPage menu item is clicked
	 *
	 * Compiles the url to be executed when the MainPage menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string						action to be executed when the MainPage menu item is clicked
	 *
	 * @method
	 */
	function MainPage()
	{
		$this->strLabel	= "Main Page";
		
		return "index.html";
	}
	
	
	//------------------------------------------------------------------------//
	// GetLabel
	//------------------------------------------------------------------------//
	/**
	 * GetLabel()
	 *
	 * Returns the label for this menu item or NULL if not set
	 *
	 * Returns the label for this menu item or NULL if not set
	 * 
	 * @return	string	the label for this menu item or NULL if not set
	 *
	 * @method
	 */
	function GetLabel()
	{
		if (isset($this->strLabel))
		{
			return $this->strLabel;
		}
		return NULL;
	}
	
	//------------------------------------------------------------------------//
	// GetBaseURL()
	//------------------------------------------------------------------------//
	/**
	 * GetBaseURL()
	 *
	 * Returns the base URL for the application 
	 * (everything before the flex.php in the request URI)
	 *
	 * Returns the base URL for the application 
	 * (everything before the flex.php in the request URI)
	 * 
	 * @return	string		The base URL for links in the application
	 *
	 * @method
	 */
	function GetBaseURL()
	{
		$strUrl = preg_replace("/flex.php\/.*$/", "", $_SERVER['REQUEST_URI']);
		return preg_replace("/reflex.php\/.*$/", "", $strUrl);
	}


	//------------------------------------------------------------------------//
	// Console DEPRICATED
	//------------------------------------------------------------------------//
	/**
	 * Console()
	 *
	 * Compiles the url to be executed when the Console menu item is clicked
	 *
	 * Compiles the url to be executed when the Console menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string						action to be executed when the Console menu item is clicked
	 *
	 * @method
	 */
	function Console()
	{
		$this->strLabel	= "Home";
		
		return $this->GetBaseURL()."flex.php/Console/Home/";
	}


	//------------------------------------------------------------------------//
	// ResetPassword()
	//------------------------------------------------------------------------//
	/**
	 * ResetPassword()
	 *
	 * 
	 * @return	string	action to be executed when linking to the ResetPassword Console
	 *
	 * @method
	 */
	function ResetPassword()
	{
		$this->strLabel	= "Reset Password";

		return $this->GetBaseURL()."./flex.php/Console/Password/";
	}
	//------------------------------------------------------------------------//
	// ResendUsername()
	//------------------------------------------------------------------------//
	/**
	 * ResendUsername()
	 *
	 * 
	 * @return	string	action to be executed when linking to the ResendUsername Console
	 *
	 * @method
	 */
	function ResendUsername()
	{
		$this->strLabel	= "Resend Username";

		return $this->GetBaseURL()."./flex.php/Console/Username/";
	}

	//------------------------------------------------------------------------//
	// ManagementConsole()
	//------------------------------------------------------------------------//
	/**
	 * ManagementConsole()
	 *
	 * Compiles the url to be executed when linking to the Management Console
	 *
	 * Compiles the url to be executed when linking to the Management Console
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string	action to be executed when linking to the Management Console
	 *
	 * @method
	 */
	function ManagementConsole()
	{
		$this->strLabel	= "Management Console";

		return $this->GetBaseURL()."../management/index.php";
	}

	//------------------------------------------------------------------------//
	// LoadAccountInConsole DEPRICATED
	//------------------------------------------------------------------------//
	/**
	 * LoadAccountInConsole()
	 *
	 * Compiles the url to be executed when the LoadAccountInConsole menu item is clicked
	 *
	 * Compiles the url to be executed when the LoadAccountInConsole menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		account id to load
	 *
	 * @return	string						action to be executed when the LoadAccountInConsole menu item is clicked
	 *
	 * @method
	 */
	function LoadAccountInConsole($intAccountId)
	{
		$this->strLabel	= "Home";
		
		return $this->GetBaseURL()."flex.php/Console/Home/?Account.Id=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// Home
	//------------------------------------------------------------------------//
	/**
	 * Home()
	 *
	 * Compiles the url to be executed when the Home menu item is clicked
	 *
	 * Compiles the url to be executed when the Home menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string						action to be executed when the Home menu item is clicked
	 *
	 * @method
	 */
	function Home()
	{
		$this->strLabel	= "Home";
		
		return $this->GetBaseURL()."flex.php/Console/Home/";
	}


	//------------------------------------------------------------------------//
	// EditContactDetails 
	//------------------------------------------------------------------------//
	function EditAccountDetails($intAccountId)
	{
		$this->strLabel	= "Edit Account Details";
		
		return $this->GetBaseURL()."flex.php/Console/Edit/?Account.Id=$intAccountId";
	}


	//------------------------------------------------------------------------//
	// MakePayment 
	//------------------------------------------------------------------------//
	function MakePayment($intAccountId)
	{
		$this->strLabel	= "Pay Your Account Here";
		
		return $this->GetBaseURL()."flex.php/Console/Pay/?Account.Id=$intAccountId";
	}
	
	//------------------------------------------------------------------------//
	// LoadAccountInHomePage 
	//------------------------------------------------------------------------//
	/**
	 * LoadAccountInHomePage()
	 *
	 * Compiles the url to be executed when the LoadAccountInHomePage menu item is clicked
	 *
	 * Compiles the url to be executed when the LoadAccountInHomePage menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		account id to load
	 *
	 * @return	string						action to be executed when the LoadAccountInHomePage menu item is clicked
	 *
	 * @method
	 */
	function LoadAccountInHomePage($intAccountId)
	{
		$this->strLabel	= "Home";
		
		return $this->GetBaseURL()."flex.php/Console/Home/?Account.Id=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// LogoutUser
	//------------------------------------------------------------------------//
	/**
	 * LogoutUser()
	 *
	 * Compiles the url to be executed when the LogoutUser menu item is clicked
	 *
	 * Compiles the url to be executed when the LogoutUser menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string						action to be executed when the LogoutUser menu item is clicked
	 *
	 * @method
	 */
	function LogoutUser()
	{
		//$this->strLabel	= "Logout";
		//return "vixen.php/Console/Logout/";
		
		$this->strLabel	= "Logout";
		
		// Popup style logout
		//return "javascript:Vixen.Ajax.CallAppTemplate(\"Console\", \"Logout\")";
		
		
		// Page style logout
		return $this->GetBaseURL()."flex.php/Console/Logout/";
	}


	//------------------------------------------------------------------------//
	// ViewUnbilledChargesForService
	//------------------------------------------------------------------------//
	/**
	 * ViewUnbilledChargesForService()
	 *
	 * Compiles the url to be executed when the ViewUnbilledChargesForService menu item is clicked
	 *
	 * Compiles the url to be executed when the ViewUnbilledChargesForService menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intServiceId				service id to load
	 * @param	int		$intPage					optional, page number of the paginated CDR table to load
	 * @param 	int		$intFilterId				optional, id of the record type to use as a filter for the CDR table
	 *
	 * @return	string						action to be executed when the ViewUnbilledChargesForService menu item is clicked
	 *
	 * @method
	 */
	function ViewUnbilledChargesForService($intServiceId, $intPage=1, $intFilterId=0)
	{
		$this->strLabel	= "Service: $intServiceId";
		
		return $this->GetBaseURL()."flex.php/Service/ViewUnbilledCharges/?Service.Id=$intServiceId&Page.PageToLoad=$intPage&Filter.Id=$intFilterId";
	}
	
	//------------------------------------------------------------------------//
	// ViewUnbilledChargesForAccount
	//------------------------------------------------------------------------//
	/**
	 * ViewUnbilledChargesForAccount()
	 *
	 * Compiles the url to be executed when the ViewUnbilledChargesForAccount menu item is clicked
	 *
	 * Compiles the url to be executed when the ViewUnbilledChargesForAccount menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		Account id to load
	 *
	 * @return	string						action to be executed when the ViewUnbilledChargesForAccount menu item is clicked
	 *
	 * @method
	 */
	function ViewUnbilledChargesForAccount($intAccountId)
	{
		$this->strLabel	= "Account Charges";
		
		return $this->GetBaseURL()."flex.php/Account/ViewUnbilledCharges/?Account.Id=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// ViewInvoicesAndPayments
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoicesAndPayments()
	 *
	 * Compiles the url to be executed when the ViewInvoicesAndPayments menu item is clicked
	 *
	 * Compiles the url to be executed when the ViewInvoicesAndPayments menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		Account id to load
	 *
	 * @return	string						action to be executed when the ViewInvoicesAndPayments menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoicesAndPayments($intAccountId, $bolDontIncludeSplash=FALSE)
	{
		$this->strLabel	= "Invoices and Payments";
		
		return $this->GetBaseURL()."flex.php/Account/ListInvoicesAndPayments/?Account.Id=$intAccountId";
	}


	//------------------------------------------------------------------------//
	// DownloadInvoicePDF
	//------------------------------------------------------------------------//
	/**
	 * DownloadInvoicePDF()
	 *
	 * Compiles the url to be executed when the DownloadInvoicePDF menu item is triggered
	 *
	 * Compiles the url to be executed when the DownloadInvoicePDF menu item is triggered
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		Account id that the invoice belongs to
	 * @param	int		$intYear			year the invoice relates to
	 * @param	int 	$intMonth			month the invoice relates to
	 *
	 * @return	string						action to be executed when the DownloadInvoicePDF menu item is clicked
	 *
	 * @method
	 */
	function DownloadInvoicePDF($intAccountId, $intYear, $intMonth, $intInvoiceId, $strInvoiceRun)
	{
		$this->strLabel	= "Download Invoice Pdf";
		
		return $this->GetBaseURL()."flex.php/Account/DownloadInvoicePDF/?Account.Id=$intAccountId&Invoice.Year=$intYear&Invoice.Month=$intMonth&Invoice.Id=$intInvoiceId&Invoice.InvoiceRun=$strInvoiceRun";
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

?>
