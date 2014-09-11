<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// error.php
//----------------------------------------------------------------------------//
/**
 * error
 *
 * HTML Template for the HTML Error object
 *
 * HTML Template for the HTML Error object
 *
 * @file		error.php
 * @language	PHP
 * @package		web_app
 * @author		Jared 'flame' Herbohn
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateError
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateError
 *
 * HTML Template class for the HTML Error object
 *
 * HTML Template class for the HTML Error object
 *
 *
 *
 * @package	web_app
 * @class	HtmlTemplateError
 * @extends	HtmlTemplate
 */
class HtmlTemplateError extends HtmlTemplate
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
		echo "<div Id='VixenError'>\n	";
		DBO()->Error->Message->Render();
		echo "</div>\n";
		
		echo "<div style='height:300px;'></div>\n";
	}
}

?>
