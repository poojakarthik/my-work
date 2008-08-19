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
 


 class HtmlTemplateAccountEdit extends HtmlTemplate
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
		echo "<div class='NarrowContent'>\n";

		// Put db fields into usable variables... e.g. `Email` field is now `$Email`
		$mixFetchAccountDetails=DBO()->Account;
		foreach($mixFetchAccountDetails as $mixKey=>$mixVal)
		{
			$$mixKey=$mixVal;
		}
		$mixFetchContactDetails=DBO()->Contact;
		foreach($mixFetchContactDetails as $mixKey=>$mixVal)
		{
			$$mixKey=$mixVal;
		}
		
		// Display the details of their primary address
		$intAccountUpdated = DBO()->Account->Updated->Value;
		echo "$intAccountUpdated";
		if($intAccountUpdated=="1")
		{
			print "Thank you for taking the time to update your account,<br/><font color=\"green\">your changes have been completed. <img src=\"/" . CUSTOMER_URL_NAME . "/trunk/html/images/generic/check.gif\"></font><br/><br/>";
		}
		if($intAccountUpdated!="1")
		{
			print "Thank you for taking the time to update your account,<br/>Please apply new options below.<br/><br/>";
			print "
			<!-- We dont want any caching of this page.. -->
			<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
			<form method=\"POST\" action=\"./flex.php/Console/Edit/\">
			<input type=\"hidden\" name=\"intUpdateAccountId\" value=\"$intAccountId\">
			<TABLE>
			<TR>
			<TD colspan=\"2\"><IMG SRC=\"./img/template/account.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=\"0\" ALT=\"\"> <B>Address Details</B></TD>
			</TR>
			<TR>
			<TD width=\"200\">Address1: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address1\" VALUE=\"$Address1\"></TD>
			</TR>
			<TR>
			<TD>Address2: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address2\" VALUE=\"$Address2\"></TD>
			</TR>
			<TR>
			<TD>Suburb: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Suburb\" VALUE=\"$Suburb\"></TD>
			</TR>
			<TR>
			<TD>State: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_State\" VALUE=\"$State\"></TD>
			</TR>
			<TR>
			<TD>Postcode: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Postcode\" VALUE=\"$Postcode\"></TD>
			</TR>
			<TR>
			<TD>Country: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Country\" VALUE=\"$Country\"></TD>
			</TR>
			</TABLE>
			<br/>
			<TABLE>
			<TR>
			<TD colspan=\"2\"><IMG SRC=\"./img/template/account.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=\"0\" ALT=\"\"> <B>Contact Details</B></TD>
			</TR>
			<TR>
			<TD width=\"200\">Title: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"Title\" VALUE=\"$Title\"></TD>
			</TR>
			<TR>
			<TD>FirstName: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_FirstName\" VALUE=\"$FirstName\"></TD>
			</TR>
			<TR>
			<TD>LastName: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_LastName\" VALUE=\"$LastName\"></TD>
			</TR>
			<TR>
			<TD>JobTitle: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_JobTitle\" VALUE=\"$JobTitle\"></TD>
			</TR>
			<TR>
			<TD>Email: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Email\" VALUE=\"$Email\"></TD>
			</TR>
			<TR>
			<TD>Phone: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Phone\" VALUE=\"$Phone\"></TD>
			</TR>
			<TR>
			<TD>Mobile: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Mobile\" VALUE=\"$Mobile\"></TD>
			</TR>
			<TR>
			<TD>Fax: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Fax\" VALUE=\"$Fax\"></TD>
			</TR>
			</TABLE>
			<br/>
			<TABLE>
			<TR>
			<TD width=\"200\"></TD>
			<TD><INPUT TYPE=\"submit\" VALUE=\"Update Details\"></TD>
			</TR>
			</TABLE>
			";
		}
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
