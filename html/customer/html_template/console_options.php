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
		$strViewUnbilledChargesLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strViewUnbilledCharges' >&nbsp;&nbsp;View Unbilled Debits & Credits</a></span>";

		// build the "View Invoices and Payments" link
		$strViewInvoicesAndPayments = Href()->ViewInvoicesAndPayments(DBO()->Account->Id->Value);
		$strViewInvoicesAndPaymentsLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strViewInvoicesAndPayments' >&nbsp;&nbsp;View Invoices and Payments</a></span>";

		// EditAccountDetails link
		$strEditAccountDetails = Href()->EditAccountDetails(DBO()->Account->Id->Value);
		$strEditAccountDetailsLabel = "<span><img src=\"" . Href()->GetBaseUrl() . "img/generic/square_black.gif\"> <a href='$strEditAccountDetails' >&nbsp;&nbsp;Edit Account Details</a></span>";


		//echo "<h2 class='Options'>Options</h2>\n";
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
		echo "</table>\n";
				
		//echo "</div>\n";
	}
}

?>
