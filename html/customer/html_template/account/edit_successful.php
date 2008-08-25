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
		if($_POST['mixAccount_OldPassword'] != "" && SHA1($_POST['mixAccount_NewPassword1']) != DBO()->Contact->PassWord->Value)
		{
			$mixFoundError = TRUE;
		}
		if($_POST['mixAccount_NewPassword1'] != $_POST['mixAccount_NewPassword2'])
		{
			$mixFoundError = TRUE;
		}
		if($mixFoundError)
		{
			echo "Error: Password not updated, the old/new passwords did not match.<br/>";
		}

		list($strFoundError,$strErrorResponse) = InputValidation("Password",$_POST['mixAccount_NewPassword1'],"mixed","40");
		if($strFoundError)
		{
			$mixFoundError = TRUE;
			print "Error: Password not updated, characters allowed: A-Z and 0-9, max length 40.<br/>";
		}
		if(!$mixFoundError)
		{
			print "Thank you for taking the time to update your account,<br/>your changes have been completed. <img src=\"" . Href()->GetBaseUrl() . "/img/generic/check.gif\"><br/><br/>";
		}
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
