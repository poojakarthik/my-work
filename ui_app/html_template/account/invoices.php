<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_invoices.php
//----------------------------------------------------------------------------//
/**
 * account_invoices
 *
 * HTML Template for the Account Invoices HTML object
 *
 * HTML Template for the Account Invoices HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all invoices relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		account_invoices.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountInvoices
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountInvoices
 *
 * HTML Template class for the Account Invoices HTML object
 *
 * HTML Template class for the Account Invoices HTML object
 * Lists all invoices related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountInvoices
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountInvoices extends HtmlTemplate
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
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
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
		// Render each of the account invoices
		//TODO!
		//echo "<br> INSERT ACCOUNT INVOICES HERE";
		
		//DBL()->ShowInfo();
				
		echo "<table border='0' cellpadding='3' cellspacing='0' class='Listing' width='100%' id='AccountInvoices'>\n";
		echo "<tr class='First'>\n";
		echo " <th>Account Id</th>\n";
		echo " <th>Invoice Id</th>\n";
		echo " <th>Account Balance</th>\n";
		echo " <th>Total Owing</th>\n";
		echo "</tr>\n";
		$intRowCount = 0;
		foreach (DBL()->Invoice AS $strProperty=>$objValue)
		{
			$intRowCount++;
			$strClass = ($intRowCount % 2) ? 'Odd' : 'Even' ;
			echo "<tr id='AccountInvoices_$intRowCount' class='$strClass'>\n";
			// change these to Render (not RenderOutput)
			echo "<td>";
			$objValue->Account->RenderValue();
			echo "</td>";
			echo "<td>";
			$objValue->Id->RenderValue();
			echo "</td>";
			echo "<td>";
			$objValue->AccountBalance->RenderValue();
			echo "</td>";
			echo "<td>";
			$objValue->TotalOwing->RenderValue();
			echo "</td>";			
			echo "</tr><tr>";
			echo "<td colspan=4 style='padding-top: 0px; padding-bottom: 0px'>";
			echo "<div id='AccountInvoices_" . $intRowCount . "DIV' style='height: 20px; display: none; overflow:hidden;'>";
			//$objValue->ShowInfo();
			echo $objValue->Status->Label . ":";
			$objValue->Status->RenderValue();
			echo "</div>";
			echo "</td></tr>\n";
		}
		echo "</table>\n";
		echo "<script type='text/javascript'>Vixen.Highlight.Attach('AccountInvoices', $intRowCount);</script>";
		echo "<script type='text/javascript'>Vixen.Slide.Attach('AccountInvoices', $intRowCount);</script>";
		
	}
}

?>
