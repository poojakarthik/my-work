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
		$this->LoadJavascript("tooltip");
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
		$strTableName = 'AccountInvoices';
		//DBL()->ShowInfo();
				
		echo "<table border='0' cellpadding='3' cellspacing='0' class='Listing' width='100%' id='$strTableName'>\n";
		echo "<tr class='First'>\n";
		echo " <th>Invoice Date</th>\n";
		echo " <th>Invoice Id</th>\n";
		echo " <th>Invoice Amount</th>\n";
		echo " <th>Applied Amount (balance)</th>\n";
		echo " <th>Amount Owing (accountbalance)</th>\n";
		echo " <th>Invoice Sent (status)</th>\n";
		echo " <th>View PDF</th>\n";
		echo " <th>View Invoice Details</th>\n";
		echo "</tr>\n";
		$intRowCount = -1;
		foreach (DBL()->Invoice AS $strProperty=>$objValue)
		{
			$intRowCount++;
			$strClass = ($intRowCount % 2) ? 'Even' : 'Odd' ;
			echo "<tr id='" . $strTableName . "_" . $intRowCount . "' class='$strClass'>\n";
			echo "<td>";
			$objValue->DueOn->RenderValue();
			echo "</td>\n";
			echo "<td>";
			$objValue->Id->RenderValue();
			echo "</td>\n";
			echo "<td>";
			$objValue->Total->RenderValue();	// Invoice Amount
			echo "</td>\n";			
			echo "<td>";
			$objValue->Balance->RenderValue();
			echo "</td>\n";			
			echo "<td>";
			$objValue->AccountBalance->RenderValue();
			echo "</td>\n";
			echo "<td>";
			echo GetConstantDescription($objValue->Status->Value, "InvoiceStatus");
			echo "</td>\n";
			echo "<td>";
			echo "<img src='img/template/pdf.png' height=20px></img>";	// View PDF icon
			echo "</td>\n";
			echo "<td>";
			echo "<a href=''><img src='img/template/invoice.png' height=20px></img></a>";			// View Invoice Details icon
			echo "</td>\n";
			echo "</tr>\n<tr>";
			echo "<td colspan=8 style='padding-top: 0px; padding-bottom: 0px'>\n";
			echo "<div id='" . $strTableName . "_" . $intRowCount . "DIV-DETAIL' style='display: block; overflow:hidden;'>";
			// todo - add the payment applied details in here
			echo $objValue->Info();
			echo "</div>\n";
			echo "<div id='" . $strTableName . "_" . $intRowCount . "DIV-TOOLTIP' style='display: none;'>";
			echo "Tooltip goes here";
			echo "</div>\n";
			echo "</td>\n</tr>\n";
		}
		echo "</table>\n";
		echo "<script type='text/javascript'>Vixen.AddCommand('Vixen.Highlight.Attach','\'$strTableName\'', $intRowCount);</script>";
		echo "<script type='text/javascript'>Vixen.Slide.Attach('$strTableName', $intRowCount, TRUE);</script>";
		echo "<script type='text/javascript'>Vixen.Tooltip.Attach('$strTableName', $intRowCount);</script>";
		
	}
}

?>
