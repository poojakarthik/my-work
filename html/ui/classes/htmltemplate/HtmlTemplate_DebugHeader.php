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
// HtmlTemplate_DebugHeader
//----------------------------------------------------------------------------//
/**
 * HtmlTemplate_DebugHeader
 *
 * HTML Template class for the HTML Vixen header object
 *
 * HTML Template class for the HTML Vixen header object
 *
 *
 *
 * @package	ui_app
 * @class	HtmlTemplate_DebugHeader
 * @extends	HtmlTemplate
 */
class HtmlTemplate_DebugHeader extends HtmlTemplate
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
		
		$this->LoadJavascript("debug");
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
		// TODO: Fix debug facility in newer versions of Flex management
		// This doesn't work properly in the new versions of flex, only in the old 'management' style code.
		// Probably relies on lightbox js files which aren't being loaded and may conflict with the jQuery library.
		return;
		/* 
		echo "
			<div id='VixenTooltip' style='display: none;' class='VixenTooltip'></div>
			<div class='debug-options'>
				<div class='Menu_Button'>
					<a href='#' onclick=''>
						<img src='img/template/bug.png' alt='Report Bug' title='Report Bug' border='0' />
					</a>&nbsp;\n";

			// Add debug button, which doesnt do much yet, just set debug to true;
			//  eventually move this somewhere more appropriate
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_DEBUG))
			{	
				echo "
					<a href='javascript: Vixen.debug^=TRUE;alert(\"Vixen.debug now is: \" + Vixen.debug );window.location = window.location + \"&Debug=1\"'>
					<img src='img/template/debug.png' alt='Debug' title='Debug' border='0' ></a>
					<script type='text/javascript'>Vixen.debug = TRUE;</script>\n";
			}
			echo "
				</div>
			</div>\n";
		*/
	}
}

?>
