<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// client_app_header.php DEPRECIATED
//----------------------------------------------------------------------------//
/**
 * client_app_header
 *
 * HTML Template for the client app header object
 *
 * HTML Template for the client app header object
 *
 * @file		client_app_header.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateClientAppHeader
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateClientAppHeader
 *
 * HTML Template class for the HTML client app header object
 *
 * HTML Template class for the HTML client app header object
 *
 *
 *
 * @package	web_app
 * @class	HtmlTemplateClientAppHeader
 * @extends	HtmlTemplate
 */
class HtmlTemplateClientAppHeader extends HtmlTemplate
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
	
		// NOTE: this html Template has been depricated.  Its contents are now output in the layout_template
		echo "<div id='Document' class='documentContainer'>\n";
		
		echo "<div class='documentCurve Left documentCurveTopLeft'></div>\n";
		echo "<div class='documentCurve Right documentCurveTopRight'></div>\n";
		echo "<div class='clear'></div>\n";
		echo "<div class='pageContainer'>\n";
		
		
		return;
		
	}
}

?>
