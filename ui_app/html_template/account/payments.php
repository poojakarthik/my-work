<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_payments.php
//----------------------------------------------------------------------------//
/**
 * account_payments
 *
 * HTML Template for the Account Payments HTML object
 *
 * HTML Template for the Account Payments HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all payments relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		account_payments.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountPayments
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountPayments
 *
 * HTML Template class for the Account Payments HTML object
 *
 * HTML Template class for the Account Payments HTML object
 * Lists all payments related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountPayments
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountPayments extends HtmlTemplate
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
		//$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("debug");
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
		//DBL()->PaidInvoices->ShowInfo();
		$strTableName = 'AccountPayments';
		
		echo "<table border='0' cellpadding='3' cellspacing='0' class='Listing' width='100%' id='$strTableName'>\n";
		echo "<tr class='First'>\n";
		echo " <th>Payment Id</th>\n";
		echo " <th>Payment Amount</th>\n";
		echo " <th>Payment Date</th>\n";
		echo " <th>Account Balance</th>\n";
		echo "</tr>\n";
		$intRowCount = 0;
		foreach (DBL()->Payment AS $strProperty=>$objValue)
		{
			$intRowCount++;
			$strClass = ($intRowCount % 2) ? 'Odd' : 'Even' ;
			echo "<tr id='" . $strTableName . "_" . $intRowCount . "' class='$strClass'>\n";
			echo "<td>";
			$objValue->Id->RenderValue();
			echo "</td>";
			echo "<td>";
			$objValue->Amount->RenderValue();
			echo "</td>";	
			echo "<td>";
			$objValue->PaidOn->RenderValue();
			echo "</td>";
			echo "<td>";
			$objValue->Balance->RenderValue();
			echo "</td>";		
			echo "</tr><tr>";
			echo "<td colspan=4 style='padding-top: 0px; padding-bottom: 0px'>";
			echo "<div id='" . $strTableName . "_" . $intRowCount . "DIV' style='display: block; overflow:hidden;'>";
			$objValue->ShowInfo();
			//echo $objValue->Status->Label . ":";
			//$objValue->Status->RenderValue();
			echo "</div>";
			echo "</td></tr>\n";
		}
		/*foreach (DBL()->PaidInvoices AS $strProperty=>$objValue)
		{
			$intRowCount++;
			$strClass = ($intRowCount % 2) ? 'Odd' : 'Even' ;
			echo "<tr id='" . $strTableName . "_" . $intRowCount . "' class='$strClass'>\n";
			echo "<td>";
			$objValue->InvoiceId->RenderValue();
			echo "</td>";
			echo "<td>";
			$objValue->InvoiceAmount->RenderValue();
			echo "</td>";	
			echo "<td>";
			$objValue->PaymentAmount->RenderValue();
			echo "</td>";
			echo "<td>";
			$objValue->AccountBalance->RenderValue();
			echo "</td>";		
			echo "<td>";
			$objValue->PaymentDate->RenderValue();
			echo "</td>";			
			echo "</tr><tr>";
			echo "<td colspan=4 style='padding-top: 0px; padding-bottom: 0px'>";
			echo "<div id='" . $strTableName . "_" . $intRowCount . "DIV' style='display: block; overflow:hidden;'>";
			$objValue->ShowInfo();
			//echo $objValue->Status->Label . ":";
			//$objValue->Status->RenderValue();
			echo "</div>";
			echo "</td></tr>\n";
		}*/
		echo "</table>\n";
		echo "<script type='text/javascript'>Vixen.Highlight.Attach('$strTableName', $intRowCount);</script>";
		echo "<script type='text/javascript'>Vixen.Slide.Attach('$strTableName', $intRowCount, TRUE);</script>";

	}
}

?>
