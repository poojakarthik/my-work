<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// breadcrumb.php
//----------------------------------------------------------------------------//
/**
 * breadcrumb
 *
 * HTML Template for the breadcrumb HTML object
 *
 * HTML Template for the breadcrumb HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the breadcrumb menu and can be embedded in various Page Templates
 * 
 *
 * @file		breadcrumb.php
 * @language	PHP
 * @package		web_app
 * @author		Jared 'flame' Herbohn
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateBreadCrumb
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateBreadCrumb
 *
 * HTML Template class for the breadcrumb HTML object
 *
 * HTML Template class for the breadcrumb HTML object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateBreadCrumb
 * @extends	HtmlTemplate
 */
 
 
class HtmlTemplateBreadCrumb extends HtmlTemplate
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
		$strHtmlCode .= "<div id='BreadCrumbMenu'>\n";
		foreach (DBO()->BreadCrumb AS $objProperty)
		{
			$strHtmlCode .= "<a href='".$objProperty->Value."'>".$objProperty->Label."</a> &gt; ";
		}
		
		// Add the current page as a breadcrumb
		$mixCurrentPage = BreadCrumb()->GetCurrentPage();
		if ($mixCurrentPage !== FALSE)
		{
			// the current page has been defined.  Attach it to the bread crumb trail
			$strHtmlCode .= $mixCurrentPage;
		}
		
		// Remove the last 6 chars from html code, if it is equal to " &gt; "
		if (substr($strHtmlCode, -6) == " &gt; ")
		{
			$strHtmlCode = substr($strHtmlCode, 0, -6);
		}
		
		$strHtmlCode .= "\n</div>\n";
		
		echo $strHtmlCode;
	}
}

?>
