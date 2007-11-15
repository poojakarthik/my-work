<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_override.php
//----------------------------------------------------------------------------//
/**
 * rate_group_override
 *
 * HTML Template for the Rate Group Override HTML object
 *
 * HTML Template for the Rate Group Override HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a rate group.
 *
 * @file		rate_group_override.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross 'Spudnik' Mullen
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupOverride
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateGroupOverride
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupOverride
 * @extends	HtmlTemplate
 */
class HtmlTemplateRateGroupOverride extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("rate_group_override");
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
	 *
	 * @method
	 */
	function Render()
	{
		//TODO! Draw the contents of the popup
	}
}

?>
