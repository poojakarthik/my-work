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
 


 class HtmlTemplateAccountEditConfirm extends HtmlTemplate
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
		echo "<div class='customer-standard-display-title'>&nbsp;</div><br/><br/>";
		echo "<div class='customer-standard-table-title-style-notice'><FONT COLOR='red'>Please confirm the new changes below</FONT></div><br/><br/>";

		echo "
		<form method=\"POST\" action=\"./flex.php/Console/Edit/\"\">
		<input type=\"hidden\" name=\"intUpdateAccountId\" value=\"" . htmlspecialchars($_POST['intUpdateAccountId']) . "\">
		<input type=\"hidden\" name=\"mixAccount_Address1\" value=\"" . htmlspecialchars($_POST['mixAccount_Address1']) . "\">
		<input type=\"hidden\" name=\"mixAccount_Address2\" value=\"" . htmlspecialchars($_POST['mixAccount_Address2']) . "\">
		<input type=\"hidden\" name=\"mixAccount_Suburb\" value=\"" . htmlspecialchars($_POST['mixAccount_Suburb']) . "\">
		<input type=\"hidden\" name=\"mixAccount_State\" value=\"" . htmlspecialchars($_POST['mixAccount_State']) . "\">
		<input type=\"hidden\" name=\"mixAccount_Postcode\" value=\"" . htmlspecialchars($_POST['mixAccount_Postcode']) . "\">
		<input type=\"hidden\" name=\"mixAccount_BillingMethod\" value=\"" . htmlspecialchars($_POST['mixAccount_BillingMethod']) . "\">
		<input type=\"hidden\" name=\"mixAccount_Country\" value=\"" . htmlspecialchars($_POST['mixAccount_Country']) . "\">
		<input type=\"hidden\" name=\"mixContact_FirstName\" value=\"" . htmlspecialchars($_POST['mixContact_FirstName']) . "\">
		<input type=\"hidden\" name=\"mixContact_LastName\" value=\"" . htmlspecialchars($_POST['mixContact_LastName']) . "\">
		<input type=\"hidden\" name=\"mixContact_Title\" value=\"" . htmlspecialchars($_POST['mixContact_Title']) . "\">
		<input type=\"hidden\" name=\"mixContact_JobTitle\" value=\"" . htmlspecialchars($_POST['mixContact_JobTitle']) . "\">
		<input type=\"hidden\" name=\"mixContact_Email\" value=\"" . htmlspecialchars($_POST['mixContact_Email']) . "\">
		<input type=\"hidden\" name=\"mixContact_Phone\" value=\"" . htmlspecialchars($_POST['mixContact_Phone']) . "\">
		<input type=\"hidden\" name=\"mixContact_Mobile\" value=\"" . htmlspecialchars($_POST['mixContact_Mobile']) . "\">
		<input type=\"hidden\" name=\"mixAccount_OldPassword\" value=\"" . htmlspecialchars($_POST['mixAccount_OldPassword']) . "\">
		<input type=\"hidden\" name=\"mixAccount_NewPassword1\" value=\"" . htmlspecialchars($_POST['mixAccount_NewPassword1']) . "\">
		<input type=\"hidden\" name=\"mixAccount_NewPassword2\" value=\"" . htmlspecialchars($_POST['mixAccount_NewPassword2']) . "\">
		<input type=\"hidden\" name=\"mixContact_Fax\" value=\"" . htmlspecialchars($_POST['mixContact_Fax']) . "\">";

		$intBillMethod = htmlspecialchars($_POST['mixAccount_BillingMethod']);
		$strNewBillingMethod = $GLOBALS['*arrConstant']['BillingMethod'][$intBillMethod]['Description'];
		print "
		<div class='customer-standard-table-title-style-confirm-details'>Billing Details</div>
		<!-- <h2 class='Account'>Billing Details</h2> -->
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD width=\"200\">Billing Method: </TD>
			<TD>$strNewBillingMethod</TD>
		</TR>
		</TABLE>
		</div>
		<br/>";

		print "
		<div class='customer-standard-table-title-style-confirm-details'>Address Details</div>
		<!-- <h2 class='Account'>Address Details</h2> -->
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD width=\"200\">Street Address: </TD>
			<TD>" . htmlspecialchars($_POST['mixAccount_Address1']) . "</TD>
		</TR>
		<TR>
			<TD></TD>
			<TD>" . htmlspecialchars($_POST['mixAccount_Address2']) . "</TD>
		</TR>
		<TR>
			<TD>Suburb: </TD>
			<TD>" . htmlspecialchars($_POST['mixAccount_Suburb']) . "</TD>
		</TR>
		<TR>
			<TD>State: </TD>
			<TD>" . htmlspecialchars($_POST['mixAccount_State']) . "</TD>
		</TR>
		<TR>
			<TD>Postcode: </TD>
			<TD>" . htmlspecialchars($_POST['mixAccount_Postcode']) . "</TD>
		</TR>
		<TR>
			<TD>Country: </TD>
			<TD>" . htmlspecialchars($_POST['mixAccount_Country']) . "</TD>
		</TR>
		</TABLE>
		</div>
		<br/>";

		print "
		<div class='customer-standard-table-title-style-confirm-details'>Contact Details</div>
		<!-- <h2 class='Account'>Contact Details</h2> -->
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD width=\"200\">First Name: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_FirstName']) . "</TD>
		</TR>
		<TR>
			<TD>Last Name: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_LastName']) . "</TD>
		</TR>
		<TR>
			<TD>Title: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_Title']) . "</TD>
		</TR>
		<TR>
			<TD>Job Title: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_JobTitle']) . "</TD>
		</TR>
		<TR>
			<TD>Email: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_Email']) . "</TD>
		</TR>
		<TR>
			<TD>Phone: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_Phone']) . "</TD>
		</TR>
		<TR>
			<TD>Mobile: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_Mobile']) . "</TD>
		</TR>
		<TR>
			<TD>Fax: </TD>
			<TD>" . htmlspecialchars($_POST['mixContact_Fax']) . "</TD>
		</TR>
		</TABLE>
		</div>
		<br/>

		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD align=right><input type=\"submit\" value=\"Confirm Changes\"></TD>
		</TR>
		</TABLE>
		</form>
		";
		
		echo "</div>\n";
	}
}

?>
