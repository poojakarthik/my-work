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
 * @package		ui_app
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
 * @package	ui_app
 * @class	HtmlTemplateError
 * @extends	HtmlTemplate
 */
class HtmlTemplateError extends HtmlTemplate
{
	function __construct()
	{
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
		echo "<div Id='VixenError' Class=''>\n	";
		DBO()->Error->Message->Render();
		echo "\n</div>\n";
	}
}

?>
