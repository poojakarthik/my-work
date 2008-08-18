<?php
//----------------------------------------------------------------------------//
// HtmlTemplateConsoleOptions
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateConsoleOptions
 *
 * HTML Template object for the client app console options
 *
 * HTML Template object for the client app console options
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateConsoleOptions
 * @extends	HtmlTemplate
 */
class HtmlTemplateConsoleOptions extends HtmlTemplate
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
		echo "<div class='NarrowContent'>\n";

		echo "<h2 class='Options'>Options</h2>\n";
		
		// build the "View Unbilled Charges for Account" link
		$strViewUnbilledCharges = Href()->ViewUnbilledChargesForAccount(DBO()->Account->Id->Value);
		$strViewUnbilledChargesLabel = "<span><a href='$strViewUnbilledCharges' >&nbsp;&nbsp;View Unbilled Charges</a></span>";

		// build the "View Invoices and Payments" link
		$strViewInvoicesAndPayments = Href()->ViewInvoicesAndPayments(DBO()->Account->Id->Value);
		$strViewInvoicesAndPaymentsLabel = "<span><a href='$strViewInvoicesAndPayments' >&nbsp;&nbsp;View Invoices and Payments</a></span>";
		
		echo "<table width='100%' border='0' class=\"main_table\">\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		#echo "         $strViewUnbilledChargesLabel\n";
		echo "        <img src=\"/" . CUSTOMER_URL_NAME . "/trunk/html/images/generic/square_black.gif\"> <a href=\"./flex.php/Console/Home/\"><font size=\"2\">Console Home</font></a><br/><br/>\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "        <img src=\"/" . CUSTOMER_URL_NAME . "/trunk/html/images/generic/square_black.gif\"> <a href=\"./flex.php/Console/Pay/\"><font size=\"2\">Pay Your Account Here</font></a>\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		#echo "         $strViewUnbilledChargesLabel\n";
		echo "        <img src=\"/" . CUSTOMER_URL_NAME . "/trunk/html/images/generic/square_black.gif\"> <a href=\"./flex.php/Account/ViewUnbilledCharges/?Account.Id=" . DBO()->Account->Id->Value . "\"><font size=\"2\">View Unbilled Charges</font></a>\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		#echo "         $strViewInvoicesAndPaymentsLabel\n";
		echo "        <img src=\"/" . CUSTOMER_URL_NAME . "/trunk/html/images/generic/square_black.gif\"> <a href=\"./flex.php/Account/ListInvoicesAndPayments/?Account.Id=" . DBO()->Account->Id->Value . "\"><font size=\"2\">View Invoices and Payments</font></a>\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "        <img src=\"/" . CUSTOMER_URL_NAME . "/trunk/html/images/generic/square_black.gif\"> <a href=\"./flex.php/Console/Edit/\"><font size=\"2\">Edit Account Details</font></a>\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "</table>\n";
				
		echo "</div>\n";
	}
}

?>
