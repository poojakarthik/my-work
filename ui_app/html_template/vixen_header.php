<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// vixen_header.php
//----------------------------------------------------------------------------//
/**
 * vixen_header
 *
 * HTML Template for the vixen header object
 *
 * HTML Template for the vixen header object
 *
 * @file		vixen_header.php
 * @language	PHP
 * @package		ui_app
 * @author		Jared 'flame' Herbohn
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateVixenHeader
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateVixenHeader
 *
 * HTML Template class for the HTML Vixen header object
 *
 * HTML Template class for the HTML Vixen header object
 *
 *
 *
 * @package	ui_app
 * @class	HtmlTemplateVixenHeader
 * @extends	HtmlTemplate
 */
class HtmlTemplateVixenHeader extends HtmlTemplate
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
		
		echo "    <div id='PopupHolder'></div>
    <div class='Logo'>
      <img src='img/template/vixen_logo.png' border='0'>
    </div>
    <div id='Header' class='sectionContainer'>
      <span class='LogoSpacer'></span>
      <div class='sectionContent'>
        <div class='Left'>
			TelcoBlue Internal Management System
		</div>
        <div class='Right'>
			Version 7.03
									
			<div class='Menu_Button'>
				<a href='#' onclick=''>
					<img src='img/template/bug.png' alt='Report Bug' title='Report Bug' border='0'>
				</a>
			</div>
		</div>
        <div class='Clear'></div>
      </div>
      <div class='Clear'></div>
    </div>
    <div class='Clear'></div>
    <div class='Seperator'></div>";
	}
}

?>
