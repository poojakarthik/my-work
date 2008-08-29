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
 


 class HtmlTemplateAccountEditSuccessful extends HtmlTemplate
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

		$mixFoundError = FALSE;
		if($_POST['mixAccount_NewPassword1'] != "" || $_POST['mixAccount_NewPassword2'] != "")
		{
			// they have tried to change the password.. lets check if it went ok...
			if(SHA1($_POST['mixAccount_NewPassword1']) != DBO()->Contact->PassWord->Value)
			{
				$mixFoundError = TRUE;
			}
		}
		print "
		<div class='customer-standard-table-title-style-confirmation'>Confirmation</div>
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD>";
		if($mixFoundError)
		{
			print "There was an error with the passwords entered.<br/>";
			print "<br/><A HREF=\"javascript:history.go(-1)\">Return and correct errors.</A>";
		}
		if($mixFoundError == FALSE)
		{
			print "Thank you for taking the time to update your account.<br/><br/>Your changes have been completed.<br/><br/>";
		}
		print "</TD>
		</TR>
		</TABLE>
		</div>";

	}
}

?>
