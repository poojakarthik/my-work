<?php
//----------------------------------------------------------------------------//
// HtmlTemplateContactDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateContactDetails
 *
 * A specific HTML Template object
 *
 * An Contact Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateContactDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateContactDetails extends HtmlTemplate
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
		echo "<h2 class='Contact'>Contact Details</h2>\n";
		
		$strFullName = DBO()->Contact->Title->Value ." ". DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value;
		DBO()->Contact->FullName = $strFullName;
		
		echo "<div class='NarrowForm'>\n";
		DBO()->Contact->FullName->RenderOutput();
		DBO()->Contact->JobTitle->RenderOutput();
		DBO()->Contact->DOB->RenderOutput();
		DBO()->Contact->Email->RenderOutput();
		DBO()->Contact->Phone->RenderOutput();
		DBO()->Contact->Mobile->RenderOutput();
		DBO()->Contact->UserName->RenderOutput();
		DBO()->Contact->CustomerContact->RenderOutput();
		DBO()->Contact->Archived->RenderOutput();
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
