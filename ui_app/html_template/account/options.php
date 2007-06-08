<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_options.php
//----------------------------------------------------------------------------//
/**
 * account_options
 *
 * HTML Template for the Account Options HTML object
 *
 * HTML Template for the Account Options HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all Account Options and can be embedded in
 * various Page Templates
 *
 * @file		account_options.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountOptions
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountOptions
 *
 * HTML Template class for the Account Options HTML object
 *
 * HTML Template class for the Account Options HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountOptions
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountOptions extends HtmlTemplate
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
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
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
		// Render each of the account options
		//TODO!
		echo "<br> INSERT ACCOUNT OPTIONS HERE";
		
		/*
		echo "<table border='5'>\n";
		foreach (DBO()->KnowledgeBase AS $strProperty=>$objValue)
		{
			$objValue->RenderOutput();
		}
		echo "</table>\n";
		*/
	}
}

?>
