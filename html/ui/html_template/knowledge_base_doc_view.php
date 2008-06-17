<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// knowledge_base_doc_view.php
//----------------------------------------------------------------------------//
/**
 * knowledge_base_doc_view
 *
 * HTML Template for the knowledge base doc view HTML object
 *
 * HTML Template for the knowledge base doc view HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all information relating to a knowledge base document and can be embedded in
 * various Page Templates
 *
 * @file		knowledge_base_doc_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateKnowledgeBaseDocView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateKnowledgeBaseDocView
 *
 * HTML Template class for the knowledge base doc view HTML object
 *
 * HTML Template class for the knowledge base doc view HTML object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateKnowledgeBaseDocView
 * @extends	HtmlTemplate
 */
 
 
class HtmlTemplateKnowledgeBaseDocView extends HtmlTemplate
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
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
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
		echo "<div class='WideColumn'>\n";
	
		DBO()->KnowledgeBase->Title->RenderOutput();
		DBO()->KnowledgeBase->CreatedOn->RenderOutput();
		if (DBO()->KnowledgeBase->LastUpdated->Value)
		{
			DBO()->KnowledgeBase->LastUpdated->RenderOutput();
		}
		DBO()->KnowledgeBase->CreatedBy->RenderCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
		if (DBO()->KnowledgeBase->AuthorisedBy->Value)
		{
			// display the name of the employee who authorised the article
			DBO()->KnowledgeBase->AuthorisedBy->RenderCallback("GetEmployeeName", NULL, RENDER_OUTPUT);
		}
		else
		{
			if (!DBO()->KnowledgeBase->AuthorisedOn->Value)
			{
				// Only display this if there isn't an AuthorisedOn date
				DBO()->KnowledgeBase->AuthorisedBy->RenderArbitrary("This article has not yet been authorised", RENDER_OUTPUT);
			}
		}
	
		// only display the AuthorisedOn date if there is one
		if (DBO()->KnowledgeBase->AuthorisedOn->Value)
		{
			DBO()->KnowledgeBase->AuthorisedOn->RenderOutput();
		}
		
		// Display the contents of the article
		echo "<div class='Seperator'></div>\n";
		DBO()->KnowledgeBase->Content->RenderValue();
		
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
