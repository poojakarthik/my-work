<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// client_app_header.php
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
		// This line should probably go in Page->RenderHeader as it should be included in every page
		echo "<div id='PopupHolder'></div>";
		
		echo "<div>[INSERT CLIENT APP HEADER HERE]</div>";
		return;
		
		echo "    <div id='PopupHolder'></div>
	<div id='VixenTooltip' style='display: none;' class='VixenTooltip'></div>
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
                	<img src='img/template/bug.png' alt='Report Bug' title='Report Bug' border='0' /></a>\n";
            
			// Add debug button, which doesnt do much yet, just set debug to true;
			//  eventually move this somewhere more appropriate
		if (AuthenticatedUser()->_arrUser['Privileges'] >= PERMISSION_DEBUG)
		{
			echo "            	<a href='#' onclick='Vixen.debug^=TRUE;alert(\"Vixen.debug now is: \" + Vixen.debug );'>
            		<img src='img/template/debug.png' alt='Debug' title='Debug' border='0' >            	</a>			
            	<script type='text/javascript'>Vixen.debug = TRUE;</script>
            	<script type='text/javascript' src='" . JAVASCRIPT_BASE_DIR . "javascript/debug.js'></script>\n";
		}
		echo "            </div>\n
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
