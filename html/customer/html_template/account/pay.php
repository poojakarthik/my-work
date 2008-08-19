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
		
		$mixFetchAccountDetails=DBO()->Account;
		foreach($mixFetchAccountDetails as $mixKey=>$mixVal)
		{
			// Put db fields into usable variables... e.g. `Email` field is now `$Email`
			$$mixKey=$mixVal;
		}

		echo "<div class='NarrowContent'>\n";
		$mixShowName = "$FirstName $LastName";
		if($Title!==""){
			$mixShowName = "$Title $FirstName $LastName";
		}
		
		print "Making a payment is fast and easy,<br/>Please supply the required details below.<br/><br/>";
		print "
		<!-- We dont want any caching of this page.. -->
		<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
		<form method=\"POST\" action=\"./flex.php/Console/Pay/\">
		<input type=\"hidden\" name=\"intUpdateAccountId\" value=\"$intAccountId\">
		<TABLE>
		<TR>
			<TD colspan=\"2\"><IMG SRC=\"./img/template/account.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=\"0\" ALT=\"\"> <B>Payment Details</B></TD>
		</TR>
		<TR>
			<TD width=\"200\">Email*</TD>
			<TD><INPUT TYPE=\"text\" NAME=\"\" VALUE=\"$Email\"></TD>
		</TR>
		<TR>
			<TD>Name on card*</TD>
			<TD><INPUT TYPE=\"text\" NAME=\"\" VALUE=\"$mixShowName\"></TD>
		</TR>
		<TR>
			<TD>Cred Card Type*</TD>
			<TD><SELECT NAME=\"\">
				<OPTION VALUE=\"\" SELECTED>
				<OPTION VALUE=\"\">
			</SELECT></TD>
		</TR>
		<TR>
			<TD>Credit Card Number*</TD>
			<TD><INPUT TYPE=\"text\" NAME=\"\"></TD>
		</TR>
		<TR>
			<TD>Expiry</TD>
			<TD><SELECT NAME=\"\">
				<OPTION VALUE=\"\" SELECTED>month
				<OPTION VALUE=\"\">
			</SELECT>
			<SELECT NAME=\"\">
				<OPTION VALUE=\"\" SELECTED>year
				<OPTION VALUE=\"\">
			</SELECT></TD>
		</TR>
		<TR>
			<TD>CVV*</TD>
			<TD><INPUT TYPE=\"text\" NAME=\"\" SIZE=\"5\"></TD>
		</TR>
		<TR>
			<TD>Amount to pay*</TD>
			<TD><INPUT TYPE=\"text\" NAME=\"\" SIZE=\"10\"></TD>
		</TR>
		<TR>
			<TD>\$ to SecurePay</TD>
			<TD><INPUT TYPE=\"text\" NAME=\"\" VALUE=\"WILL NEED JAVA SCRIPT\" DISABLED></TD>
		</TR>
		<TR>
			<TD>\$ Outstanding after paymnt</TD>
			<TD><INPUT TYPE=\"text\" NAME=\"\" VALUE=\"WILL NEED JAVA SCRIPT\" DISABLED></TD>
		</TR>
		<TR>
			<TD>Enable Direct Debit.</TD>
			<TD><INPUT TYPE=\"checkbox\" NAME=\"\"></TD>
		</TR>
		</TABLE>
		<br/>
		<TABLE>
		<TR>
			<TD width=\"200\"></TD>
			<TD><INPUT TYPE=\"submit\" VALUE=\"Make Payment\"></TD>
		</TR>
		</TABLE>
		";
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
