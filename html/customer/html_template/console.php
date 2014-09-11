<?php
//----------------------------------------------------------------------------//
// HtmlTemplateConsole
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateConsole
 *
 * HTML Template object for the client app console 
 *
 * HTML Template object for the client app console
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateConsole
 * @extends	HtmlTemplate
 */
class HtmlTemplateConsole extends HtmlTemplate
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
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
		//$this->LoadJavascript("tooltip");
		
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
		
		echo "<h2 class='Console'>Console</h2>\n";
		
		$oAccountUser 	= Account_User::getForId(AuthenticatedUser()->_arrUser['id']);
		$strWelcome 	= "&nbsp;&nbsp;Welcome {$oAccountUser->given_name} {$oAccountUser->family_name}. You are currently logged into your account\n";
		
		echo "<span class='DefaultOutputSpan Default'>$strWelcome</span>";
		
		echo "</div>\n";
	}
}

?>
