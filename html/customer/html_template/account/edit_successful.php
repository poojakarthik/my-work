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
		echo "<div class='NarrowContent'>\n";

		$mixFoundError = FALSE;
		if($_POST['mixAccount_OldPassword'] == "" || $_POST['mixAccount_NewPassword1'] == "" || $_POST['mixAccount_NewPassword2'] == "")
		{
			$mixFoundError = TRUE;
		}
		if(SHA1($_POST['mixAccount_NewPassword1']) != DBO()->Contact->PassWord->Value)
		{
			$mixFoundError = TRUE;
		}
		if($_POST['mixAccount_NewPassword1'] != $_POST['mixAccount_NewPassword2'])
		{
			$mixFoundError = TRUE;
		}
		if(strlen($_POST['mixAccount_NewPassword1'])<"6" || strlen($_POST['mixAccount_NewPassword1'])>"40")
		{
			$mixFoundError = TRUE;
		}
		if($mixFoundError)
		{
			print "There was an error with the passwords entered.<br/>";
			print "<br/><A HREF=\"javascript:history.go(-1)\">Return and correct errors.</A>";
		}
		if($mixFoundError == FALSE)
		{
			print "Thank you for taking the time to update your account,<br/>your changes have been completed. <img src=\"" . Href()->GetBaseUrl() . "/img/generic/check.gif\"><br/><br/>";
		}
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
