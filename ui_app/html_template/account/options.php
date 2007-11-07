<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// options.php DEPRICATED
//----------------------------------------------------------------------------//
/**
 * options
 *
 * HTML Template for the Account Options
 *
 * HTML Template for the Account Options
 *
 * @file		options.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross, Joel 'MagnumSwordFortress' Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountOptions DEPRICATED
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountOptions DEPRICATED
 *
 * A specific HTML Template object
 *
 * An Account Options HTML Template object
 *
 * @prefix	<prefix>
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
		echo "<h2 class='Options'>Options</h2>\n";
		echo "<div class='NarrowForm'>\n";
		
		echo "<div style='padding-left: 10px'>";
		$strEditAccountLink = Href()->ViewAccount(DBO()->Account->Id->Value);
		echo "<li><a href='$strEditAccountLink'><span class='DefaultOutputSpan'>View Account</span></a></li>\n";

		$strViewServicesLink = Href()->ViewServices(DBO()->Account->Id->Value);
		echo "<li><a href='$strViewServicesLink'><span class='DefaultOutputSpan'>View Services</span></a></li>\n";
		
		$strAddServiceLink = Href()->AddService(DBO()->Account->Id->Value);
		echo "<li><a href='$strAddServiceLink'><span class='DefaultOutputSpan'>Add Service</span></a></li>\n";
		
		echo "</div>\n";
		echo "</div>\n"; //NarrowForm
		echo "<div class='Seperator'></div>\n";
	}
}

?>
