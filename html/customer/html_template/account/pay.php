<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountDetails
 *
 * HTML Template object for the Account Details
 *
 * HTML Template object for the Account Details
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
 


 class HtmlTemplateAccountPay extends HtmlTemplate
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

		$this->LoadJavascript('prototype');
		$this->LoadJavascript('jquery');
		$this->LoadJavascript('json');
		$this->LoadJavascript('reflex_popup');
		$this->LoadJavascript('credit_card_type');
		$this->LoadJavascript('credit_card_payment');
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
		echo "<div class='customer-standard-display-title'>&nbsp;</div>
		<IMG SRC=\"./img/generic/cc_payments_accepted.jpg\" WIDTH=\"402\" HEIGHT=\"47\" BORDER=\"0\" ALT=\"\"><br/>
		<div class='small'>Secured by Equifax Secure INC. High grade encryption 256 bit.</div><br/><br/>
		<div class='customer-standard-table-title-style-confirm-details'>Important Payment Information</div>
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD>Credit card payments are processed by SecurePay in Australian dollars (AUD.)</TD>
		</TR>
		</TABLE>
		</div><br/>";
		// this is going to be replaced with HO's form.
		require_once dirname(__FILE__) . '/../../../../lib/classes/credit/card/Credit_Card_Payment.php';
		$strPanel = Credit_Card_Payment::getPaymentPanel(DBO()->Account->Id->Value);
		if ($strPanel)
		{
			echo $strPanel;

		}
		if (!$strPanel)
		{
			echo "Configuration has not been set for your Customer Group.";
		}
	}
}

?>
