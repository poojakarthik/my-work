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
		$strErrorMsg = DBO()->Error->Message->Value;
		echo "
<div id='ErrorMsg'>
	$strErrorMsg
</div>
<input type='button' value='Back' onClick='history.back();' style='margin-top:20px'></input>
			";
	}
}

?>
