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
		$strViewUnbilledChargesLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewUnbilledCharges' style='color:blue; text-decoration: none;'>&nbsp;&nbsp;View Unbilled Charges</a></span>";

		// build the "View Invoices and Payments" link
		$strViewInvoicesAndPayments = Href()->ViewInvoicesAndPayments(DBO()->Account->Id->Value);
		$strViewInvoicesAndPaymentsLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewInvoicesAndPayments' style='color:blue; text-decoration: none;'>&nbsp;&nbsp;View Invoices and Payments</a></span>";
		
		echo "<table width='100%' border='0'>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "         $strViewUnbilledChargesLabel\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "   <tr>\n";
		echo "      <td>\n";
		echo "         $strViewInvoicesAndPaymentsLabel\n";
		echo "      </td>\n";
		echo "   </tr>\n";
		echo "</table>\n";
				
		echo "</div>\n";
	}
}

?>
