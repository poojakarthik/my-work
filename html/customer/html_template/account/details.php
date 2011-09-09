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
 


 class HtmlTemplateAccountDetails extends HtmlTemplate
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
	 * @method
	 */
	 
	function Render()
	{
		#echo "<div class='NarrowContent'>\n";
	
		echo "<div class='customer-standard-display-title'>&nbsp;Home</div><br><br>";

		echo "<div class='customer-standard-table-title-style-account'>Account Details</div>\n";
		//echo "<h2 class='Account'>Account Details</h2>\n";

		echo "	
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">";
		
		if(DBO()->Account->BusinessName->Value)
		{
			print "
			<TR>
				<TD width=\"200\">Business Name: </TD>
				<TD>" . DBO()->Account->BusinessName->Value . "</TD>
			</TR>";
		}
		if(DBO()->Account->TradingName->Value)
		{
			print "
			<TR>
				<TD>Trading Name: </TD>
				<TD>" . DBO()->Account->TradingName->Value . "</TD>
			</TR>";
		}
		if(DBO()->Account->ABN->Value)
		{
			print "
			<TR>
				<TD>ABN: </TD>
				<TD>" . DBO()->Account->ABN->Value . "</TD>
			</TR>";
		}
		if(DBO()->Account->ACN->Value)
		{
			print "
			<TR>
				<TD>ACN: </TD>
				<TD>" . DBO()->Account->ACN->Value . "</TD>
			</TR>";
		}
		$strCustomerBalance = "$" . number_format(DBO()->Account->CustomerBalance->Value, 2, '.', '');
		// The ereg function has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 6.0.0.
		// if(eregi("-",$strCustomerBalance))
		if(preg_match("/-/",$strCustomerBalance))
		{
			$strCustomerBalance = str_replace("-","",$strCustomerBalance) . " CR";
		}
		print "
		<TR>
			<TD>Customer Balance: </TD>
			<TD>$strCustomerBalance</TD>
		</TR>
		<TR>
			<TD>Overdue: </TD>
			<TD>" . "$" . number_format(Framework()->GetOverdueBalance(DBO()->Account->Id->Value), 2, '.', '') . "</TD>
		</TR>";

		$strUnbilledCharges = "$" . number_format(DBO()->Account->UnbilledCharges->Value, 2, '.', '');
		// The ereg function has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 6.0.0.
		// if(eregi("-",$strUnbilledCharges))
		if(preg_match("/-/",$strUnbilledCharges))
		{
			$strUnbilledCharges = str_replace("-","",$strUnbilledCharges) . " CR";
		}
		print "
		<TR>
			<TD>Unbilled Debits &amp; Credits: </TD>
			<TD>$strUnbilledCharges</TD>
		</TR>
		<TR>
			<TD>Unbilled Calls: </TD>
			<TD>" . "$" . number_format(DBO()->Account->UnbilledCDRs->Value, 2, '.', '') . "</TD>
		</TR>";

		
		$BillingMethod = DBO()->Account->BillingMethod->Value;
		$strDescriptionOfMethod = $GLOBALS['*arrConstant']['delivery_method'][$BillingMethod]['Description'];
		print "
		<TR>
			<TD>Billing Method: </TD>
			<TD>$strDescriptionOfMethod</TD>
		</TR>
		</TABLE>
		</div>
		<br/>\n";

		echo "<div class='customer-standard-table-title-style-address'>Billing Address Details</div>\n";
		//echo "<h2 class='Account'>Address Details</h2>\n";

		echo "	
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD width=\"200\">Street Address: </TD>
			<TD>" . DBO()->Account->Address1->Value . "</TD>
		</TR>
		<TR>
			<TD>&nbsp;</TD>
			<TD>" . DBO()->Account->Address2->Value . "</TD>
		</TR>
		<TR>
			<TD>Suburb: </TD>
			<TD>" . DBO()->Account->Suburb->Value . "</TD>
		</TR>
		<TR>
			<TD>State: </TD>
			<TD>" . DBO()->Account->State->Value . "</TD>
		</TR>
		<TR>
			<TD>Postcode: </TD>
			<TD>" . DBO()->Account->Postcode->Value . "</TD>
		</TR>
		<TR>
			<TD>Country: </TD>
			<TD>" . DBO()->Account->Country->Value . "</TD>
		</TR>
		</TABLE>
		</div>
		<br/>";
		
		$oAccountUser 	= Account_User::getForId(AuthenticatedUser()->_arrUser['id']);
		$D 				= new DOM_Factory();
		$D->getDOMDocument()->appendChild(
			$D->div(array('class' => 'customer-standard-table-title-style-contact'),
				"Contact Details"
			)
		);
		$D->getDOMDocument()->appendChild(
			$D->div(array('class' => 'grouped-content'),
				$D->table(array('class' => 'customer-standard-table-style'),
					$D->tr(
						$D->td(array('width' => 160),
							"Given Name: "
						),
						$D->td($oAccountUser->given_name)
					),
					$D->tr(
						$D->td("Family Name: "),
						$D->td($oAccountUser->family_name)
					),
					$D->tr(
						$D->td("Email Address: "),
						$D->td(trim($oAccountUser->email))
					)
				)
			)
		);
		echo $D->getDOMDocument()->saveHTML();
		echo "<br/>";
	}
}

?>
