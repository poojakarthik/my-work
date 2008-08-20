<?php

class HtmlTemplate_BreadCrumb extends HtmlTemplate
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
		$strHtmlCode = "\t\t\t<div id='BreadCrumbMenu'>\n";
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
		
		$strHtmlCode .= "\n\t\t\t</div>\n";

		echo $strHtmlCode;
	}
}

?>
