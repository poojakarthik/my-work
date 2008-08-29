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
 


 class HtmlTemplateAccountEditPassfail extends HtmlTemplate
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

		print "
		<div class='customer-standard-table-title-style-notice'>Password Failure</div>
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD>";

		print "There was an error processing your password change request.<br/>
		Please check the requirements below:<br/><br/>
		<ul>
			<li> New password can't be left blank.
			<li> Both new passwords must match.
			<li> Old password must match with your current password.
			<li> Password length should match 6 - 40 characters.
		</ul>";
		print "<br/><A HREF=\"javascript:history.go(-1)\">Please return and try again.</A>";

		print "</TD>
		</TR>
		</TABLE>
		</div>";

	}
}

?>
