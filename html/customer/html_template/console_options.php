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
		//echo "<br/><br/><div class='NarrowContent'>\n";
		echo "<br/><br/>\n";

		require_once dirname(__FILE__) . '/../../../lib/classes/credit/card/Credit_Card_Payment.php';
		
		$bolCC = Credit_Card_Payment::availableForCustomerGroup(DBO()->Account->CustomerGroup->Value);
		if ($bolCC)
		{
			// build the "View Unbilled Charges for Account" link
			$strMakePayment = Href()->MakePayment(DBO()->Account->Id->Value);
			$strMakePaymentLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strMakePayment' >&nbsp;&nbsp;Pay Your Account Here</a></span>";
		}

		// build the "View Unbilled Charges for Account" link
		$strViewUnbilledCharges = Href()->ViewUnbilledChargesForAccount(DBO()->Account->Id->Value);
		$strViewUnbilledChargesLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strViewUnbilledCharges' >&nbsp;&nbsp;View Unbilled Debits &amp; Credits</a></span>";

		// build the "View Invoices and Payments" link
		$strViewInvoicesAndPayments = Href()->ViewInvoicesAndPayments(DBO()->Account->Id->Value);
		$strViewInvoicesAndPaymentsLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strViewInvoicesAndPayments' >&nbsp;&nbsp;View Invoices and Payments</a></span>";

		// EditAccountDetails link
		$strEditAccountDetails = Href()->EditAccountDetails(DBO()->Account->Id->Value);
		$strEditAccountDetailsLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strEditAccountDetails' >&nbsp;&nbsp;Edit Account Details</a></span>";
 
		// Support link
		$strSupportRequest = Href()->SupportRequest(DBO()->Account->Id->Value);
		$strSupportRequestLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strSupportRequest' >&nbsp;&nbsp;Create Support Request</a></span>";
 
		// FAQ Link
		$strCustomerFAQ = Href()->CustomerFAQ(DBO()->Account->Id->Value);
		$strCustomerFAQLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strCustomerFAQ' >&nbsp;&nbsp;Frequently Asked Questions</a></span>";
 
		// Customer Survey
		$strCustomerSurvey = Href()->CustomerSurvey(DBO()->Account->Id->Value);
		$strCustomerSurveyLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strCustomerSurvey' >&nbsp;&nbsp;Customer Survey</a></span>";
 
		echo "<div class='customer-standard-table-style-menu-options-title'>Options</div>\n";
		echo "<table class=\"customer-standard-table-style-menu-options\">\n";
		
		if ($bolCC)
		{
			echo "   <tr>\n";
			echo "      <td>\n";
			echo "			$strMakePaymentLabel\n";
			echo "      </td>\n";
			echo "   </tr>\n";
		}
		
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "			$strViewUnbilledChargesLabel\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "			$strViewInvoicesAndPaymentsLabel\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "			$strEditAccountDetailsLabel\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "			$strSupportRequestLabel\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>$strCustomerFAQLabel\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>$strCustomerSurveyLabel\n";
		echo "   </tr>\n";
		echo "</table>
		<BR><BR>\n";
		echo "<div class='customer-standard-table-style-menu-options-title'>Secure Payments</div>\n";
		echo "<table class=\"customer-standard-table-style-menu-options-info\">\n";	
		echo "   <tr>\n";
		echo "      <td><IMG SRC=\"./img/generic/logo_securepay.gif\" WIDTH=\"136\" HEIGHT=\"58\" BORDER=\"0\" ALT=\"\"></td>";
		echo "		<td>All payments are processed securely by one of Australias leading payment gateways.</td>\n";
		echo "   </tr>
		</table><BR><BR>\n";
		echo "<div class='customer-standard-table-style-menu-options-title'>Our Partners</div>\n";
		echo "<table class=\"customer-standard-table-style-menu-options-info\">\n";
		
			echo "   <tr>\n";
			echo "      <td><A HREF=\"http://www.yellowbilling.com.au/\" target=\"_blank\"><IMG SRC=\"./img/template/logo.png\" WIDTH=\"320\" HEIGHT=\"60\" BORDER=\"0\" ALT=\"\"></A></td>\n";
			echo "   </tr>
			</table>\n";
		//echo "</div>\n";
	}
}

?>
