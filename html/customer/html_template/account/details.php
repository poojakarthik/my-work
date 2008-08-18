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
		echo "<div class='NarrowContent'>\n";
		
		// Display the details of their primary account
		echo "<h2 class='Account'>Account Details</h2>\n";
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		if (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		if (trim(DBO()->Account->ABN->Value))
		{
			DBO()->Account->ABN->RenderOutput();
		}
		if (trim(DBO()->Account->ACN->Value))
		{
			DBO()->Account->ACN->RenderOutput();
		}
		
		DBO()->Account->CustomerBalance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		
		DBO()->Account->UnbilledAdjustments->RenderOutput();
		DBO()->Account->UnbilledCDRs->RenderOutput();
		
		// Display the details of their primary address
		
		$db_user = $GLOBALS['**arrDatabase']['flex']['User'];
		$db_pass = $GLOBALS['**arrDatabase']['flex']['Password'];
		$db_name = $GLOBALS['**arrDatabase']['flex']['Database'];
		$db_host = $GLOBALS['**arrDatabase']['flex']['URL'];

		$MySQLDatabase = new MySQLDatabase($db_host, $db_name, $db_user, $db_pass, $db_handler);
		# Debug only...
		# if($MySQLDatabase->is_connected()){
		#  echo "connected...";
		# }
		$intAccountId = DBO()->Account->Id->Value;
		$arrAccountTable = $MySQLDatabase->execute("SELECT * FROM Account WHERE Id='$intAccountId'");

		while($data = mysql_fetch_array($arrAccountTable))
		{
			foreach($data as $key=>$val)
			{
				$$key = $val;
			}
		}

		print "<br/>
		<TABLE>
		<TR>
			<TD colspan=\"2\"><IMG SRC=\"./img/template/account.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=\"0\" ALT=\"\"> <B>Address Details</B></TD>
		</TR>
		<TR>
			<TD width=\"200\">Address1: </TD>
			<TD>$Address1</TD>
		</TR>
		<TR>
			<TD>Address2: </TD>
			<TD>$Address2</TD>
		</TR>
		<TR>
			<TD>Suburb: </TD>
			<TD>$Suburb</TD>
		</TR>
		<TR>
			<TD>State: </TD>
			<TD>$State</TD>
		</TR>
		<TR>
			<TD>Postcode: </TD>
			<TD>$Postcode</TD>
		</TR>
		<TR>
			<TD>Country: </TD>
			<TD>$Country</TD>
		</TR>
		</TABLE>
		<br/>";

		// Display the contact details
		$arrContactTable = $MySQLDatabase->execute("SELECT * FROM Contact WHERE Account='$intAccountId'");

		while($data = mysql_fetch_array($arrContactTable))
		{
			foreach($data as $key=>$val)
			{
				$$key = $val;
			}
		}



		print "
		<TABLE>
		<TR>
			<TD colspan=\"2\"><IMG SRC=\"./img/template/account.gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=\"0\" ALT=\"\"> <B>Contact Details</B></TD>
		</TR>
		<TR>
			<TD width=\"200\">Title: </TD>
			<TD>$Title</TD>
		</TR>
		<TR>
			<TD>FirstName: </TD>
			<TD>$FirstName</TD>
		</TR>
		<TR>
			<TD>LastName: </TD>
			<TD>$LastName</TD>
		</TR>
		<TR>
			<TD>JobTitle: </TD>
			<TD>$JobTitle</TD>
		</TR>
		<TR>
			<TD>Email: </TD>
			<TD>$Email</TD>
		</TR>
		<TR>
			<TD>Phone: </TD>
			<TD>$Phone</TD>
		</TR>
		<TR>
			<TD>Mobile: </TD>
			<TD>$Mobile</TD>
		</TR>
		<TR>
			<TD>Fax: </TD>
			<TD>$Fax</TD>
		</TR>
		</TABLE>
		";
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
