<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_import.php
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupCreditCardPaymentLink
 *
 * HTML Template for the Customer Group Credit Card Config Link
 *
 * HTML Template for the Customer Group Credit Card Config Link
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the link to the Customer Group Credit Card Config page.
 *
 * @file		rate_group_import.php Customer Group Credit Card
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateCustomerGroupCreditCardConfigLink
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupCreditCardConfigLink
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateCustomerGroupCreditCardConfigLink
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupCreditCardConfigLink extends HtmlTemplate
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
	 *
	 * @method
	 */
	function Render()
	{
		echo "<h2 class='CustomerGroup'>Secure Pay Configuration</h2>\n";

		require_once dirname(__FILE__) . '/../../../../lib/classes/credit/card/Credit_Card_Payment_Config.php';
		$config = Credit_Card_Payment_Config::getForCustomerGroup(DBO()->CustomerGroup->Id->Value);

		echo "<div class='GroupedContent'>\n";
		
		if ($config)
		{
			echo "
			<TABLE>
				<TR>
					<TD width=\"200\">Merchant Id: </TD>
					<TD>" . $config->merchantId . "</TD>
				</TR>
			</TABLE>";
		}
		else
		{
			echo "
			<TABLE>
				<TR>
					<TD colspan=\"2\">[ Not configurred ] </TD>
				</TR>
			</TABLE>";
		}
		echo "</div>\n";
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$url = Href()->ViewCustomerGroupCreditCardConfig(DBO()->CustomerGroup->Id->Value, $config ? 'View' : 'Create');
		$this->Button(($config ? "View" : "Enter") . " Configuration", "document.location='$url'");
		echo "</div></div>\n";

		echo "<br/><br/>";
	}

}

?>
