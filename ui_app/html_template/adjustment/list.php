<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// adjustment_list.php
//----------------------------------------------------------------------------//
/**
 * adjustment_list
 *
 * HTML Template for the Adjustment List HTML object
 *
 * HTML Template for the Adjustment List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all adjustments relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		adjustment_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAdjustmentList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAdjustmentList
 *
 * HTML Template class for the Adjustment List HTML object
 *
 * HTML Template class for the Adjustment List HTML object
 * Lists all adjustments related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateAdjustmentList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAdjustmentList extends HtmlTemplate
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
		// Render each of the account invoices
		//TODO!
		echo "<br> INSERT ACCOUNT ADJUSTMENTS HERE";
		
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
